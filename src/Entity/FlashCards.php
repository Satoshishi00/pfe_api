<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FlashCardsRepository")
 */
class FlashCards
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $nb_cards = 0;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $recto_type = "text";

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $verso_type = "text";

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $recto_name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $verso_name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="flashCards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $id_creator;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Card", mappedBy="id_flash_cards")
     */
    private $cards;


       public function __construct()
    {
        $this->updated_at = new \DateTime("Europe/Paris");
        $this->created_at = new \DateTime("Europe/Paris");
        $this->cards = new ArrayCollection();
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

    public function getNbCards(): ?int
    {
        return $this->nb_cards;
    }

    public function setNbCards(int $nb_cards): self
    {
        $this->nb_cards = $nb_cards;

        return $this;
    }

    public function getRectoType(): ?string
    {
        return $this->recto_type;
    }

    public function setRectoType(string $recto_type): self
    {
        $this->recto_type = $recto_type;

        return $this;
    }

    public function getVersoType(): ?string
    {
        return $this->verso_type;
    }

    public function setVersoType(string $verso_type): self
    {
        $this->verso_type = $verso_type;

        return $this;
    }

    public function getRectoName(): ?string
    {
        return $this->recto_name;
    }

    public function setRectoName(string $recto_name): self
    {
        $this->recto_name = $recto_name;

        return $this;
    }

    public function getVersoName(): ?string
    {
        return $this->verso_name;
    }

    public function setVersoName(string $verso_name): self
    {
        $this->verso_name = $verso_name;

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

    public function getIdCreator(): ?User
    {
        return $this->id_creator;
    }

    public function setIdCreator(?User $id_creator): self
    {
        $this->id_creator = $id_creator;

        return $this;
    }

    /**
     * @return Collection|Card[]
     */
    public function getCards(): Collection
    {
        return $this->cards;
    }

    public function addCard(Card $card): self
    {
        if (!$this->cards->contains($card)) {
            $this->cards[] = $card;
            $card->setIdFlashCards($this);
        }

        return $this;
    }

    public function removeCard(Card $card): self
    {
        if ($this->cards->contains($card)) {
            $this->cards->removeElement($card);
            // set the owning side to null (unless already changed)
            if ($card->getIdFlashCards() === $this) {
                $card->setIdFlashCards(null);
            }
        }

        return $this;
    }
}
