<?php
namespace App\Controller;

use App\Repository\FileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\RequestStack;
use Psr\Log\LoggerInterface;

class HistoryController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private FileRepository $fileRepository;
    private LoggerInterface $logger;
    private RequestStack $requestStack;

    public function __construct(
        EntityManagerInterface $entityManager,
        FileRepository $fileRepository,
        LoggerInterface $logger,
        RequestStack $requestStack
    ) {
        $this->entityManager = $entityManager;
        $this->fileRepository = $fileRepository;
        $this->logger = $logger;
        $this->requestStack = $requestStack;
    }

    #[Route('/history', name: 'history')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        $user = $this->getUser();
        $previousSubscription = $user->getSubscription();

        // Accéder à la session via le RequestStack
        $session = $this->requestStack->getSession();

        // Vérifier si l'abonnement a changé
        $subscription = $user->getSubscription();
        $subscriptionId = $subscription ? $subscription->getId() : null;

        // Mettre à jour l'abonnement dans la session si nécessaire
        if ($session->get('subscription_id') !== $subscriptionId) {
            $session->set('subscription_id', $subscriptionId);
            // Rafraîchir les données de l'utilisateur ici si nécessaire
        }

        // Récupérer l'historique des fichiers de l'utilisateur
        $fileHistory = $this->fileRepository->findBy(
            ['account' => $user],
            ['created_at' => 'DESC']
        );

        $generatedPdfCount = count($fileHistory);
        $maxPdf = match ($subscriptionId) {
            2 => 100,  // Pro
            3 => 1000, // Entreprise
            default => 10, // Free ou inconnu
        };

        $remainingPdf = max(0, $maxPdf - $generatedPdfCount);
        $progressPercentage = $maxPdf > 0 ? min(100, ($generatedPdfCount / $maxPdf) * 100) : 0;

        return $this->render('history/index.html.twig', [
            'fileHistory' => $fileHistory,
            'maxPdf' => $maxPdf,
            'generatedPdfCount' => $generatedPdfCount,
            'remainingPdf' => $remainingPdf,
            'progressPercentage' => $progressPercentage,
            'subscriptionId' => $subscriptionId,
        ]);
    }

    #[Route('/file/download/{id}', name: 'file_download')]
    public function download(int $id): Response
    {
        $file = $this->fileRepository->find($id);

        if (!$file) {
            return new Response("Fichier non trouvé.", Response::HTTP_NOT_FOUND);
        }

        // Correction du chemin pour éviter d'ajouter 'uploads/pdfs/' deux fois
        $filePath = $file->getPath();

        // Si le chemin ne commence pas déjà par 'pdf/uploads/pdfs/', on l'ajoute
        if (strpos($filePath, '/pdf') !== 0) {
            $fileFullPath = $this->getParameter('kernel.project_dir') . '/public/pdf/' . $filePath;
        } else {
            $fileFullPath = $this->getParameter('kernel.project_dir') . '/public/' . $filePath;
        }

        if (!file_exists($fileFullPath)) {
            return new Response("Le fichier physique n'existe pas : $fileFullPath", Response::HTTP_NOT_FOUND);
        }

        // Forcer le téléchargement avec 'attachment'
        return $this->file($fileFullPath, basename($fileFullPath));
    }
}
