<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class TransactionForm extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
                'expanded' => 'true',
                'multiple' => 'true',
                'data' => ['payment', 'deposit'],
                'label' => 'Тип транзакции',
                'preferred_choices' => ['payment', 'deposit'],
                'constraints' => [
                    new NotBlank(['message' => $this->translator->trans(
                        'errors.transaction.non_empty',
                        [],
                        'validators'
                    )])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
