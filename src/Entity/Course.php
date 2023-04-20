<?php

namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CourseRepository::class)
 * @ORM\Table(name="Course")
 * @UniqueEntity(fields={"CharacterCode"}, message="Курс с таким символьным кодом уже существует.")
 */
class Course
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $CharacterCode;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Name;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $Description;

    /**
     * @ORM\OneToMany(targetEntity=Lesson::class, mappedBy="Course", cascade={"persist"}, orphanRemoval=true)
     */
    private $Lessons;

    public function __construct()
    {
        $this->Lessons = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->Name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCharacterCode(): ?string
    {
        return $this->CharacterCode;
    }

    public function setCharacterCode(string $CharacterCode): self
    {
        $this->CharacterCode = $CharacterCode;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(?string $Description): self
    {
        $this->Description = $Description;

        return $this;
    }

    /**
     * @return Collection<int, Lesson>
     */
    public function getLessons(): Collection
    {
        return $this->Lessons;
    }

    public function addLesson(Lesson $lesson): self
    {
        if (!$this->Lessons->contains($lesson)) {
            $this->Lessons[] = $lesson;
            $lesson->setCourse($this);
        }

        return $this;
    }

    public function removeLesson(Lesson $lesson): self
    {
        if ($this->Lessons->removeElement($lesson)) {
            // set the owning side to null (unless already changed)
            if ($lesson->getCourse() === $this) {
                $lesson->setCourse(null);
            }
        }

        return $this;
    }
}
