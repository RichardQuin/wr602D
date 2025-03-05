<?php

// src/Controller/SubscriptionController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{
    #[Route('/subscription/change', name: 'subscription_change')]
    public function changeSubscription(): Response
    {
        // Logique de changement d'abonnement ici (par exemple, afficher un formulaire)
        return $this->render('subscription/index.html.twig');
    }
}
