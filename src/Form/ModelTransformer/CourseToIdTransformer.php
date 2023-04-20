<?php

namespace App\Form\ModelTransformer;

use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CourseToIdTransformer implements DataTransformerInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Преобразует объект (проблему) в строку (цифру).
     *
     * @param  Course|null $value
     */
    public function transform($value): string
    {
        if (null === $value) {
            return '';
        }


        return $value->getId();
    }

    /**
     * Преобразует строку (число) в объект (проблему).
     *
     * @param  string $value
     * @throws TransformationFailedException
     */
    public function reverseTransform($value): ?Course
    {
        if (!$value) {
            return null;
        }

        $course = $this->entityManager
            ->getRepository(Course::class)
            ->find($value)
        ;

        if (null === $course) {
            throw new TransformationFailedException('Такого курса не существует.');
        }

        return $course;
    }
}
