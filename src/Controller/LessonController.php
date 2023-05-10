<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Form\LessonType;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use App\Services\BillingCoursesService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/lessons")
 */
class LessonController extends AbstractController
{
    private LessonRepository $lessonRepository;

    private CourseRepository $courseRepository;

    private BillingCoursesService $billingCoursesService;

    private const ACCESS_DENIED_TEXT = 'У вас доступа к этому курсу.';

    public function __construct(
        LessonRepository $lessonRepository,
        CourseRepository $courseRepository,
        BillingCoursesService $billingCoursesService
    ) {
        $this->billingCoursesService = $billingCoursesService;
        $this->courseRepository = $courseRepository;
        $this->lessonRepository = $lessonRepository;
    }

    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @Route("/new", name="app_lesson_new", methods={"GET", "POST"})
     */
    public function new(Request $request): Response
    {
        $lesson = new Lesson();
        $courseId = $request->query->get("id", null);
        $lesson->setCourse($this->courseRepository->find($courseId));
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->lessonRepository->add($lesson, true);

            return $this->redirectToRoute(
                'app_course_show',
                ['id' => $lesson->getCourse()->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderForm('lesson/new.html.twig', [
            'lesson' => $lesson,
            'form' => $form,
            'courseId' => $courseId
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/{id}", name="app_lesson_show", methods={"GET"})
     */
    public function show(Lesson $lesson): Response
    {
        $transactionsOnLessonCourse = $this->billingCoursesService->transactions(
            $this->getUser(),
            ['course_code' => $lesson->getCourse()->getCharacterCode(),
            'skip_expired' => true]
        );
        $course = $this->billingCoursesService->course($lesson->getCourse()->getCharacterCode());
        if (count($transactionsOnLessonCourse) === 0 && $course["type"] !== "free") {
            throw new AccessDeniedException(self::ACCESS_DENIED_TEXT);
        }
        return $this->render('lesson/show.html.twig', [
            'lesson' => $lesson,
        ]);
    }

    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @Route("/{id}/edit", name="app_lesson_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Lesson $lesson): Response
    {
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->lessonRepository->add($lesson, true);

            return $this->redirectToRoute(
                'app_course_show',
                ['id' => $lesson->getCourse()->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderForm('lesson/edit.html.twig', [
            'lesson' => $lesson,
            'form' => $form,
        ]);
    }

    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @Route("/{id}", name="app_lesson_delete", methods={"POST"})
     */
    public function delete(Request $request, Lesson $lesson): Response
    {
        if (
            $this->isCsrfTokenValid(
                'delete' . $lesson->getId(),
                $request->request->get('_token')
            )
        ) {
            $this->lessonRepository->remove($lesson, true);
        }

        return $this->redirectToRoute(
            'app_course_show',
            ['id' => $lesson->getCourse()->getId()],
            Response::HTTP_SEE_OTHER
        );
    }
}
