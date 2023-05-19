<?php

namespace App\Form;

use App\Entity\Lesson;
use App\Form\ModelTransformer\CourseToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class LessonType extends AbstractType
{
    private CourseToIdTransformer $courseTransformer;

    private TranslatorInterface $translator;

    public function __construct(CourseToIdTransformer $courseTransformer, TranslatorInterface $translator)
    {
        $this->courseTransformer = $courseTransformer;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Name', TextType::class, [
                'label' => 'Название',
                'constraints' => [
                    new NotBlank(['message' => $this->translator->trans(
                        'errors.lessons.name.non_empty',
                        [],
                        'validators'
                    )]),
                    new Length([
                        'max' => 255,
                        'maxMessage' => $this->translator->trans(
                            'errors.lessons.name.too_big',
                            [],
                            'validators'
                        )]),
                ],
            ])
            ->add('SequenceNumber', NumberType::class, [
                'label' => 'Порядковый номер',
                'constraints' => [
                    new GreaterThanOrEqual(['value' => 1,
                        'message' => $this->translator->trans(
                            'errors.lessons.sequence_number.too_tiny',
                            [],
                            'validators'
                        )]),
                    new LessThanOrEqual(['value' => 10000,
                        'message' => $this->translator->trans(
                            'errors.lessons.sequence_number.too_big',
                            [],
                            'validators'
                        )]),
                    new NotBlank(['message' => $this->translator->trans(
                        'errors.lessons.sequence_number.non_empty',
                        [],
                        'validators'
                    )]),
                ],
                'invalid_message' => $this->translator->trans(
                    'errors.lessons.sequence_number.non_integer',
                    [],
                    'validators'
                )
            ])
            ->add(
                'Content',
                TextareaType::class,
                [
                    'label' => 'Содержимое урока',
                    'constraints' => [
                        new NotBlank(['message' => $this->translator->trans(
                            'errors.lessons.content.non_empty',
                            [],
                            'validators'
                        )]),
                    ],
                ]
            )
            ->add('Course', HiddenType::class);

        $builder->get('Course')
            ->addModelTransformer($this->courseTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lesson::class,
            'Course' => null,
        ]);
    }
}
