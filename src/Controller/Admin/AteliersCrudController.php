<?php

namespace App\Controller\Admin;

use App\Entity\Ateliers;
use App\Form\AteliersType;
use App\Repository\AteliersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/ateliers', name: 'admin_ateliers_')]
final class AteliersCrudController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(AteliersRepository $ateliers_repository): Response
    {

        return $this->render('admin/ateliers/index.html.twig', [
            'list_ateliers' => $ateliers_repository->findAll(),
        ]);
    }

    // CREATE
    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $atelier = new Ateliers();
        
        $form = $this->createForm(AteliersType::class, $atelier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $atelier = $form->getData();
            $em->persist($atelier);
            $em->flush();

            return $this->redirectToRoute('admin_ateliers_index');
        }

        return $this->render('admin/ateliers/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // READ
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Ateliers $ateliers): Response
    {

        return $this->render('admin/ateliers/show.html.twig', [
            'atelier' => $ateliers,
        ]);
    }

    // UPDATE
    #[Route('/{id}/update', name: 'update', methods: ['GET', 'POST'])]
    public function update(int $id, Request $request, EntityManagerInterface $em, Ateliers $ateliers ): Response
    {

        $form = $this->createForm(AteliersType::class, $ateliers);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if($ateliers->getPlacesMax() <= $ateliers->getParticipants()->count()){
                $this->addFlash('danger', 'Le nombre de participants est atteint');
                return $this->redirectToRoute('admin_ateliers_show', ['id' => $id]);
            }
            $em->flush();

            return $this->redirectToRoute('admin_ateliers_index');
        }

        return $this->render('admin/ateliers/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // DELETE
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $em, Ateliers $ateliers): Response
    {
        // Vérifier le token CSRF
        if ($this->isCsrfTokenValid('delete' . $ateliers->getId(), $request->request->get('_token'))) {
            $em->remove($ateliers);
            $em->flush();
        }

        return $this->redirectToRoute('admin_ateliers_index');
    }
}
