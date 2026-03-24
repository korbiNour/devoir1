<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Form\ProjetType;
use App\Form\ProjetFilterType;
use App\Repository\ProjetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/projet')]
final class ProjetController extends AbstractController
{
    #[Route('/', name: 'app_projet')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        // Créer le formulaire de filtre
        $form = $this->createForm(ProjetFilterType::class);
        $form->handleRequest($request);

        // Créer le QueryBuilder pour récupérer les projets
        $qb = $em->getRepository(Projet::class)->createQueryBuilder('p');

        // Filtrage si le formulaire est soumis
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Filtrer par titre
            if (!empty($data['titre'])) {
                $qb->andWhere('p.titre LIKE :titre')
                   ->setParameter('titre', '%'.$data['titre'].'%');
            }

            // Filtrer par statut
            if (!empty($data['statut'])) {
                $qb->andWhere('p.statut = :statut')
                   ->setParameter('statut', $data['statut']);
            }
        }

        $projets = $qb->getQuery()->getResult();

        return $this->render('projet/index.html.twig', [
            'projets' => $projets,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'app_projet_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $projet = new Projet();
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($projet);
            $em->flush();

            $this->addFlash('success', 'Projet créé avec succès !');

            return $this->redirectToRoute('app_projet');
        }

        return $this->render('projet/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_projet_show')]
    public function show(Projet $projet): Response
    {
        return $this->render('projet/show.html.twig', [
            'projet' => $projet,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_projet_edit')]
    public function edit(Request $request, Projet $projet, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Projet modifié avec succès !');

            return $this->redirectToRoute('app_projet');
        }

        return $this->render('projet/edit.html.twig', [
            'form' => $form->createView(),
            'projet' => $projet,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_projet_delete', methods: ['POST'])]
    public function delete(Request $request, Projet $projet, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$projet->getId(), $request->request->get('_token'))) {
            $em->remove($projet);
            $em->flush();

            $this->addFlash('success', 'Projet supprimé avec succès !');
        }

        return $this->redirectToRoute('app_projet');
    }
}