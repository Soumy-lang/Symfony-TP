<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Favorite;
use App\Entity\Vehicle;
use App\Entity\User;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/favorite')]
class FavoriteController extends AbstractController
{
    #[Route('/toggle/{id}', name: 'app_favorite_toggle', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function toggleFavorite(
        Vehicle $vehicle,
        FavoriteRepository $favoriteRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        $existingFavorite = $favoriteRepository->findOneBy([
            'user' => $user,
            'vehicle' => $vehicle
        ]);

        if ($existingFavorite) {
            $entityManager->remove($existingFavorite);
            $message = "Véhicule retiré des favoris.";
        } else {
            $favorite = new Favorite();
            $favorite->setUser($user);
            $favorite->setVehicle($vehicle);
            $entityManager->persist($favorite);
            $message = "Véhicule ajouté aux favoris.";
        }

        $entityManager->flush();

        $this->addFlash('success', $message);
        return $this->redirectToRoute('app_vehicle_show', ['id' => $vehicle->getId()]);
    }

    #[Route('/list', name: 'app_favorite_list')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function list(FavoriteRepository $favoriteRepository): Response
    {
        $user = $this->getUser();
        $favorites = $favoriteRepository->findBy(['user' => $user]);

        return $this->render('favorite/list.html.twig', [
            'favorites' => $favorites
        ]);
    }

}
