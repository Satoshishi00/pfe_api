<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"username"}, message="Il existe déjà un compte avec ce nom d'utilisateur")
 * @UniqueEntity(fields={"email"}, message="Il existe déjà un compte avec cet Email")
 *
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=120)
     * @Assert\Email(strict=true, checkMX=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $username;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="integer")
     */
    private $nb_classes = 0;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $roles = ["ROLE_USER"];

    /**
     * @ORM\Column(type="integer")
     */
    private $nb_qcm = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $nb_flash_cards = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $points = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $premium = false;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Media", mappedBy="id_user", cascade={"persist", "remove"})
     */
    private $image;

    /**
     * @ORM\Column(type="string", unique=true, length=255)
     */
    private $pepper='first';

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Qcm", mappedBy="id_creator")
     */
    private $qcms;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\FlashCards", mappedBy="id_creator")
     */
    private $flashCards;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token_password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Classroom", mappedBy="leader")
     */
    private $classrooms;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Quizz", mappedBy="creator")
     */
    private $quizz;



    public function __construct()
    {
        $this->updated_at = new \DateTime("Europe/Paris");
        $this->created_at = new \DateTime("Europe/Paris");
        $this->qcms = new ArrayCollection();
        $this->flashCards = new ArrayCollection();
        $this->classrooms = new ArrayCollection();
        $this->quizz = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNbClasses(): ?int
    {
        return $this->nb_classes;
    }

    public function setNbClasses(int $nb_classes): self
    {
        $this->nb_classes = $nb_classes;

        return $this;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function setRoles($roles): self
    {
        $this->roles = $roles;

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

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(int $points): self
    {
        $this->points = $points;

        return $this;
    }


    public function getPremium(): ?bool
    {
        return $this->premium;
    }

    public function setPremium(bool $premium): self
    {
        $this->premium = $premium;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function isEqualTo(UserInterface $user)
    {
        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->salt !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }

    public function getImage(): ?Media
    {
        return $this->image;
    }

    public function setImage(Media $image): self
    {
        $this->image = $image;

        // set the owning side of the relation if necessary
        if ($this !== $image->getIdUser()) {
            $image->setIdUser($this);
        }

        return $this;
    }

    public function getPepper(): ?string
    {
        return $this->pepper;
    }

    public function setPepper(string $pepper): self
    {
        $this->pepper = $pepper;

        return $this;
    }

    /**
     * @return Collection|Qcm[]
     */
    public function getQcms(): Collection
    {
        return $this->qcms;
    }

    public function addQcm(Qcm $qcm): self
    {
        if (!$this->qcms->contains($qcm)) {
            $this->qcms[] = $qcm;
            $qcm->setIdCreator($this);
        }

        return $this;
    }

    public function removeQcm(Qcm $qcm): self
    {
        if ($this->qcms->contains($qcm)) {
            $this->qcms->removeElement($qcm);
            // set the owning side to null (unless already changed)
            if ($qcm->getIdCreator() === $this) {
                $qcm->setIdCreator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|FlashCards[]
     */
    public function getFlashCards(): Collection
    {
        return $this->flashCards;
    }

    public function addFlashCard(FlashCards $flashCard): self
    {
        if (!$this->flashCards->contains($flashCard)) {
            $this->flashCards[] = $flashCard;
            $flashCard->setIdCreator($this);
        }

        return $this;
    }

    public function removeFlashCard(FlashCards $flashCard): self
    {
        if ($this->flashCards->contains($flashCard)) {
            $this->flashCards->removeElement($flashCard);
            // set the owning side to null (unless already changed)
            if ($flashCard->getIdCreator() === $this) {
                $flashCard->setIdCreator(null);
            }
        }

        return $this;
    }

    public function getTokenPassword(): ?string
    {
        return $this->token_password;
    }

    public function setTokenPassword(string $token_password): self
    {
        $this->token_password = $token_password;

        return $this;
    }

    /**
     * @return Collection|Classroom[]
     */
    public function getClassrooms(): Collection
    {
        return $this->classrooms;
    }

    public function addClassroom(Classroom $classroom): self
    {
        if (!$this->classrooms->contains($classroom)) {
            $this->classrooms[] = $classroom;
            $classroom->setLeader($this);
        }

        return $this;
    }

    public function removeClassroom(Classroom $classroom): self
    {
        if ($this->classrooms->contains($classroom)) {
            $this->classrooms->removeElement($classroom);
            // set the owning side to null (unless already changed)
            if ($classroom->getLeader() === $this) {
                $classroom->setLeader(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Quizz[]
     */
    public function getQuizz(): Collection
    {
        return $this->quizz;
    }

    public function addQuizz(Quizz $quizz): self
    {
        if (!$this->quizz->contains($quizz)) {
            $this->quizz[] = $quizz;
            $quizz->setCreator($this);
        }

        return $this;
    }

    public function removeQuizz(Quizz $quizz): self
    {
        if ($this->quizz->contains($quizz)) {
            $this->quizz->removeElement($quizz);
            // set the owning side to null (unless already changed)
            if ($quizz->getCreator() === $this) {
                $quizz->setCreator(null);
            }
        }

        return $this;
    }

}
