<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RedirectController extends AbstractController
{
    /**
     * @Route("/", name="app_redirect_to_courses")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('app_course_index', [], 301);
    }
}
