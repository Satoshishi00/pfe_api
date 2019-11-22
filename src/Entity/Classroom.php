<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClassroomRepository")
 */
class Classroom
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="classrooms")
     * @ORM\JoinColumn(nullable=false)
     */
    private $leader;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $nb_qcm = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $nb_flash_cards = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLeader(): ?User
    {
        return $this->leader;
    }

    public function setLeader(?User $leader): self
    {
        $this->leader = $leader;

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

    public function getNbQcm(): ?int
    {
        return $this->nb_qcm;
    }

    public function setNbQcm(int $nb_qcm): self
    {
        $this->nb_qcm = $nb_qcm;

        return $this;
    }

    public function getNbFlashCards(): ?int
    {
        return $this->nb_flash_cards;
    }

    public function setNbFlashCards(int $nb_flash_cards): self
    {
        $this->nb_flash_cards = $nb_flash_cards;

        return $this;
    }
}
