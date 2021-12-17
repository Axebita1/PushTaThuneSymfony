<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Soiree;
use App\Form\AddParticipantType;
use App\Form\AddSoireeType;
use phpDocumentor\Reflection\Types\Array_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SoireeController extends AbstractController
{
    #[Route('/soiree', name: 'soiree')]
    public function index(ManagerRegistry $doctrine): Response
    {

        $soirees = $doctrine->getRepository(Soiree::class)->findAll();

        return $this->render('soiree/index.html.twig', [
            'soirees' => $soirees,
        ]);
    }


    #[Route('/soiree/add', name:'soiree_add')]
    public function soiree_add(Request $request, ManagerRegistry $doctrine){
        $soiree = new Soiree;

        $form  = $this->createForm(AddSoireeType::class, $soiree);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //entity manager
            $em = $doctrine->getManager();
            //entity manager save categorie (persist)
            $em->persist($soiree);
            //declenche la request
            $em->flush();
            return $this->redirectToRoute("soiree");
        }

        return $this->render('soiree/add.html.twig', [
            'formadd'=>$form->createView()
        ]);
    }




    #[Route('soiree/delete/{id}', name:'soiree_delete')]
    public function soiree_delete($id, Request $request, ManagerRegistry $doctrine){

        $em = $doctrine->getManager();
        $em2= $doctrine->getManager();
        $soiree = $em->getRepository(Soiree::class)->find($id);
        $participant = $em2->getRepository(Participant::class)->findBy(['idSoiree' => $id]);
        for($i =0 ; $i<count($participant); $i++){
            $em2->remove($participant[$i]);
        }
        $em2->flush();
        $em->remove($soiree);
        $em->flush();

        return $this->redirectToRoute("soiree");

    }

    #[Route('soiree/entered/{id}', name:'soiree_entered')]
    public function soiree_entered($id, Request $request, ManagerRegistry $doctrine){

        $participants = $doctrine->getRepository(Participant::class)->findAll();
        $listeParticipants= array();
        for ($i = 0 ; $i < count($participants); $i ++){

            if ($participants[$i]->getIdSoiree() == $id){
                $listeParticipants[$i]= $participants[$i];
            }
        }
        $thisSoiree = $doctrine->getRepository(Soiree::class)->findOneBy(['id' => $id]);


        return $this->render('soiree/Entered.html.twig', [
            'listeParticipants' => $listeParticipants,
            'idSoiree' => $thisSoiree,
        ]);
    }

}
