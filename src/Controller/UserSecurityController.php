<?php

namespace App\Controller;

use App\Security\BillingAuthenticator;
use App\Services\BillingCoursesService;
use App\Services\BillingUserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
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
    public function profile(BillingUserService $billingService): Response
    {
        $authedUser = $this->getUser();
        $token = $authedUser->getApiToken();
        $refreshToken = $authedUser->getRefreshToken();
        $user = $billingService->currentUser($token, $refreshToken);
        return $this->render('security/profile.html.twig', [
            'email' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
            'balance' => $user->getBalance(),
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/transactions", name="app_transactions")
     * @throws \Exception
     */
    public function transactions(
        Request $request,
        BillingCoursesService $billingCoursesService
    ): Response {
        $user = $this->getUser();
        $form = $this->createFormBuilder()
            ->add(
                'course_code',
                null,
                ['label' => 'Код курса', 'required' => false]
            )
            ->add(
                'skip_expired',
                CheckboxType::class,
                ['label' => 'Убрать истёкшие транзакции', 'required' => false]
            )
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Платежи' => 'payment',
                    'Депозиты' => 'deposit'
                ],
                'choice_attr' => function () {
                        return ['checked' => true];
                },
                'expanded' => 'true',
                'multiple' => 'true',
                'label' => 'Тип транзакции',
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $type = null;
            try {
                if ($formData['type'] === []) {
                    throw new \Exception('Вы должны выбрать хотя бы один тип.');
                }
            } catch (\Exception $e) {
                $form->get('type')->addError(new FormError($e->getMessage()));
                $transactions = $billingCoursesService->transactions($user);
                return $this->render('security/transactions.html.twig', [
                    'transactions' => $transactions,
                    'form' => $form->createView()
                ]);
            }
            if (!(in_array('payment', $formData['type']) && in_array('deposit', $formData['type']))) {
                $type = in_array('payment', $formData['type']) ? 'payment' : 'deposit';
            }
            $params = [
                'type' => $type,
                'course_code' => $formData['course_code'],
                'skip_expired' => $formData['skip_expired']
            ];
            $transactions = $billingCoursesService->transactions($user, $params);
            return $this->render('security/transactions.html.twig', [
                'transactions' => $transactions,
                'form' => $form->createView()
            ]);
        }
        $transactions = $billingCoursesService->transactions($user);
        return $this->render('security/transactions.html.twig', [
            'transactions' => $transactions,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/register", name="app_register")
     * @throws \Exception
     */
    public function register(
        Request $request,
        UserAuthenticatorInterface $authenticator,
        BillingAuthenticator $formAuthenticator,
        BillingUserService $billingService
    ): Response {
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
                    $request
                );
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
