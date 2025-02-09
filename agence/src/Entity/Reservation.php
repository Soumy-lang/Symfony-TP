<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    private ?Vehicle $vehicle = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    private ?User $user = null;

    #[Assert\NotNull(message: "La date de début est obligatoire.")]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $startDate = null;

    #[Assert\NotNull(message: "La date de fin est obligatoire.")]
    #[Assert\GreaterThan(
        propertyPath: "startDate",
        message: "La date de fin doit être postérieure à la date de début."
    )]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(nullable: true)]
    private ?float $totalPrice = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isCancelled = null;

    // public function calculateTotalPrice(): void
    // {
    //     $interval = $this->startDate->diff($this->endDate)->days;
    //     $this->totalPrice = $interval * $this->vehicle->getPrixJournalier();
    // }

    public function calculateTotalPrice(): void
    {
        $interval = $this->startDate->diff($this->endDate)->days;
        $this->totalPrice = $interval * $this->vehicle->getPrixJournalier();

        if ($this->totalPrice >= 400) {
            $this->totalPrice *= 0.9;  
        }
    }

    public function cancel(): void
    {
        $this->isCancelled = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicle $vehicle): static
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(?float $totalPrice): static
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function isCancelled(): ?bool
    {
        return $this->isCancelled;
    }

    public function setIsCancelled(?bool $isCancelled): static
    {
        $this->isCancelled = $isCancelled;

        return $this;
    }


}
