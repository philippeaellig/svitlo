<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CampaignRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Security\Http\LoginLink\LoginLinkNotification;

class SecurityController extends AbstractController
{
    #[Route('/login_check', name: 'login_check')]
    public function loginCheck(
        Request $request
    ): Response
    {
// get the login link query parameters
        $expires = $request->query->get('expires');
        $username = $request->query->get('user');
        $hash = $request->query->get('hash');

        // and render a template with the button
        return $this->render('security/process_login_link.html.twig', [
            'expires' => $expires,
            'user' => $username,
            'hash' => $hash,
        ]);
    }

    #[Route('/login', name: 'login')]
    public function requestLoginLink(
        LoginLinkHandlerInterface $loginLinkHandler,
        NotifierInterface         $notifier,
        CampaignRepository        $campaignRepository,
        Request                   $request,
    ): Response
    {
        // check if login form is submitted
        if ($request->isMethod('POST')) {
            // load the user in some way (e.g. using the form input)
            $campaignId = $request->request->get('campaign');
            $campaign = $campaignRepository->find($campaignId);

            // create a login link for $user this returns an instance
            // of LoginLinkDetails
            $loginLinkDetails = $loginLinkHandler->createLoginLink($campaign);
            $loginLink = $loginLinkDetails->getUrl();


            $notification = new LoginLinkNotification(
                $loginLinkDetails,
                'Welcome to MY WEBSITE!' // email subject
            );
            // create a recipient for this user
            $recipient = new Recipient($campaign->getMail());

            // send the notification to the user
            $notifier->send($notification, $recipient);

            // render a "Login link is sent!" page
            return $this->render('security/login_link_sent.html.twig');
            // ... send the link and return a response (see next section)
        }


        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'campaigns' => $campaignRepository->findAll()
        ]);
    }

}