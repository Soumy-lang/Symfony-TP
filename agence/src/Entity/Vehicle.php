<?php

namespace App\Entity;

use App\Repository\VehicleRepository;
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
    private ?string $brand = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $registrationNumber = null;

    #[ORM\Column(nullable: true)]
    private ?float $dailyPrice = null;

    #[ORM\Column(nullable: true)]
    private ?bool $availabilityStatus = null;

    #[ORM\OneToMany(targetEntity:"App\Entity\Comment", mappedBy:"vehicle")]
    #[ORM\JoinColumn(nullable: false)]
    private Collection $comments;


    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'vehicle')]
    private Collection $vehicle;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(?string $registrationNumber): static
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    public function getDailyPrice(): ?float
    {
        return $this->dailyPrice;
    }

    public function setDailyPrice(?float $dailyPrice): static
    {
        $this->dailyPrice = $dailyPrice;

        return $this;
    }

    public function isAvailabilityStatus(): ?bool
    {
        return $this->availabilityStatus;
    }

    public function setAvailabilityStatus(?bool $availabilityStatus): static
    {
        $this->availabilityStatus = $availabilityStatus;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getVehicle(): Collection
    {
        return $this->vehicle;
    }

    public function addVehicle(Reservation $vehicle): static
    {
        if (!$this->vehicle->contains($vehicle)) {
            $this->vehicle->add($vehicle);
            $vehicle->setVehicle($this);
        }

        return $this;
    }

    public function removeVehicle(Reservation $vehicle): static
    {
        if ($this->vehicle->removeElement($vehicle)) {
            // set the owning side to null (unless already changed)
            if ($vehicle->getVehicle() === $this) {
                $vehicle->setVehicle(null);
            }
        }

        return $this;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setVehicle($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            if ($comment->getVehicle() === $this) {
                $comment->setVehicle(null);
            }
        }

        return $this;
    }

}
