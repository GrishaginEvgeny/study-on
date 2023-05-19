<?php

namespace App\Controller;

use App\Entity\Course;
use App\Exception\BillingNotFoundException;
use App\Exception\BillingValidationException;
use App\Form\CourseType;
use App\Repository\CourseRepository;
use App\Services\BillingCoursesService;
use App\Services\BillingUserService;
use App\Util\OperationsForArraysWithArraysByKey;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/courses")
 */
class CourseController extends AbstractController
{
    private CourseRepository $courseRepository;

    private BillingCoursesService $billingCoursesService;

    private NotifierInterface $notifier;

    private BillingUserService $billingUserService;

    private TranslatorInterface $translator;


    public function __construct(
        CourseRepository $courseRepository,
        BillingCoursesService $billingCoursesService,
        NotifierInterface $notifier,
        BillingUserService $billingUserService,
        TranslatorInterface $translator
    ) {
        $this->courseRepository = $courseRepository;
        $this->billingCoursesService = $billingCoursesService;
        $this->notifier = $notifier;
        $this->billingUserService = $billingUserService;
        $this->translator = $translator;
    }

    /**
     * @Route("", name="app_course_index", methods={"GET"})
     */
    public function index(): Response
    {
        $purchasedCourses = [];
        if ($this->getUser()) {
            $transactions = $this->billingCoursesService
                ->transactions($this->getUser(), [
                    'type' => 'payment',
                    'skip_expired' => true
                ]);

            $courses = $this->billingCoursesService->courses();
            $leftJoinOnTransactionAndCourses = OperationsForArraysWithArraysByKey::leftJoin(
                $transactions,
                $courses,
                'course_code'
            );
            $purchasedCourses = array_column($leftJoinOnTransactionAndCourses, 'course_code');
        }
        $test = $this->translator->trans('errors.course.cost.buyable_without_cost', [], 'validators');
        return $this->render('course/index.html.twig', [
            'courses' => $this->courseRepository->findAll(),
            'purchasedCourses' => $purchasedCourses,
        ]);
    }

    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @Route("/new", name="app_course_new", methods={"GET", "POST"})
     */
    public function new(Request $request): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->billingCoursesService->addCourse($this->getUser(), [
                    'type' => array_flip(Course::TYPES_ARRAY)[$form->get('type')->getData()],
                    'title' => $course->getName(),
                    'code' => $course->getCharacterCode(),
                    'price' => $form->get('cost')->getData()
                ]);
                $this->courseRepository->add($course, true);
                return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
            } catch (BillingValidationException $e) {
                $notification = (new Notification(implode("\n", $e->getErrors()), ['browser']))
                    ->emoji($this->translator->trans('emoji.bad'));
                $this->notifier->send($notification);
            } catch (BillingNotFoundException $e) {
                $notification = (new Notification($e->getMessage(), ['browser']))
                    ->emoji($this->translator->trans('emoji.bad'));
                $this->notifier->send($notification);
            }
        }

        return $this->renderForm('course/new.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_course_show", methods={"GET"})
     */
    public function show(Course $course): Response
    {
        $billingCourse = $this->billingCoursesService->course($course->getCharacterCode());
        $authedUser = $this->getUser();
        $token = !is_null($authedUser) ? $authedUser->getApiToken() : null;
        $refreshToken = !is_null($authedUser) ? $authedUser->getRefreshToken() : null;
        $isPurchased = false;
        $balance = null;
        if (!is_null($authedUser)) {
            $userInfo = $this->billingUserService->currentUser($token, $refreshToken);
            $countOfTransactionOnUser = count($this->billingCoursesService->transactions($authedUser, [
                'type' => 'payment',
                'course_code' => $course->getCharacterCode(),
                'skip_expired' => true
            ]));
            $isPurchased = $countOfTransactionOnUser === 1;
            $balance = $userInfo['balance'];
        }
        return $this->render('course/show.html.twig', [
            'course' => $course,
            'type' => $billingCourse['type'],
            'price' => $billingCourse['price'],
            'balance' => $balance,
            'isPurchased' => $isPurchased
        ]);
    }

    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @Route("/{id}/edit", name="app_course_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Course $course): Response
    {
        $previousCode = $course->getCharacterCode();
        $billingCourse = $this->billingCoursesService->course($course->getCharacterCode());
        $form = $this->createForm(CourseType::class, $course);
        $billingCourseType = Course::TYPES_ARRAY[$billingCourse['type']];
        $form->get('cost')->setData($billingCourse['price']);
        $form->get('type')->setData($billingCourseType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->billingCoursesService->editCourse($this->getUser(), $previousCode, [
                    'type' => array_flip(Course::TYPES_ARRAY)[$form->get('type')->getData()],
                    'title' => $course->getName(),
                    'code' => $course->getCharacterCode(),
                    'price' => $form->get('cost')->getData()
                ]);
                $this->courseRepository->add($course, true);
                return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
            } catch (BillingValidationException $e) {
                $notification = (new Notification(implode("\n", $e->getErrors()), ['browser']))
                    ->emoji($this->translator->trans('emoji.bad'));
                $this->notifier->send($notification);
            } catch (BillingNotFoundException $e) {
                $notification = (new Notification($e->getMessage(), ['browser']))
                    ->emoji($this->translator->trans('emoji.bad'));
                $this->notifier->send($notification);
            }
        }

        return $this->renderForm('course/edit.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @Route("/{id}", name="app_course_delete", methods={"POST"})
     */
    public function delete(Request $request, Course $course): Response
    {
        if ($this->isCsrfTokenValid('delete' . $course->getId(), $request->request->get('_token'))) {
            $this->courseRepository->remove($course, true);
        }

        return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/{id}/buy", name="app_course_buy", methods={"GET"})
     */
    public function buy(Course $course)
    {
        $user = $this->getUser();
        try {
            $this->billingCoursesService->buy($course->getCharacterCode(), $user);
            $notification = (new Notification(
                $this->translator->trans('success.pay'),
                ['browser']
            ))->emoji($this->translator->trans('emoji.good'));
            $this->notifier->send($notification);
        } catch (BillingValidationException $e) {
            $notification = (new Notification(implode("\n", $e->getErrors()), ['browser']))
                ->emoji($this->translator->trans('emoji.bad'));
            $this->notifier->send($notification);
        } catch (BillingNotFoundException $e) {
            $notification = (new Notification($e->getMessage(), ['browser']))
                ->emoji($this->translator->trans('emoji.bad'));
            $this->notifier->send($notification);
        }
        return $this->redirectToRoute('app_course_show', ['id' => $course->getId()], 301);
    }
}
