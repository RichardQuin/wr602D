<?php

namespace App\Service;

use App\Entity\Subscription;
use App\Entity\User;
use App\Entity\UserSubscriptionHistory;
use Doctrine\ORM\EntityManagerInterface;

class SubscriptionManager
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Souscrit un utilisateur à un abonnement
     */
    public function subscribeUser(User $user, Subscription $subscription, int $durationMonths = 1): ?UserSubscriptionHistory
    {
        // Désactiver l'abonnement actif si présent
        $activeHistory = $this->getActiveSubscriptionHistory($user);
        if ($activeHistory) {
            $activeHistory->setIsActive(false);
            $this->entityManager->persist($activeHistory);
            $this->entityManager->flush(); // Évite les conflits de transaction
        }

        // Créer un nouvel enregistrement d'historique
        $history = new UserSubscriptionHistory();
        $history->setUser($user);
        $history->setSubscription($subscription);
        $history->setStartDate(new \DateTime());

        $endDate = new \DateTime();
        $endDate->modify("+{$durationMonths} months");
        $history->setEndDate($endDate);

        $history->setIsActive(true);
        $history->setPdfCount($subscription->getMaxPdf());
        $history->setPdfUsed(0);

        // Mettre à jour la relation User -> Subscription
        $user->setSubscription($subscription);

        try {
            $this->entityManager->persist($history);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $history;
        } catch (\Exception $e) {
            error_log("Erreur lors de la souscription : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Renouvelle un abonnement existant
     */
    public function renewSubscription(User $user, int $durationMonths = 1): ?UserSubscriptionHistory
    {
        $activeHistory = $this->getActiveSubscriptionHistory($user);
        if (!$activeHistory || !$activeHistory->getSubscription()) {
            return null;
        }

        $subscription = $activeHistory->getSubscription();

        // Créer un nouvel enregistrement d'historique
        $history = new UserSubscriptionHistory();
        $history->setUser($user);
        $history->setSubscription($subscription);

        // Utiliser la date de fin actuelle comme nouvelle date de début
        $now = new \DateTime();
        $startDate = $activeHistory->getEndDate() > $now ? $activeHistory->getEndDate() : $now;
        $history->setStartDate($startDate);

        $endDate = clone $startDate;
        $endDate->modify("+{$durationMonths} months");
        $history->setEndDate($endDate);

        $history->setIsActive(true);
        $history->setPdfCount($subscription->getMaxPdf());
        $history->setPdfUsed(0);

        // Désactiver l'ancien abonnement
        $activeHistory->setIsActive(false);

        try {
            $this->entityManager->persist($history);
            $this->entityManager->persist($activeHistory);
            $this->entityManager->flush();

            return $history;
        } catch (\Exception $e) {
            error_log("Erreur lors du renouvellement de l'abonnement : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Annule un abonnement
     */
    public function cancelSubscription(User $user): bool
    {
        $activeHistory = $this->getActiveSubscriptionHistory($user);
        if (!$activeHistory) {
            return false;
        }

        $activeHistory->setIsActive(false);
        $user->setSubscription(null);

        try {
            $this->entityManager->persist($activeHistory);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            error_log("Erreur lors de l'annulation de l'abonnement : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère l'historique d'abonnement actif d'un utilisateur
     */
    public function getActiveSubscriptionHistory(User $user): ?UserSubscriptionHistory
    {
        return $this->entityManager->getRepository(UserSubscriptionHistory::class)
            ->findOneBy([
                'user' => $user,
                'isActive' => true
            ]);
    }
}
