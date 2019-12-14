<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuizzRepository")
 */
class Quizz
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     */
    private $nb_questions;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $stats;

    /**
     * @ORM\Column(type="integer")
     */
    private $nb_done;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\user", inversedBy="quizz")
     * @ORM\JoinColumn(nullable=false)
     */
    private $creator;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\QuizzQuestion", mappedBy="quizz")
     */
    private $quizzQuestions;

    public function __construct()
    {
        $this->quizzQuestions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getNbQuestions(): ?int
    {
        return $this->nb_questions;
    }

    public function setNbQuestions(int $nb_questions): self
    {
        $this->nb_questions = $nb_questions;

        return $this;
    }

    public function getStats(): ?int
    {
        return $this->stats;
    }

    public function setStats(?int $stats): self
    {
        $this->stats = $stats;

        return $this;
    }

    public function getNbDone(): ?int
    {
        return $this->nb_done;
    }

    public function setNbDone(int $nb_done): self
    {
        $this->nb_done = $nb_done;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getCreator(): ?user
    {
        return $this->creator;
    }

    public function setCreator(?user $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * @return Collection|QuizzQuestion[]
     */
    public function getQuizzQuestions(): Collection
    {
        return $this->quizzQuestions;
    }

    public function addQuizzQuestion(QuizzQuestion $quizzQuestion): self
    {
        if (!$this->quizzQuestions->contains($quizzQuestion)) {
            $this->quizzQuestions[] = $quizzQuestion;
            $quizzQuestion->setQuizz($this);
        }

        return $this;
    }

    public function removeQuizzQuestion(QuizzQuestion $quizzQuestion): self
    {
        if ($this->quizzQuestions->contains($quizzQuestion)) {
            $this->quizzQuestions->removeElement($quizzQuestion);
            // set the owning side to null (unless already changed)
            if ($quizzQuestion->getQuizz() === $this) {
                $quizzQuestion->setQuizz(null);
            }
        }

        return $this;
    }
}
