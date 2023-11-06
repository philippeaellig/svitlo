<?php

namespace App\Controller;

use App\Entity\Campaign;
use App\Entity\Child;
use App\Entity\Donor;
use App\Repository\CampaignRepository;
use App\Repository\ChildRepository;
use App\Repository\ChildTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;

class CampaignController extends AbstractController
{

    #[Route('/campaign/view/{slug}', name: 'app_show_campaign')]
    public function index(CampaignRepository $campaignRepository, Campaign $campaign): Response
    {
        return $this->render('campaign/index.html.twig', [
            'campaign' => $campaign,
            'controller_name' => 'CampaignController',
        ]);
    }

    #[Route('/campaign/thank-you/{slug}', name: 'app_thank_you_donor')]
    public function thankYou(CampaignRepository $campaignRepository, Campaign $campaign): Response
    {
        return $this->render('campaign/appThankYouDonor.html.twig', [
            'campaign' => $campaign,
            'controller_name' => 'CampaignController',
        ]);
    }

    #[Route('/campaign/become-a-donor/{id}', name: 'app_become_a_donor_campaign')]
    public function becomeADonor(
        CampaignRepository $campaignRepository,
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        Request $request,
        Child $child
    ): Response
    {
        $donor = new Donor();

        $form = $this->createFormBuilder($donor)
            ->add('firstname', TextType::class, [
                'label' => $translator->trans('firstname') . ' *',
                'required' => true,
            ])
            ->add('surname', TextType::class, [
                'label' => $translator->trans('surname') . ' *',
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'label' => $translator->trans('email') . ' *',
                'required' => true,
            ])
            ->add('save', SubmitType::class, ['label' => $translator->trans('become.a.donor')])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $donor->setChild($child);

            $entityManager->persist($donor);
            $entityManager->flush();

            return $this->redirectToRoute('app_thank_you_donor', [
                'slug' => $child->getCampaign()->getSlug()
            ]);
        }

        return $this->render('campaign/becomeAChild.html.twig', [
            'child' => $child,
            'form' => $form,
            'controller_name' => 'CampaignController',
        ]);
    }
/*
    #[Route('/campaign/generate-children/{slug}', name: 'app_show_campaign')]
    public function generateChildren(
        ChildTemplateRepository $childTemplateRepository,
        ChildRepository         $childRepository,
        CampaignRepository      $campaignRepository,
        EntityManagerInterface  $entityManager,
        string                  $slug
    ): Response
    {
        $campaign = $campaignRepository
            ->findOneBy(['slug' => $slug]);

        foreach (Child::$GENDER as $key => $gender) {
            while ($campaign->getNumberOfFemale() > $childRepository->getNumberOfChildrenByGenderAndCampaign($key, $campaign)) {
                $child = new Child();
                $template = $childTemplateRepository->findRandomTemplateByGender($key);
                $child->setFirstName($template->getFirstName());
                $child->setGender($template->getGender());
                $child->setCampaign($campaign);
                $entityManager->persist($child);
                $entityManager->flush();
            }
        }

        return $this->render('campaign/index.html.twig', [
            'campaign' => $campaign,
            'controller_name' => 'CampaignController',
        ]);
    }

    #[Route('/campaign/create', name: 'app_create_campaign')]
    public function create(ValidatorInterface $validator, EntityManagerInterface $entityManager, CampaignRepository $campaignRepository): Response
    {
        $campaign = new Campaign();
        $slugger = new AsciiSlugger();
        $campaign->setSlug($slugger->slug(uniqid())->lower());

        $errors = $validator->validate($campaign);
        if (count($errors) > 0) {
            return new Response((string)$errors, 400);
        }
        $entityManager->persist($campaign);
        $entityManager->flush();
        // redirect to index controiller
        return $this->redirectToRoute('app_show_campaign', [
            'slug' => $campaign->getSlug()
        ]);
    }*/
}