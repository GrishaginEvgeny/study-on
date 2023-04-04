<?php

namespace App\Form;

use App\Entity\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('CharacterCode',TextType::class, [
                'label' => 'Символьный код',
                'constraints' => [
                    new NotBlank(['message' => 'Поле "Cимвольный код" не должно быть пустым']),
                    new Length( [
                        'max' => 255,
                        'maxMessage' => 'Поле "Cимвольный код" не должно быть длинной более {{ limit }} символов']),
                ],
            ])
            ->add('Name', TextType::class, [
                'label' => 'Название',
                'constraints' => [
                    new NotBlank(['message' => 'Поле "Название" не должно быть пустым']),
                    new Length( [
                        'max' => 255,
                        'maxMessage' => 'Поле "Название" не должно быть длинной более {{ limit }} символов']),
                ],
            ])
            ->add('Description', TextareaType::class,[
                'label' => 'Описание',
                'constraints' => [
                    new Length( [
                        'max' => 1000,
                        'maxMessage' => 'Поле "Описание" не должно быть длинной более {{ limit }} символов']),
                ],
            ] )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}
