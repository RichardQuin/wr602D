<?php

// src/Controller/SecurityController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Obtenir l'erreur d'authentification, s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // Obtenir le dernier email utilisé dans le formulaire de connexion
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    public function logout(): void
    {
        // Symfony gère la déconnexion automatiquement avec la route définie dans le YAML
    }
}
