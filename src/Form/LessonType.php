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

class LessonType extends AbstractType
{
    private CourseToIdTransformer $courseTransformer;

    public function __construct(CourseToIdTransformer $courseTransformer)
    {
        $this->courseTransformer = $courseTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Name', TextType::class, [
                'label' => 'Название',
                'constraints' => [
                    new NotBlank(['message' => 'Поле "Название" не должно быть пустым']),
                    new Length( [
                        'max' => 255,
                        'maxMessage' => 'Поле "Название" не должно быть длинной более {{ limit }} символов']),
                ],
            ])
            ->add('SequenceNumber', NumberType::class, [
                'label' => 'Порядковый номер',
                'constraints' => [
                    new GreaterThanOrEqual(['value'=> 1, 'message' => 'Порядковый номер должен быть больше или равен {{ compared_value }}']),
                    new LessThanOrEqual(['value'=> 10000, 'message' => 'Порядковый номер должен быть меньше или равен {{ compared_value }}'])
                ]
            ])
            ->add('Content', TextareaType::class,
                [
                    'label' => 'Содержимое урока',
                    'constraints' => [
                        new NotBlank(['message' => 'Поле "Содержимое урока" не должно быть пустым']),
                    ],
                ])
            ->add('Course', HiddenType::class)
        ;

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
