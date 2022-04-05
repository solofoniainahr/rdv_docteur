<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Form\AppointmentType;
use App\Repository\AppointmentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Encoder\JsonEncode;

class DoctorController extends AbstractController
{
    #[Route('/doctor/{id<[0-9]+>}', name: 'app_doctor')]
    /**
     * @Security("is_granted('ROLE_USER')")
     *
     * @return Response
     */
    public function index(Request $request, $id, 
                            UserRepository $ur, 
                            EntityManagerInterface $em,
                            AppointmentRepository $ar): Response
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
            'appointment_form' => $appointment_form->createView(),
        ]);
    }

    /**
     * @Route("/appointment/load/{doctorId<[0-9]+>}",name="app_load_appointment")
     * 
     * @Security("is_granted('ROLE_USER')")
     *
     * @return void
     */
    public function loadAppointment(Request $request, 
                                    AppointmentRepository $ar,
                                    UserRepository $ur, 
                                    $doctorId){
        $rdvs = []; 
        
        $doctor =$ur->find($doctorId);
        $dataRdv = [];
        $appointments = $ar->findBy(['practitioner' => $doctor->getId()]);
        if (count($appointments) ) {
            foreach ($appointments as $rdv) {
                $rdvs[] = [
                    'id'              => $rdv->getId(),
                    'title'           => $rdv->getTitle(),
                    'description'     => $rdv->getDescription(),
                    'start'           => $rdv->getStart()->format('Y-m-d H:i:s'),
                    'end'             => $rdv->getEnd()->format('Y-m-d H:i:s'),
                    'allDay'          => $rdv->getAllday(),
                    'backgroundColor' => $rdv->getBackgroundColor(),
                    'borderColor'     => $rdv->getBorderColor(),
                    'textColor'       => $rdv->getTextColor()
                ];
            }
            $dataRdv = json_encode($rdvs);
        }

        return new Response($dataRdv);
        
    }
}


