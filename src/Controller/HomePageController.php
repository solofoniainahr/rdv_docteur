<?php

namespace App\Controller;

use App\Data\SearchDoctorData;
use App\Form\SearchFormDoctor;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomePageController extends AbstractController
{
    #[Route('/', name: 'app_home_page')]
    public function index(Request $request): Response
    {
        $data = new SearchDoctorData;
        $form_search = $this->createForm(SearchFormDoctor::class, $data);
        $form_search->handleRequest($request);
        
        
        if ($form_search->isSubmitted() && $form_search->isValid()) { 
            dd($data);
        }
        
        return $this->render('home_page/index.html.twig', [
            'form' => $form_search->createView(),
        ]);
    }
}
