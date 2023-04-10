<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Form\LessonType;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/lessons")
 */
class LessonController extends AbstractController
{
    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @Route("/new", name="app_lesson_new", methods={"GET", "POST"})
     */
    public function new(Request $request, LessonRepository $lessonRepository, CourseRepository $courseRepository): Response
    {
        $lesson = new Lesson();
        $courseId = $request->query->get("id", null);
        $lesson->setCourse($courseRepository->find($courseId));
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $lessonRepository->add($lesson, true);

            return $this->redirectToRoute('app_course_show', ['id' => $lesson->getCourse()->getId()], Response::HTTP_SEE_OTHER);
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
        return $this->render('lesson/show.html.twig', [
            'lesson' => $lesson,
        ]);
    }

    /**
     * @IsGranted("ROLE_SUPER_ADMIN")
     * @Route("/{id}/edit", name="app_lesson_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Lesson $lesson, LessonRepository $lessonRepository): Response
    {
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lessonRepository->add($lesson, true);

            return $this->redirectToRoute('app_course_show', ['id' => $lesson->getCourse()->getId()], Response::HTTP_SEE_OTHER);
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
    public function delete(Request $request, Lesson $lesson, LessonRepository $lessonRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$lesson->getId(), $request->request->get('_token'))) {
            $lessonRepository->remove($lesson, true);
        }

        return $this->redirectToRoute('app_course_show', ['id' => $lesson->getCourse()->getId()], Response::HTTP_SEE_OTHER);
    }
}
