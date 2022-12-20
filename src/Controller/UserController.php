<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/register')]
    public function register (Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('notice', 'Account created.');
            return $this->redirectToRoute("app_default_home");
        }

        return $this->render('user/register.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/user/delete/{user}')]
    public function delete (User $user, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $this->addFlash('notice', 'User n°' . $user->getId() . ' successfully deleted.');
        $em->remove($user);
        $em->flush();
        return $this->redirectToRoute('app_user_list');
    }

    #[Route('/user/{user}')]
    public function read (User $user): Response
    {
        return $this->render('user/read.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/user/edit/{user}')]
    public function edit (User $user, Request $request, ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $entityManager = $doctrine->getManager();
            $entityManager->flush();
            $this->addFlash('notice', 'User successfully updated.');
            return $this->redirectToRoute("app_user_list");
        }

        return $this->render('user/create.html.twig', [
            'form' => $form
        ]);
    }
}