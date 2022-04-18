<?php

namespace App\Controller;

use App\Data\SearchDoctorData;
use App\Form\SearchFormDoctor;
use App\Repository\AppointmentRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomePageController extends AbstractController
{
    #[Route('/', name: 'app_home_page')]
    public function index(Request $request, UserRepository $ur, 
                        PaginatorInterface $paginator,
                        AppointmentRepository $ap,
                        AppointmentRepository $ar
                        ): Response
    {
        $notificationsDoctor = [];
        $notificationsPatient = [];
        $data = new SearchDoctorData;
        $form_search = $this->createForm(SearchFormDoctor::class, $data);
        $form_search->handleRequest($request);
        $practiciens = $ur->findBy(['is_doctor' => 1]);
        
        if ($form_search->isSubmitted() && $form_search->isValid()) { 
            $practiciens = $ur->findDoctor($data);
        }
 
        $doctors = $paginator->paginate(
            $practiciens, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            2 // Nombre de résultats par page
        );

        $notificationsDoctor = $ar->findBy(
                                    [
                                        'status'       => 0, //Status 0:creer -1 : lu 2 approuvé 
                                        'practitioner' => $this->getUser()],
                                    [
                                        'created_at'   => 'DESC'
                                    ]);
        $notificationsPatient = $ar->findBy(
                                    [
                                        'status' => 2,
                                        'patient'     => $this->getUser()
                                    ], 
                                    [
                                        'created_at' => 'DESC'
                                    ]);
        $notificationsDoctorReadAndNews = $ar->findBy(
                                                [
                                                    'status'       => [0, 1], //Status 0:creer -1 : lu 2 approuvé 
                                                    'practitioner' => $this->getUser()],
                                                [
                                                    'created_at'   => 'DESC'
                                            ]);
        return $this->render('home_page/index.html.twig', [
            'form' => $form_search->createView(),
            'doctors' => $doctors,
            'notificationsDoctor' => $notificationsDoctor,
            'notificationsPatient' => $notificationsPatient,
            'notificationsDoctorReadAndNews' => $notificationsDoctorReadAndNews
        ]);
    }
}
