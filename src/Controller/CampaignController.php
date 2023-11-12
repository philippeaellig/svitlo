<?php

namespace App\Controller;

use App\Entity\Campaign;
use App\Entity\Child;
use App\Entity\Donor;
use App\Repository\CampaignRepository;
use App\Repository\ChildRepository;
use App\Repository\ChildTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\WrappedTemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Twig\Environment;
use Symfony\Bundle\SecurityBundle\Security;

class CampaignController extends AbstractController
{

    // constructor
    private LoggerInterface $logger;
    private KernelInterface $appKernel;
    private Environment $twig;
    private TranslatorInterface $translator;
    private MailerInterface $mailer;
    private CampaignRepository $campaignRepository;

    public function __construct(
        LoggerInterface     $logger,
        KernelInterface     $appKernel,
        Environment         $twig,
        TranslatorInterface $translator,
        MailerInterface     $mailer,
        CampaignRepository  $campaignRepository,
    )
    {
        $this->logger = $logger;
        $this->appKernel = $appKernel;
        $this->twig = $twig;
        $this->translator = $translator;
        $this->mailer = $mailer;
        $this->campaignRepository = $campaignRepository;
    }

    #[Route('/campaign/view/{slug}', name: 'app_show_campaign')]
    public function index(
        CampaignRepository $campaignRepository,
        Campaign           $campaign,
    ): Response
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


    #[Route('/campaign/resend-confirmation-email/{id}', name: 'app_resend_confirmation_email')]
    public function resendConfirmationEmail(
        CampaignRepository $campaignRepository,
        Donor              $donor,
        Security           $security,
    ): Response
    {
        // check if user is campaign owner
        if ($donor->getChild()->getCampaign() === $security->getUser()) {
            $this->sendConfirmationEmail($donor);
        }
        return $this->redirectToRoute('app_show_campaign', [
            'slug' => $donor->getChild()->getCampaign()->getSlug()
        ]);
    }

    #[Route('/campaign/become-a-donor/{id}', name: 'app_become_a_donor_campaign')]
    public function becomeADonor(
        CampaignRepository     $campaignRepository,
        TranslatorInterface    $translator,
        EntityManagerInterface $entityManager,
        Request                $request,
        Child                  $child,
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
            ->add('phone', TextType::class, [
                'label' => $translator->trans('phone') . ' *',
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

            $this->sendConfirmationEmail($donor);

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
    private function sendConfirmationEmail(Donor $donor): void
    {

        $signer = new DkimSigner('file:///' . $this->appKernel->getProjectDir() . '/dkim.key', 'svitlo.ch', 's1');
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@svitlo.ch', 'svitlo.ch'))
            ->replyTo(new Address('no-reply@svitlo.ch', 'svitlo.ch'))
            ->to(new Address($donor->getEmail()))
            ->cc(new Address($donor->getChild()->getCampaign()->getMail()))
            ->subject($this->translator->trans('thank.you.for.your.donation'))
            ->htmlTemplate('emails/confirmation.html.twig')
            ->context([
                'donor' => $donor,
            ]);

        $html = $this->render('emails/confirmation.html.twig', [
            'donor' => $donor,
        ])->getContent();
        $email->html($html);

        $signedEmail = $signer->sign($email);
        try {
            $this->mailer->send($signedEmail);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('An error occurred' . $e->getMessage());
        }
    }
}
