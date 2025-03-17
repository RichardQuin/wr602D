<?php
namespace App\Controller;

use App\Entity\Subscription;
use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/subscription')]
class SubscriptionController extends AbstractController
{
    private $entityManager;
    private $subscriptionRepository;
    private $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SubscriptionRepository $subscriptionRepository,
        UserRepository $userRepository
    ) {
        $this->entityManager = $entityManager;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->userRepository = $userRepository;
    }

    // Affichage de la page de changement d'abonnement
    #[Route('/change', name: 'subscription_change')]
    #[IsGranted('ROLE_USER')]
    public function changeSubscription(): Response
    {
        // Récupérer l'utilisateur à jour depuis la base de données
        $user = $this->userRepository->find($this->getUser()->getId());
        $subscriptions = $this->subscriptionRepository->findAll();
        $activeSubscription = $user->getSubscription();

        return $this->render('subscription/change.html.twig', [
            'subscriptions' => $subscriptions,
            'activeSubscription' => $activeSubscription,
        ]);
    }

    // Mise à jour de l'abonnement de l'utilisateur
    #[Route('/update/{id}', name: 'subscription_update')]
    #[IsGranted('ROLE_USER')]
    public function updateSubscription(Subscription $subscription): Response
    {
        $userId = $this->getUser()->getId();

        // Récupérer l'utilisateur à jour depuis la base de données
        $user = $this->userRepository->find($userId);

        // Vérification si l'abonnement est déjà actif
        if ($user->getSubscription() && $user->getSubscription()->getId() === $subscription->getId()) {
            $this->addFlash('warning', 'Vous êtes déjà abonné à ce plan.');
            return $this->redirectToRoute('subscription_change');
        }

        try {
            // Mettre à jour l'abonnement avec une requête SQL directe
            $connection = $this->entityManager->getConnection();
            $sql = "UPDATE user SET subscription_id = :subscription_id WHERE id = :user_id";

            // Exécution de la requête UPDATE
            $connection->executeStatement($sql, [
                'subscription_id' => $subscription->getId(),
                'user_id' => $userId,
            ]);

            // Vider le cache de l'entity manager pour s'assurer que les données sont à jour
            $this->entityManager->clear();

            // Affichage du message de succès
            $this->addFlash('success', 'Votre abonnement a été mis à jour avec succès.');
        } catch (\Exception $e) {
            // En cas d'erreur
            $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour de votre abonnement.');
        }

        // Redirection vers la page des abonnements
        return $this->redirectToRoute('subscription_change');
    }
}