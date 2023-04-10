<?php

namespace App\Controller;

use App\Security\BillingAuthenticator;
use App\Services\BillingService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Security\User;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class UserSecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_course_index');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/profile", name="app_profile")
     */
    public function profile(BillingService $billingService): Response
    {
        $authedUser = $this->getUser();
        $token = $authedUser->getApiToken();
        $user = $billingService->currentUser($token);
        return $this->render('security/profile.html.twig', [
            'email' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
            'balance' => $user->getBalance(),
        ]);
    }

    /**
     * @Route("/register", name="app_register")
     * @throws \Exception
     */
    public function register(Request              $request, UserAuthenticatorInterface $authenticator,
                             BillingAuthenticator $formAuthenticator, BillingService $billingService): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_profile');
        }
        $form = $this->createFormBuilder()
            ->add('username', EmailType::class, ['label' => 'E-mail'])
            ->add('password', RepeatedType::class, [
                'first_options' => ['label' => 'Пароль'],
                'second_options' => ['label' => 'Подтверждение пароля'],
                'type' => PasswordType::class,
                'invalid_message' => 'Поля паролей должны совпадать.'
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $user = $billingService->register(json_encode($form->getData()));
                return $authenticator->authenticateUser(
                    $user,
                    $formAuthenticator,
                    $request);
            } catch (\Exception $e) {
                    $errors = json_decode($e->getMessage(), true);
                    foreach ($errors as $key => $error) {
                    $form->get($key)->addError(new FormError($error));
                        if ($key === 'password') {
                            $form->get($key)->get('first')->addError(new FormError($error));
                        }
                    }
                    return $this->render('security/register.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
            }
            return $this->render('security/register.html.twig', [
                'form' => $form->createView(),
            ]);
        }
}