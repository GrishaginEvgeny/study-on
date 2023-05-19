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
use Symfony\Contracts\Translation\TranslatorInterface;

class CourseType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('characterCode', TextType::class, [
                'label' => 'Символьный код',
                'constraints' => [
                    new NotBlank(['message' => $this->translator->trans(
                        'errors.course.slug.non_empty',
                        [],
                        'validators'
                    )]),
                    new Length([
                        'max' => 255,
                        'maxMessage' => $this->translator->trans('errors.course.slug.too_big', [], 'validators')]),
                    new Regex([
                        'pattern' => '/^[A-Za-z0-9]+$/',
                        'message' => $this->translator->trans('errors.course.slug.wrong_regex', [], 'validators')
                    ])
                ],
            ])
            ->add('name', TextType::class, [
                'label' => 'Название',
                'constraints' => [
                    new NotBlank(['message' => $this->translator->trans(
                        'errors.course.name.non_empty',
                        [],
                        'validators'
                    )]),
                    new Length([
                        'max' => 255,
                        'maxMessage' => $this->translator->trans(
                            'errors.course.name.too_big',
                            [],
                            'validators'
                        )]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание',
                'constraints' => [
                    new Length([
                        'max' => 1000,
                        'maxMessage' => $this->translator->trans(
                            'errors.course.description.too_big',
                            [],
                            'validators'
                        )]),
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
                                $context->buildViolation($this->translator->trans(
                                    'errors.course.cost.free_with_cost',
                                    [],
                                    'validators'
                                ))
                                    ->atPath('cost')
                                    ->addViolation();
                            }
                            if ($formData["type"]->getData() !== Course::FREE_TYPE && $object <= 0) {
                                $context->buildViolation($this->translator->trans(
                                    'errors.course.cost.buyable_without_cost',
                                    [],
                                    'validators'
                                ))
                                    ->atPath('cost')
                                    ->addViolation();
                            }
                        }
                    )
                ],
                'invalid_message' => $this->translator->trans(
                    'errors.course.cost.invalid_message',
                    [],
                    'validators'
                )
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}
