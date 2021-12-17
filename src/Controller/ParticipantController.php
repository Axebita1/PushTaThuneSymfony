<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Soiree;
use App\Form\AddParticipantType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{
    #[Route('/participant', name: 'participant')]
    public function index(): Response
    {
        return $this->render('participant/index.html.twig', [
            'controller_name' => 'ParticipantController',
        ]);
    }

    #[Route('/participant/add/{id}', name:'participant_add')]
    public function participant_add(Request $request, ManagerRegistry $doctrine, int $id){
        $participant = new Participant;

        $form  = $this->createForm(AddParticipantType::class, $participant);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //entity manager
            $em = $doctrine->getManager();
            //entity manager save categorie (persist)
            $participant->setIdSoiree($id);
            $em->persist($participant);

            //declenche la request
            $em->flush();
            return $this->redirectToRoute("soiree_entered",['id' => $id]);
        }

        return $this->render('participant/add.html.twig', [
            'form'=>$form->createView()
        ]);
    }
    #[Route('/participant/update/{idsoiree}/{id}', name:'participant_update')]
    public function participant_update(Request $request, ManagerRegistry $doctrine, int $id, int $idsoiree){

        $participant = $doctrine->getRepository(Participant::class)->findOneBy(['id' => $id]);

        $form  = $this->createForm(AddParticipantType::class, $participant);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //entity manager
            $em = $doctrine->getManager();
            //entity manager save categorie (persist)
            $em->persist($participant);

            //declenche la request
            $em->flush();
            return $this->redirectToRoute("soiree_entered",['id' => $idsoiree]);
        }

        return $this->render('participant/update.html.twig', [
            'formUpdate'=>$form->createView()
        ]);
    }

    #[Route('participant/delete/{idsoiree}/{id}', name:'participant_delete')]
    public function participant_delete(int $id, int $idsoiree, ManagerRegistry $doctrine){

        $em = $doctrine->getManager();
        $participant = $em->getRepository(Participant::class)->findOneBy(['id' => $id ]);

        $em->remove($participant);
        $em->flush();

        return $this->redirectToRoute("soiree_entered",['id' => $idsoiree]);

    }


}
