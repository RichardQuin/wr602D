<?php
namespace App\Controller;

use App\Repository\FileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Psr\Log\LoggerInterface;

class HistoryController extends AbstractController
{
private EntityManagerInterface $entityManager;
private FileRepository $fileRepository;
private LoggerInterface $logger;

public function __construct(
EntityManagerInterface $entityManager,
FileRepository $fileRepository,
LoggerInterface $logger
) {
$this->entityManager = $entityManager;
$this->fileRepository = $fileRepository;
$this->logger = $logger;
}

#[Route('/history', name: 'history')]
#[IsGranted('ROLE_USER')]
public function index(): Response
{
$user = $this->getUser();

// Récupérer l'historique des PDF générés par l'utilisateur
$fileHistory = $this->fileRepository->findBy(
['account' => $user],
['created_at' => 'DESC']
);

// Déterminer le nombre de PDF générés par l'utilisateur
$generatedPdfCount = count($fileHistory);

// Récupérer l'abonnement de l'utilisateur via le subscription_id
$subscription = $user->getSubscription();

// Ajouter des logs pour vérifier l'abonnement
$this->logger->info('Abonnement ID: ' . ($subscription ? $subscription->getId() : 'Aucun'));

// Déterminer le nombre maximum de PDF selon l'abonnement
$maxPdf = 10;
if ($subscription) {
switch ($subscription->getId()) {
case 1: // Free
$maxPdf = 10;
break;
case 2: // Pro
$maxPdf = 100;
break;
case 3: // Entreprise
$maxPdf = 1000;
break;
default:
$maxPdf = 10; // Par défaut si aucun abonnement trouvé
}
}

// Calculer le nombre de PDF restants
$remainingPdf = max(0, $maxPdf - $generatedPdfCount);

// Calculer le pourcentage pour la barre de progression
$progressPercentage = $maxPdf > 0 ? min(100, ($generatedPdfCount / $maxPdf) * 100) : 0;

// Retourner la vue avec toutes les informations nécessaires
return $this->render('history/index.html.twig', [
'fileHistory' => $fileHistory,
'maxPdf' => $maxPdf,
'generatedPdfCount' => $generatedPdfCount,
'remainingPdf' => $remainingPdf,
'progressPercentage' => $progressPercentage,
]);
}

#[Route('/file/download/{id}', name: 'file_download')]
public function download(int $id): Response
{
// Récupérer le fichier par ID
$file = $this->fileRepository->find($id);

if (!$file) {
throw $this->createNotFoundException('Le fichier demandé n\'existe pas.');
}

$user = $this->getUser();
if (!$user) {
throw $this->createAccessDeniedException('Utilisateur non connecté.');
}

$fileAccount = $file->getAccount();

// Log des IDs pour débogage
$this->logger->info('User ID: ' . $user->getId());
$this->logger->info('File Account ID: ' . $fileAccount->getId());

// Vérifier que le fichier appartient à l'utilisateur
if ($fileAccount !== $user) {
throw $this->createAccessDeniedException('Vous n\'avez pas l\'autorisation de télécharger ce fichier.');
}

// Chemin du fichier à télécharger
$filePath = $file->getPath();

// Log pour vérifier le chemin du fichier
$this->logger->info('Chemin du fichier: ' . $filePath);

// Vérifier si le fichier existe réellement dans le répertoire attendu
$fileFullPath = $this->getParameter('kernel.project_dir') . '/public/' . $filePath;

if (!file_exists($fileFullPath)) {
throw $this->createNotFoundException('Le fichier n\'existe pas.');
}

// Créer la réponse de téléchargement
$response = new StreamedResponse(function () use ($fileFullPath) {
readfile($fileFullPath);
});

// Ajouter des headers pour le téléchargement
$response->headers->set('Content-Type', 'application/pdf');
$response->headers->set('Content-Disposition', 'attachment; filename="' . basename($fileFullPath) . '"');

return $response;
}
}
