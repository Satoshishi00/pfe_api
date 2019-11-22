<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CardRepository")
 */
class Card
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\FlashCards", inversedBy="cards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $id_flash_cards;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $recto;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $verso;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Media", mappedBy="id_card", cascade={"persist", "remove"})
     */
    private $media;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdFlashCards(): ?FlashCards
    {
        return $this->id_flash_cards;
    }

    public function setIdFlashCards(?FlashCards $id_flash_cards): self
    {
        $this->id_flash_cards = $id_flash_cards;

        return $this;
    }

    public function getRecto(): ?string
    {
        return $this->recto;
    }

    public function setRecto(string $recto): self
    {
        $this->recto = $recto;

        return $this;
    }

    public function getVerso(): ?string
    {
        return $this->verso;
    }

    public function setVerso(string $verso): self
    {
        $this->verso = $verso;

        return $this;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): self
    {
        $this->media = $media;

        // set (or unset) the owning side of the relation if necessary
        $newId_card = $media === null ? null : $this;
        if ($newId_card !== $media->getIdCard()) {
            $media->setIdCard($newId_card);
        }

        return $this;
    }
}
