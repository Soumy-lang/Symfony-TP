<?php

namespace App\Controller;

use App\Entity\Vehicle;
use App\Entity\Reservation;
use App\Form\Vehicle1Type;
use App\Entity\VehicleImage;
use App\Repository\VehicleRepository;
use App\Repository\FavoriteRepository;
use App\Repository\ReservationRepository;
use App\Entity\Comment;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;


final class VehicleController extends AbstractController
{
    #[Route('/vehicle', name: 'app_vehicle_index', methods: ['GET'])]
    public function index(VehicleRepository $vehicleRepository, Request $request): Response
    {
        $marque = $request->query->get('marque');
        $prixMin = $request->query->get('prix_min');
        $prixMax = $request->query->get('prix_max');
        $disponible = $request->query->get('disponible');

        $disponible = ($disponible !== null && $disponible !== '') ? (bool) $disponible : null;
        $vehicles = $vehicleRepository->findByFilters($marque, $disponible, $prixMin, $prixMax);

        return $this->render('vehicle/index.html.twig', [
            'vehicles' => $vehicles,
        ]);
    }


    #[Route('/vehicle/new', name: 'app_vehicle_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $vehicle = new Vehicle();
        $form = $this->createForm(Vehicle1Type::class, $vehicle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $images = $form->get('images')->getData();

            if ($images) {
                foreach ($images as $imageFile) {
                    $fileName = md5(uniqid()) . '.' . $imageFile->guessExtension();

                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );

                    $vehicleImage = new VehicleImage();
                    $vehicleImage->setFileName($fileName);
                    $vehicle->addVehicleImage($vehicleImage);
                }
            }

            $entityManager->persist($vehicle);
            $entityManager->flush();

            return $this->redirectToRoute('app_vehicle_index');
        }

        return $this->render('vehicle/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/vehicle/{id}', name: 'app_vehicle_show')]
    public function show(ReservationRepository $reservationRepository, 
    VehicleRepository $vehicleRepository, 
    FavoriteRepository $favoriteRepository,
    Request $request, 
    Vehicle $vehicle, 
    EntityManagerInterface $entityManager): Response
    {

        $comment = new Comment();
        $comment->setVehicle($vehicle); 

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('app_vehicle_show', ['id' => $vehicle->getId()]);
        }

        $averageRating = $vehicleRepository->getAverageRating($vehicle->getId());

        $user = $this->getUser();
        $hasRented = false;

        $favoriteVehicleIds = [];

        if ($user) {
            $hasRented = $reservationRepository->hasUserRentedVehicle($user->getId(), $vehicle->getId());

            $favorites = $favoriteRepository->findBy(['user' => $user]);
            if ($favorites) {
                $favoriteVehicleIds = array_map(fn ($fav) => $fav->getVehicle()->getId(), $favorites);
            }
        }

        $numberOfReservations = $vehicle->getNumberOfReservations();

        return $this->render('vehicle/show.html.twig', [
            'vehicle' => $vehicle,
            'comments' => $vehicle->getComment(),
            'form' => $form->createView(),
            'averageRating' => $averageRating,
            'hasRented' => $hasRented,
            'numberOfReservations' => $numberOfReservations,
            'favoriteVehicleIds' => $favoriteVehicleIds
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vehicle_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Vehicle $vehicle, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Vehicle1Type::class, $vehicle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_vehicle_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vehicle/edit.html.twig', [
            'vehicle' => $vehicle,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vehicle_delete', methods: ['POST'])]
    public function delete(Request $request, Vehicle $vehicle, EntityManagerInterface $entityManager): Response
    {
        $comments = $vehicle->getComment();
        if ($vehicle->getComment()->count() > 0) {
            foreach ($comments as $comment) {
                $entityManager->remove($comment);
            }
        }

        $reservations = $vehicle->getReservations(); 
        if ($reservations->count() > 0) {
            foreach ($reservations as $reservation) {
                $entityManager->remove($reservation);
            }
        }

        try {
            $entityManager->remove($vehicle);
            $entityManager->flush();
            $this->addFlash('success', 'Véhicule supprimé avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Impossible de supprimer ce véhicule.');
        }

        return $this->redirectToRoute('app_vehicle_index');
    }

}
