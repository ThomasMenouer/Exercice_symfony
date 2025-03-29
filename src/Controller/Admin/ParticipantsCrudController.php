<?php

namespace App\Controller\Admin;

use App\Entity\Participants;
use App\Form\Admin\ParticipantsType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ParticipantsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/participants', name: 'admin_participants_')]
final class ParticipantsCrudController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(ParticipantsRepository $participantsRepository): Response
    {
        return $this->render('admin/participants/index.html.twig', [
            'participants' => $participantsRepository->findAll(),
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $participant = new Participants();
        $form = $this->createForm(ParticipantsType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Vérifier si le participant est déjà inscrit à un atelier
            $existingAtelier = $entityManager->getRepository(Participants::class)->findOneBy([
                'atelier' => $participant->getAtelier(),
                'email' => $participant->getEmail(),
            ]);
            if ($existingAtelier) {
                $this->addFlash('error', 'Ce participant est déjà inscrit à cet atelier.');
                return $this->redirectToRoute('admin_participants_create');
            }

            //Vérifier si l'atelier est complet
            $atelier = $participant->getAtelier();
            if ($atelier->getParticipants()->count() >= $atelier->getPlacesMax()) {
                $this->addFlash('error', 'Cet atelier est déjà complet.');
                return $this->redirectToRoute('admin_participants_create');
            }

            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Participant inscrit.');
            return $this->redirectToRoute('admin_participants_index');
        }

        return $this->render('admin/participants/create.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show')]
    public function show(Participants $participant): Response
    {
        return $this->render('admin/participants/show.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Participants $participant, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ParticipantsType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Vérifier si le participant est déjà inscrit à un atelier
            $existingAtelier = $entityManager->getRepository(Participants::class)->findOneBy([
                'atelier' => $participant->getAtelier(),
                'email' => $participant->getEmail(),
            ]);
            if ($existingAtelier) {
                $this->addFlash('error', 'Ce participant est déjà inscrit à cet atelier.');
                return $this->redirectToRoute('admin_participants_create');
            }

            //Vérifier si l'atelier est complet
            $atelier = $participant->getAtelier();
            if ($atelier->getParticipants()->count() >= $atelier->getPlacesMax()) {
                $this->addFlash('error', 'Cet atelier est déjà complet.');
                return $this->redirectToRoute('admin_participants_create');
            }

            $entityManager->flush();

            return $this->redirectToRoute('admin_participants_index');
        }

        return $this->render('admin/participants/edit.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Participants $participant, EntityManagerInterface $em): Response
    {
        // Vérifier le token CSRF
        $submitted_token = $request->getPayload()->get('token');

        if($this->isCsrfTokenValid('delete-participant', $submitted_token))
        {
            $em->remove($participant);
            $em->flush();

            $this->addFlash('success', 'Participant supprimé.');
            return $this->redirectToRoute('admin_participants_index');
        }

        $this->addFlash('danger', 'Échec de la suppression');
        return $this->redirectToRoute('admin_participants_index');
    }
}
