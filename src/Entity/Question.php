<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuestionRepository")
 */
class Question
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Qcm", inversedBy="questions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $id_qcm;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $question_response;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $parent;

    /**
     * @ORM\Column(type="boolean")
     */
    private $good_rep = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $advice = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdQcm(): ?Qcm
    {
        return $this->id_qcm;
    }

    public function setIdQcm(?Qcm $id_qcm): self
    {
        $this->id_qcm = $id_qcm;

        return $this;
    }

    public function getQuestionResponse(): ?string
    {
        return $this->question_response;
    }

    public function setQuestionResponse(string $question_response): self
    {
        $this->question_response = $question_response;

        return $this;
    }

    public function getParent(): ?int
    {
        return $this->parent;
    }

    public function setParent(?int $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getGoodRep(): ?bool
    {
        return $this->good_rep;
    }

    public function setGoodRep(bool $good_rep): self
    {
        $this->good_rep = $good_rep;

        return $this;
    }

    public function getAdvice(): ?string
    {
        return $this->advice;
    }

    public function setAdvice(?string $advice): self
    {
        $this->advice = $advice;

        return $this;
    }
}
