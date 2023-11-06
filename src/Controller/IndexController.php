<?php

namespace App\Controller;

use App\Repository\CampaignRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(CampaignRepository $campaignRepository): Response
    {
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'campaigns' => $campaignRepository->findAll()
        ]);
    }
}
