<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QcmRepository")
 */
class Qcm
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="qcms")
     * @ORM\JoinColumn(nullable=false)
     */
    private $id_creator;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description = "";

    /**
     * @ORM\Column(type="integer")
     */
    private $nb_questions = 0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $stats;

    /**
     * @ORM\Column(type="integer")
     */
    private $nb_done = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Question", mappedBy="id_qcm")
     */
    private $questions;



    public function __construct()
    {
        $this->updated_at = new \DateTime("Europe/Paris");
        $this->created_at = new \DateTime("Europe/Paris");
        $this->questions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdCreator(): ?User
    {
        return $this->id_creator;
    }

    public function setIdCreator(?User $id_creator): self
    {
        $this->id_creator = $id_creator;

        return $this;
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

    public function setStats(int $stats): self
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

    /**
     * @return Collection|Question[]
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->setIdQcm($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->contains($question)) {
            $this->questions->removeElement($question);
            // set the owning side to null (unless already changed)
            if ($question->getIdQcm() === $this) {
                $question->setIdQcm(null);
            }
        }

        return $this;
    }
}
