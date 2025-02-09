<?php

namespace App\Entity;

use App\Repository\VehicleRepository;
use App\Entity\Reservation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleRepository::class)]
class Vehicle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $marque = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $immatriculation = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotNull(message: "Le prix journalier ne peut pas être vide.")]
    #[Assert\Range(
        min: 100,
        max: 500,
        notInRangeMessage: "Le prix doit être compris entre {{ min }}€ et {{ max }}€."
    )]
    private ?float $prixJournalier = null;

    #[ORM\Column(nullable: true)]
    private ?bool $disponible = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'vehicle')]
    private Collection $comment;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'vehicle')]
    private Collection $reservations;

    /**
     * @var Collection<int, VehicleImage>
     */
    #[ORM\OneToMany(targetEntity: VehicleImage::class, mappedBy: 'vehicle', cascade: ['persist', 'remove'])]
    private Collection $vehicleImages;

    /**
     * @var Collection<int, Favorite>
     */
    #[ORM\OneToMany(targetEntity: Favorite::class, mappedBy: 'vehicle')]
    private Collection $favorites;


    public function __construct()
    {
        $this->comment = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->vehicleImages = new ArrayCollection();
        $this->favorites = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(?string $marque): static
    {
        $this->marque = $marque;

        return $this;
    }

    public function getImmatriculation(): ?string
    {
        return $this->immatriculation;
    }

    public function setImmatriculation(?string $immatriculation): static
    {
        $this->immatriculation = $immatriculation;

        return $this;
    }

    public function getPrixJournalier(): ?float
    {
        return $this->prixJournalier;
    }

    public function setPrixJournalier(?float $prixJournalier): static
    {
        $this->prixJournalier = $prixJournalier;

        return $this;
    }

    public function isDisponible(): ?bool
    {
        return $this->disponible;
    }

    public function setDisponible(?bool $disponible): static
    {
        $this->disponible = $disponible;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComment(): Collection
    {
        return $this->comment;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comment->contains($comment)) {
            $this->comment->add($comment);
            $comment->setVehicle($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comment->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getVehicle() === $this) {
                $comment->setVehicle(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setVehicle($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getVehicle() === $this) {
                $reservation->setVehicle(null);
            }
        }

        return $this;
    }

    public function getNumberOfReservations(): int
    {
        return count($this->reservations); 
    }

    /**
     * @return Collection<int, VehicleImage>
     */
    public function getVehicleImages(): Collection
    {
        return $this->vehicleImages;
    }

    public function addVehicleImage(VehicleImage $vehicleImage): static
    {
        if (!$this->vehicleImages->contains($vehicleImage)) {
            $this->vehicleImages->add($vehicleImage);
            $vehicleImage->setVehicle($this);
        }

        return $this;
    }

    public function removeVehicleImage(VehicleImage $vehicleImage): static
    {
        if ($this->vehicleImages->removeElement($vehicleImage)) {
            // set the owning side to null (unless already changed)
            if ($vehicleImage->getVehicle() === $this) {
                $vehicleImage->setVehicle(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Favorite>
     */
    public function getFavorites(): Collection
    {
        return $this->favorites;
    }

    public function addFavorite(Favorite $favorite): static
    {
        if (!$this->favorites->contains($favorite)) {
            $this->favorites->add($favorite);
            $favorite->setVehicle($this);
        }

        return $this;
    }

    public function removeFavorite(Favorite $favorite): static
    {
        if ($this->favorites->removeElement($favorite)) {
            // set the owning side to null (unless already changed)
            if ($favorite->getVehicle() === $this) {
                $favorite->setVehicle(null);
            }
        }

        return $this;
    }
}
