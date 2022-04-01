<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Form\AppointmentType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class DoctorController extends AbstractController
{
    #[Route('/doctor/{id<[0-9]+>}', name: 'app_doctor')]
    /**
     * @Security("is_granted('ROLE_USER')")
     *
     * @return Response
     */
    public function index(Request $request, $id, UserRepository $ur, EntityManagerInterface $em): Response
    { 
        //@IsGranted("ROLE_USER")
        
        /* if ( !$this->getUser() ) {//Changer par annotation pour eviter la redondance
            $this->addFlash('error', 'Vous devez connectéz'); 
            
            return $this->redirectToRoute('app_login');
        }
        else {
            return $this->render('doctor/index.html.twig', [
                'controller_name' => 'DoctorController',
            ]);
        } */
        $appointment = new Appointment;
        $doctor =$ur->find($id);

        $appointment_form = $this->createForm(AppointmentType::class, $appointment);
        $appointment_form->handleRequest($request);
        
        if ($appointment_form->isSubmitted() && $appointment_form->isValid()) { 
            $appointment->setPractitioner($doctor);
            $appointment->setPatient($this->getUser());
            $appointment->setStatus(0);
            $appointment->setCreatedAt(new \DateTime());
            $appointment->setupdatedAt(new \DateTime());

            //dd($appointment);

            $em->persist($appointment);
            $em->flush();

            $this->addFlash('success', 'Votre rendez-vous a été bien crée');
        }

        return $this->render('doctor/index.html.twig', [
            'doctor' => $doctor,
            'appointment_form' => $appointment_form->createView()
        ]);


    }
}
