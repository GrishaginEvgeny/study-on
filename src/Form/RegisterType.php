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

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', EmailType::class, [
                'label' => 'E-mail',
                'constraints' => [
                    new NotBlank(["message" => "Поле e-mail не может быт пустым."])
                ],
                "invalid_message" => "Поле E-mail содержит некорректные данные."
            ])
            ->add('password', RepeatedType::class, [
                'first_options' => ['label' => 'Пароль'],
                'second_options' => ['label' => 'Подтверждение пароля'],
                'type' => PasswordType::class,
                'constraints' => [
                    new Regex([
                        "pattern" => "/(?=.*[0-9])(?=.*[.!@#$%^&*])(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z!@#$%^&*.]+$/",
                        "message" => "Пароль должен содержать как один из спец. символов (.!@#$%^&*), " .
        "прописную и строчные буквы латинского алфавита и цифру."
                    ]),
                    new Length([
                        "min" => 6,
                        "minMessage" => "Пароль должен содержать минимум {{ limit }} символов."
                    ])
                ],
                'invalid_message' => 'Поля паролей должны совпадать.'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
