<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Soiree;
use App\Form\AddParticipantType;
use App\Form\AddSoireeType;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\String_;
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


    #[Route('/soiree/add', name: 'soiree_add')]
    public function soiree_add(Request $request, ManagerRegistry $doctrine)
    {
        $soiree = new Soiree;

        $form = $this->createForm(AddSoireeType::class, $soiree);

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
            'formadd' => $form->createView()
        ]);
    }

    #[Route('soiree/entered/{id}', name: 'soiree_entered')]
    public function soiree_entered($id, Request $request, ManagerRegistry $doctrine)
    {

        $participants = $doctrine->getRepository(Participant::class)->findAll();
        $listeParticipants = array();
        for ($i = 0; $i < count($participants); $i++) {

            if ($participants[$i]->getIdSoiree() == $id) {
                $listeParticipants[$i] = $participants[$i];
            }
        }
        $thisSoiree = $doctrine->getRepository(Soiree::class)->findOneBy(['id' => $id]);


        return $this->render('soiree/Entered.html.twig', [
            'listeParticipants' => $listeParticipants,
            'idSoiree' => $thisSoiree,
        ]);
    }

    #[Route('/soiree/update/{id}', name: 'soiree_update')]
    public function soiree_update(Request $request, ManagerRegistry $doctrine, int $id)
    {

        $soiree = $doctrine->getRepository(Soiree::class)->findOneBy(['id' => $id]);

        $form = $this->createForm(AddSoireeType::class, $soiree);

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

        return $this->render('soiree/update.html.twig', [
            'formSoireeUpdate' => $form->createView()
        ]);
    }


    #[Route('soiree/delete/{id}', name: 'soiree_delete')]
    public function soiree_delete($id, Request $request, ManagerRegistry $doctrine)
    {

        $em = $doctrine->getManager();
        $em2 = $doctrine->getManager();
        $soiree = $em->getRepository(Soiree::class)->find($id);
        $participant = $em2->getRepository(Participant::class)->findBy(['idSoiree' => $id]);
        for ($i = 0; $i < count($participant); $i++) {
            $em2->remove($participant[$i]);
        }
        $em2->flush();
        $em->remove($soiree);
        $em->flush();

        return $this->redirectToRoute("soiree");

    }

    #[Route('soiree/result/{id}', name: 'soiree_calcul')]
    public function tricount_calcul(int $id, Request $request, ManagerRegistry $doctrine)
    {

        $participantsAlaSoiree = $doctrine->getRepository(Participant::class)->findBy(['idSoiree' => $id]);
        $moy = 0;


        $participantsDonneur = array();
        $participantsDejaDonne = array();
        $participantsReceveur = array();
        $participantsRAS = array();
        $stringmessage = '';
        $message = array();

        for ($i = 0; $i < count($participantsAlaSoiree); $i++) {
            $moy += $participantsAlaSoiree[$i]->getMontant();
        }
        $moy = $moy / count($participantsAlaSoiree);
        for ($i = 0; $i < count($participantsAlaSoiree); $i++) {
            if ($participantsAlaSoiree[$i]->getMontant() < $moy) {
                array_push($participantsDonneur, $participantsAlaSoiree[$i]);
            } else if ($participantsAlaSoiree[$i]->getMontant() > $moy) {
                array_push($participantsReceveur, $participantsAlaSoiree[$i]);
            } else {
                array_push($participantsRAS, $participantsAlaSoiree[$i]);
            }
        }
        $skip = false;
        $argentDonne = 0;

        if (count($participantsAlaSoiree) > 1) {
            foreach ($participantsReceveur as $receveur) {
                $argentArecevoir = $receveur->getMontant() - $moy;
                $j = 0;
                unset($participantsDejaDonne);
                $participantsDejaDonne = array();
                while ($argentArecevoir != 0) {
                    foreach ($participantsDejaDonne as $item) {
                        if ($item->getId() == $participantsDonneur[$j]->getId()) {
                            $skip = true;
                            break;
                        } else {
                            $skip = false;
                        }
                    }
                    if ($argentDonne == 0) {
                        $argentDonne = $moy - $participantsDonneur[$j]->getMontant();
                    }
                    if ($receveur->getMontant() - $argentDonne > $moy && !$skip && $argentDonne != 0) {
                        $nomDonneur = $participantsDonneur[$j]->getNom();
                        $nomReceveur = $receveur->getNom();
                        $stringmessage = $nomDonneur . ' doit ' . $argentDonne . ' € à ' . $nomReceveur;
                        array_push($message, $stringmessage);
                        array_push($participantsDejaDonne, $participantsDonneur[$j]);


                        $argentArecevoir -= $argentDonne;

                        if ($argentArecevoir <= 0) {
                            $argentDonne = 0;
                            break;
                        } else {
                            $argentDonne = 0;
                        }


                    } else if ($receveur->getMontant() - $argentDonne < $moy && !$skip && $argentDonne != 0) {
                        $nomDonneur = $participantsDonneur[$j]->getNom();
                        $nomReceveur = $receveur->getNom();
                        $stringmessage = $nomDonneur . ' doit ' . $argentArecevoir . ' € à ' . $nomReceveur;
                        array_push($message, $stringmessage);
                        $argentDonne -= $argentArecevoir;
                        $argentArecevoir = 0;
                        break;
                    } else if ($receveur->getMontant() - $argentDonne == $moy && !$skip && $argentDonne != 0) {
                        $nomDonneur = $participantsDonneur[$j]->getNom();
                        $nomReceveur = $receveur->getNom();
                        $stringmessage = $nomDonneur . ' doit ' . $argentDonne . ' € à ' . $nomReceveur;
                        array_push($message, $stringmessage);
                        array_push($participantsDejaDonne, $participantsDonneur[$j]);
                        $argentArecevoir -= $argentDonne;
                        $argentDonne = 0;
                    }
                    if ($argentArecevoir == 0) {
                        break;
                    }
                    if ($j > count($participantsDonneur)) {
                        $j = 0;
                    } else if ($i == count($participantsDonneur)) {
                        break;
                    } else {
                        $j++;
                    }
                }
            }
        } else {
            $stringmessage = 'pas assez de participant dans la soirée pour effectuer un calcul';
            array_push($message, $stringmessage);
        }
        return $this->render('soiree/result.html.twig', [
            'AllMessages' => $message,
        ]);
    }

}
