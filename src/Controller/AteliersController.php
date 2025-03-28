<?php

namespace App\Controller;

use App\Entity\Ateliers;
use App\Form\AteliersType;
use App\Entity\Participants;
use App\Form\ParticipantsType;
use App\Repository\AteliersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/ateliers', name: 'ateliers_')]
final class AteliersController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(AteliersRepository $ateliers_repository): Response
    {
        $list_ateliers = $ateliers_repository->findAll();

        return $this->render('ateliers/index.html.twig', [
            'list_ateliers' => $list_ateliers,
        ]);
    }

    #[Route('/{id}', name: 'show')]
    public function show(Ateliers $ateliers): Response
    {

        return $this->render('ateliers/show.html.twig', [
            'atelier' => $ateliers,
        ]);
    }

    #[Route('/{id}/inscription', name: 'inscription')]
    public function inscription(int $id, Ateliers $ateliers, Request $request, EntityManagerInterface $em): Response
    {
        $participant = new Participants();
        $participant->setAtelier($ateliers);

        $form = $this->createForm(ParticipantsType::class, $participant, [
            'atelier' => $ateliers,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Vérifier que le nombre de participants n'est pas dépassé
            $participants = $ateliers->getParticipants();
            if (count($participants) >= $ateliers->getPlacesMax()) {
                $this->addFlash('danger', 'Le nombre de participants est atteint');
                return $this->redirectToRoute('ateliers_show', ['id' => $id]);
            }

            // Vérifier que le participant n'est pas déjà inscrit
            $participantExist = $em->getRepository(Participants::class)->findOneBy([
                'email' => $participant->getEmail(),
                'atelier' => $ateliers,
            ]);

            if ($participantExist) {
                $this->addFlash('danger', 'Vous êtes déjà inscrit');
                return $this->redirectToRoute('ateliers_show', ['id' => $id]);
            }
            
            $em->persist($participant);
            $em->flush();

            return $this->redirectToRoute('ateliers_show', ['id' => $id]);
        }
        
        return $this->render('ateliers/inscription.html.twig', [
            'atelier' => $ateliers,
            'form' => $form->createView(),
        ]);
    }
}
