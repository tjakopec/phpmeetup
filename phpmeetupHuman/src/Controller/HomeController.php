<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function home(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/', name: 'app_public_home')]
    public function index(): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/public.html.twig');
    }
}
