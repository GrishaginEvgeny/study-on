<?php

namespace App\Controller;

use App\Entity\Course;
use App\Form\CourseType;
use App\Repository\CourseRepository;
use App\Services\BillingCoursesService;
use JMS\Serializer\EventDispatcher\EventDispatcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * @Route("/courses")
 */
class CourseController extends AbstractController
{
    /**
     * @Route("", name="app_course_index", methods={"GET"})
     */
    public function index(CourseRepository $courseRepository): Response
    {
        return $this->render('course/index.html.twig', [
            'courses' => $courseRepository->findAll(),
        ]);
    }

    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @Route("/new", name="app_course_new", methods={"GET", "POST"})
     */
    public function new(Request               $request,
                        CourseRepository      $courseRepository,
                        BillingCoursesService $billingCoursesService,
                        NotifierInterface     $notifier): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $billingCoursesService->addCourse($this->getUser(), [
                    'type' => array_flip(Course::TYPES_ARRAY)[$form->get('type')->getData()],
                    'title' => $course->getName(),
                    'code' => $course->getCharacterCode(),
                    'price' => $form->get('cost')->getData()
                ]);
                $courseRepository->add($course, true);
            } catch (\Exception $e) {
                $notification = (new Notification($e->getMessage(), ['browser']))->emoji("👎");
                $notifier->send($notification);
            }

            return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('course/new.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_course_show", methods={"GET"})
     */
    public function show(Course $course, BillingCoursesService $billingCoursesService): Response
    {
        $billingCourse = $billingCoursesService->course($course->getCharacterCode());
        return $this->render('course/show.html.twig', [
            'course' => $course,
            'type' => $billingCourse['type'],
            'price' => $billingCourse['price'],
            'balance' => $this->getUser() ? $this->getUser()->getBalance() : -1
        ]);
    }

    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @Route("/{id}/edit", name="app_course_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request,
                         Course $course,
                         CourseRepository      $courseRepository,
                         BillingCoursesService $billingCoursesService,
                         NotifierInterface     $notifier): Response
    {
        $previousCode = $course->getCharacterCode();
        $billingCourse = $billingCoursesService->course($course->getCharacterCode());
        $form = $this->createForm(CourseType::class, $course);
        $billingCourseType = Course::TYPES_ARRAY[$billingCourse['type']];
        $form->get('cost')->setData($billingCourse['price']);
        $form->get('type')->setData($billingCourseType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $billingCoursesService->editCourse($this->getUser(), $previousCode, [
                    'type' => array_flip(Course::TYPES_ARRAY)[$form->get('type')->getData()],
                    'title' => $course->getName(),
                    'code' => $course->getCharacterCode(),
                    'price' => $form->get('cost')->getData()
                ]);
                $courseRepository->add($course, true);
            } catch (\Exception $e) {
                $notification = (new Notification($e->getMessage(), ['browser']))->emoji("👎");
                $notifier->send($notification);
                return $this->renderForm('course/edit.html.twig', [
                    'course' => $course,
                    'form' => $form,
                ]);
            }

            return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
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
    public function delete(Request $request, Course $course, CourseRepository $courseRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $course->getId(), $request->request->get('_token'))) {
            $courseRepository->remove($course, true);
        }

        return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/{id}/buy", name="app_course_buy", methods={"GET"})
     */
    public function buy(
        Course                $course,
        BillingCoursesService $billingCoursesService,
        NotifierInterface     $notifier
    )
    {
        $user = $this->getUser();
        try {
            $billingCoursesService->buy($course->getCharacterCode(), $user);
            $notification = (new Notification('Курс успешно оплачен.', ['browser']))->emoji("👍");
            $notifier->send($notification);
        } catch (\Exception $e) {
            $notification = (new Notification($e->getMessage(), ['browser']))->emoji("👎");
            $notifier->send($notification);
            return $this->redirectToRoute('app_course_show', ['id' => $course->getId()], 301);
        }
        return $this->redirectToRoute('app_course_show', ['id' => $course->getId()], 301);
    }
}
