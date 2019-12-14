<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuizzQuestionRepository")
 */
class QuizzQuestion
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Quizz", inversedBy="quizzQuestions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $quizz;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $question;

    /**
     * @ORM\Column(type="string", length=127)
     */
    private $rep1;

    /**
     * @ORM\Column(type="string", length=127)
     */
    private $rep2;

    /**
     * @ORM\Column(type="string", length=127)
     */
    private $rep3;

    /**
     * @ORM\Column(type="string", length=127)
     */
    private $rep4;

    /**
     * @ORM\Column(type="integer")
     */
    private $right_answer;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuizz(): ?Quizz
    {
        return $this->quizz;
    }

    public function setQuizz(?Quizz $quizz): self
    {
        $this->quizz = $quizz;

        return $this;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getRep1(): ?string
    {
        return $this->rep1;
    }

    public function setRep1(string $rep1): self
    {
        $this->rep1 = $rep1;

        return $this;
    }

    public function getRep2(): ?string
    {
        return $this->rep2;
    }

    public function setRep2(string $rep2): self
    {
        $this->rep2 = $rep2;

        return $this;
    }

    public function getRep3(): ?string
    {
        return $this->rep3;
    }

    public function setRep3(string $rep3): self
    {
        $this->rep3 = $rep3;

        return $this;
    }

    public function getRep4(): ?string
    {
        return $this->rep4;
    }

    public function setRep4(string $rep4): self
    {
        $this->rep4 = $rep4;

        return $this;
    }

    public function getRightAnswer(): ?int
    {
        return $this->right_answer;
    }

    public function setRightAnswer(int $right_answer): self
    {
        $this->right_answer = $right_answer;

        return $this;
    }
}
