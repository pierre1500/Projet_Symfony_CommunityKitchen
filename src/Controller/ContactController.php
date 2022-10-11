<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact.index')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
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

            //send email
            $email = (new TemplatedEmail())
                ->from($contact->getEmail())
                ->to('pierre.1500@gmail.com')
                ->subject($contact->getSubject())
                ->htmlTemplate('emails/contact.html.twig')
                ->context([
                    'contact' => $contact
                ]);
            $mailer->send($email);


            $this->addFlash('success', 'Votre message a bien été envoyé.');

            return $this->redirectToRoute('contact.index');
        }


        return $this->render('pages/contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
