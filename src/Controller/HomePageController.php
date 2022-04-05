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
                        AppointmentRepository $ap
                        ): Response
    {

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

        
        return $this->render('home_page/index.html.twig', [
            'form' => $form_search->createView(),
            'doctors' => $doctors
        ]);
    }
}
