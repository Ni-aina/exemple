<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\CommentType;
use App\Entity\Comments;
use App\Entity\User;
use DateTime;

class Controller extends AbstractController
{
    #[Route('/', name: 'app_')]
    public function index(EntityManagerInterface $entityManager, Request $request,
    UserInterface $user): Response
    {
        $comments = $entityManager->getRepository(Comments:: class)->findAll();
        $comment = new Comments();
        $form = $this->CreateForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $comment = $form->getData();
            $comment->setCreatedAt(new DateTime());
            $comment->setUser($user);
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('app_');
        }
        return $this->render('/index.html.twig', [
            'form' => $form,
            'comments' => $comments
        ]);
    }
}
