<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Vehicle;
use App\Form\ReservationType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

// #[Route('/reservation', name: 'app_reservation')]
class ReservationController extends AbstractController
{

    #[Route('/reservation', name: 'app_reservation_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')] // Sécurise l'accès pour les utilisateurs connectés
    public function index(ReservationRepository $reservationRepository, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir vos réservations.');
        }

        $reservations = $reservationRepository->findBy(['user' => $user]);

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/new_reservation/{vehicleId}', name: 'app_reservation_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, $vehicleId): Response
    {
        $vehicle = $entityManager->getRepository(Vehicle::class)->find($vehicleId);

        if (!$vehicle) {
            throw $this->createNotFoundException('Véhicule non trouvé');
        }
        $reservation = new Reservation();
        $reservation->setVehicle($vehicle);
        $reservation->setUser($this->getUser());

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reservation->calculateTotalPrice();
            $entityManager->persist($reservation);
            $entityManager->flush();
            return $this->redirectToRoute('app_reservation_index', ['id' => $reservation->getId()]);
        }
        return $this->render('reservation/new.html.twig', [
            'form' => $form->createView(),
            'vehicle' => $vehicle,
        ]);
    }

    #[Route('/reservation/edit/{id}', name: 'app_reservation_update', methods: ['GET', 'POST'])]
    public function update(Request $request, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository, $id): Response
    {
        // Récupérer la réservation à partir de l'id
        $reservation = $reservationRepository->find($id);

        if (!$reservation) {
            throw $this->createNotFoundException('Réservation non trouvée');
        }

        // Vérifier si la réservation est déjà annulée
        if ($reservation->isCancelled()) {
            throw $this->createNotFoundException('La réservation a été annulée et ne peut plus être modifiée.');
        }

        // Créer le formulaire de modification
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reservation->calculateTotalPrice();  // Recalculer le prix total après modification
            $entityManager->flush();  // Sauvegarder les modifications
            return $this->redirectToRoute('app_reservation_index');
        }

        return $this->render('reservation/edit.html.twig', [
            'form' => $form->createView(),
            'reservation' => $reservation,
        ]);
    }

    #[Route('/reservation/cancel/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    public function cancel(Request $request, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository, $id): Response
    {
        // Récupérer la réservation à partir de l'id
        $reservation = $reservationRepository->find($id);

        if (!$reservation) {
            throw $this->createNotFoundException('Réservation non trouvée');
        }

        // Vérifier si la réservation peut être annulée (avant le début de la période)
        if ($reservation->isCancelled()) {
            throw $this->createNotFoundException('Cette réservation a déjà été annulée.');
        }

        // Annuler la réservation
        $reservation->cancel();
        $entityManager->flush();  // Sauvegarder l'annulation

        return $this->redirectToRoute('app_reservation_index');  // Retourner à la liste des réservations
    }


}

