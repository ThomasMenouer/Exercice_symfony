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
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AteliersController extends AbstractController
{
    #[Route('/', name: 'ateliers')]
    public function index(AteliersRepository $ateliers_repository): Response
    {
        $list_ateliers = $ateliers_repository->findAll();

        return $this->render('ateliers/index.html.twig', [
            'list_ateliers' => $list_ateliers,
        ]);
    }

    // CREATE
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/ateliers/create', name: 'ateliers_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $atelier = new Ateliers();
        
        $form = $this->createForm(AteliersType::class, $atelier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $atelier = $form->getData();
            $em->persist($atelier);
            $em->flush();

            return $this->redirectToRoute('ateliers');
        }

        return $this->render('ateliers/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // READ
    #[Route('/ateliers/{id}', name: 'ateliers_show')]
    public function show(int $id, AteliersRepository $ateliers_repository, Ateliers $ateliers): Response
    {

        return $this->render('ateliers/show.html.twig', [
            'atelier' => $ateliers,
        ]);
    }

    #[Route('/ateliers/{id}/inscription', name: 'ateliers_inscription')]
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
            else{
                $em->persist($participant);
                $em->flush();
            }

            return $this->redirectToRoute('ateliers_show', ['id' => $id]);
        }
        

        return $this->render('ateliers/inscription.html.twig', [
            'atelier' => $ateliers,
            'form' => $form->createView(),
        ]);
    }

    // UPDATE
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/ateliers/{id}/update', name: 'ateliers_update')]
    public function update(int $id, Request $request, EntityManagerInterface $em, Ateliers $ateliers ): Response
    {

        $form = $this->createForm(AteliersType::class, $ateliers);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('ateliers');
        }

        return $this->render('ateliers/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // DELETE
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/ateliers/{id}/delete', name: 'ateliers_delete')]
    public function delete(EntityManagerInterface $em, Ateliers $ateliers): Response
    {
        $em->remove($ateliers);
        $em->flush();

        return $this->redirectToRoute('ateliers');
    }
}
