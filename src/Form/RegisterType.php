<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegisterType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', EmailType::class, [
                'label' => 'E-mail',
                'constraints' => [
                    new NotBlank(["message" => $this->translator->trans(
                        'errors.register.email.non_empty',
                        [],
                        'validators'
                    )])
                ],
                "invalid_message" => $this->translator->trans(
                    'errors.register.email.invalid_message',
                    [],
                    'validators'
                )
            ])
            ->add('password', RepeatedType::class, [
                'first_options' => ['label' => 'Пароль'],
                'second_options' => ['label' => 'Подтверждение пароля'],
                'type' => PasswordType::class,
                'constraints' => [
                    new Regex([
                        "pattern" => "/(?=.*[0-9])(?=.*[.!@#$%^&*])(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z!@#$%^&*.]+$/",
                        "message" => $this->translator->trans(
                            'errors.register.password.wrong_regex',
                            [],
                            'validators'
                        )
                    ]),
                    new Length([
                        "min" => 6,
                        "minMessage" => $this->translator->trans(
                            'errors.register.password.too_tiny',
                            [],
                            'validators'
                        )
                    ])
                ],
                'invalid_message' => $this->translator->trans(
                    'errors.register.password.wrong_repeat',
                    [],
                    'validators'
                )
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
