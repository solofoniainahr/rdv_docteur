<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Notification;
use App\Entity\User;
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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncode;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

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
                            AppointmentRepository $ar,
                            ): Response
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
        
        if ( $request->isXmlHttpRequest() ) {
            //$dataAppointment = json_decode($request->getContent());
            $appointment = $ar->find($request->query->get('id')); 
                if($appointment) {
                    $code = 200;
                    $startDate = substr($request->query->get('start'), 4, 20);
                    $dateStart=date_create($startDate);
                    
                    $appointment->setTitle($request->query->get('title'));
                    $appointment->setDescription($request->query->get('description'));
                    $appointment->setStart($dateStart);
                    $appointment->setBackgroundColor($request->query->get('backgroundColor'));
                    $appointment->setTextColor($request->query->get('textColor'));
                    $appointment->setBorderColor($request->query->get('borderColor'));
                    if ( filter_var($request->query->get('allDay'), FILTER_VALIDATE_BOOLEAN) ) {
                        $appointment->setEnd($dateStart);
                    }
                    else {
                        $endDate = substr($request->query->get('end'), 4, 20);
                        $dateEnd=date_create($endDate);
                        $appointment->setEnd($dateEnd);
                    }
                $appointment->setAllday(filter_var($request->query->get('allDay'), FILTER_VALIDATE_BOOLEAN));
                $em = $this->getDoctrine()->getManager();
                $em->flush();   
                
                return new Response('OKKK', $code);
        }
        else{
            return new Response('Les données sont incompletes', 404);
        }
            return new Response('ok');
        }
    
        $appointment = new Appointment;
        $doctor =$ur->find($id);

        $appointment_form = $this->createForm(AppointmentType::class, $appointment);
        $appointment_form->handleRequest($request);
        
        if ($appointment_form->isSubmitted() && $appointment_form->isValid()) { 
            $appointment->setPractitioner($doctor);
            $appointment->setPatient($this->getUser());
            $appointment->setIsRead(false);
            $appointment->setStatus(0);
            $appointment->setCreatedAt(new \DateTime());
            $appointment->setupdatedAt(new \DateTime());

            $em->persist($appointment);
            $em->flush();
            unset($appointment_form);

            $this->addFlash('success', 'Votre rendez-vous a été bien crée');
        }

        $notificationsPatient = $ar->findBy(
                                [
                                    'status' => 2,
                                    'patient'     => $this->getUser()
                                ], 
                                [
                                    'created_at' => 'DESC'
                                ]);
        
        return $this->render('doctor/index.html.twig', [
            'doctor' => $doctor,
            'appointment_form' => $appointment_form->createView(),
            'notificationsPatient' => $notificationsPatient
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

    /**
     * @Route("/doctor/read-appointment", name="app_read_appointment")
     */
    public function readAppointment(Request $request, AppointmentRepository $ar, EntityManagerInterface $em)
    {

        $appointment = $ar->find($request->query->get('idAppointment'));
        $appointment->setStatus(1);
        $em->flush();

        return new JsonResponse('okk');
    }

    /**
     * @Route("/doctor/load-notification", name="app-load-notification")
     */
    public function loadNotification(Request $request, AppointmentRepository $ar)
    {
        $datas = [];

        $notificationsDoctor = $ar->findBy(
            [
                'status'       => 0, //Status 0:creer -1 : lu 2 approuvé 
                'practitioner' => $this->getUser()],
            [
                'created_at'   => 'DESC'
            ]);
            
        //dd($notificationsDoctor);

        foreach ( $notificationsDoctor as $notificationDoctor ) {
            $datas = [
                'id'          => $notificationDoctor->getId(),
                'title'       => $notificationDoctor->getDescription(),
                'patientname' => $notificationDoctor->getPatient()->getFullName(),
                'start'       => $notificationDoctor->getStart(),
                'end'         => $notificationDoctor->getEnd(),
                'practitoner' => $notificationDoctor->getPractitioner()->getFullName()
            ];
        }

        return new  JsonResponse([
                                'notificationsDoctor' => count($notificationsDoctor) != 0 ? count($notificationsDoctor) : null,
                                'newNotification' => count($notificationsDoctor) != 0 ? $datas : null
                            ]);

    }

    #[Route('/doctor/list_appointnntment}', name: 'app_list_appointment')]
    /**
     * @Security("is_granted('ROLE_USER')")
     *
     * @return Response
     */
    public function listAppointment(Request $request, AppointmentRepository $ar)
    {
        $notificationsDoctor = $ar->findBy(
            [
                'status'       => 0, //Status 0:creer -1 : lu 2 approuvé 
                'practitioner' => $this->getUser()],
            [
                'created_at'   => 'DESC'
            ]);
        $notificationsDoctorReadAndNews = $ar->findBy(
            [
                'status'       => [0, 1], //Status 0:creer -1 : lu 2 approuvé 
                'practitioner' => $this->getUser()],
            [
                'created_at'   => 'DESC'
        ]);

        return $this->render('doctor/list_appointment.html.twig', 
                                [
                                    'notificationsDoctorReadAndNews' => $notificationsDoctorReadAndNews,
                                    'notificationsDoctor'            => $notificationsDoctor
                                ]
                            );
    }
    
}


