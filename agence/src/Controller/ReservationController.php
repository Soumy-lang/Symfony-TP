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


class ReservationController extends AbstractController
{

    #[Route('/reservation', name: 'app_reservation_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')] 
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
        $reservation = $reservationRepository->find($id);

        if (!$reservation) {
            throw $this->createNotFoundException('Réservation non trouvée');
        }

        if ($reservation->isCancelled()) {
            throw $this->createNotFoundException('La réservation a été annulée et ne peut plus être modifiée.');
        }

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reservation->calculateTotalPrice();  
            $entityManager->flush();  
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
        $reservation = $reservationRepository->find($id);

        if (!$reservation) {
            throw $this->createNotFoundException('Réservation non trouvée');
        }

        if ($reservation->isCancelled()) {
            throw $this->createNotFoundException('Cette réservation a déjà été annulée.');
        }

        $reservation->cancel();
        $entityManager->flush(); 

        return $this->redirectToRoute('app_reservation_index');  
    }


}

