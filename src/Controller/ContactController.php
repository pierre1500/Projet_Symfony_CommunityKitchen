<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact.index')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        MailService $mailService
    ): Response
    {
        $contact = new Contact();

        if ($this->getUser()) {
            $contact->setFullName($this->getUser()->getFullName());
            $contact->setEmail($this->getUser()->getEmail());
        }

        $form = $this->createForm(ContactType::class, $contact);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contact = $form->getData();
            $entityManager->persist($contact);
            $entityManager->flush();

            $mailService->sendEmail(
                $contact->getEmail(),
                $contact->getSubject(),

                "emails/contact.html.twig",
                [
                    "contact" => $contact
                ]
            );

            $this->addFlash('success', 'Votre message a bien été envoyé.');

            return $this->redirectToRoute('contact.index');
        }


        return $this->render('pages/contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
