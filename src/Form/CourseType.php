<?php

namespace App\Form;

use App\Entity\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('characterCode', TextType::class, [
                'label' => 'Символьный код',
                'constraints' => [
                    new NotBlank(['message' => 'Поле "Cимвольный код" не должно быть пустым.']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Поле "Cимвольный код" не должно быть длинной более {{ limit }} символов.']),
                    new Regex(['pattern' => '/^[A-Za-z0-9]+$/', 'message' => 'В поле "Cимвольный код" могут содержаться только цифры и латиница.'])
                ],
            ])
            ->add('name', TextType::class, [
                'label' => 'Название',
                'constraints' => [
                    new NotBlank(['message' => 'Поле "Название" не должно быть пустым.']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Поле "Название" не должно быть длинной более {{ limit }} символов.']),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание',
                'constraints' => [
                    new Length([
                        'max' => 1000,
                        'maxMessage' => 'Поле "Описание" не должно быть длинной более {{ limit }} символов.']),
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Тип курса',
                'mapped' => false,
                'choices' => [
                    'Бесплатный' => Course::FREE_TYPE,
                    'Аренда' => Course::RENT_TYPE,
                    'Покупка' => Course::BUY_TYPE,
                ]
            ])
            ->add('cost', MoneyType::class, [
                'label' => 'Стоимость курса',
                'currency' => 'usd',
                'mapped' => false,
                'empty_data' => 0.0,
                'constraints' => [
                    new Callback(
                        function ($object, ExecutionContextInterface $context, $payload) {
                            $formData = $context->getRoot()->all();
                            if ($formData["type"]->getData() === Course::FREE_TYPE && $object != 0) {
                                $context->buildViolation('Курс с типом "Бесплатный" не может иметь стоимость.')
                                    ->atPath('cost')
                                    ->addViolation();
                            }
                            if ($formData["type"]->getData() !== Course::FREE_TYPE && $object <= 0) {
                                $context->buildViolation('Курс с типом "Аренда или Покупка" 
                            не может нулевую или отрицательную стоимость.')
                                    ->atPath('cost')
                                    ->addViolation();
                            }
                        }
                    )
                ],
                'invalid_message' => 'В данное поле можно вводить только цифры.'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}
