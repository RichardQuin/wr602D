<?php
namespace App\Controller;

use App\Service\PdfGeneratorService;
use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\User;

class PdfController extends AbstractController
{
#[Route('/generate-pdf', name: 'generate_pdf', methods: ['GET', 'POST'])]
public function generatePdf(Request $request, PdfGeneratorService $pdfGeneratorService, EntityManagerInterface $entityManager): Response
{
// Création du formulaire avec un champ URL
$form = $this->createFormBuilder()
->add('url', UrlType::class, [
'label' => 'URL à convertir en PDF',
'required' => true, // Ce champ est maintenant requis
'attr' => ['placeholder' => 'https://exemple.com'],
'default_protocol' => 'https',
])
->add('submit', SubmitType::class, [
'label' => 'Générer PDF',
'attr' => ['class' => 'btn btn-primary'],
])
->getForm();

$form->handleRequest($request);

// Vérification si le formulaire est soumis et valide
if ($form->isSubmitted() && $form->isValid()) {
$data = $form->getData();
$url = $data['url'];

// Récupération de l'utilisateur connecté
$user = $this->getUser(); // L'utilisateur déjà connecté

if (!$user) {
throw $this->createNotFoundException('Utilisateur non trouvé.');
}

// Vérifie si l'utilisateur a déjà un email unique
$existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);

if (!$existingUser) {
throw $this->createNotFoundException('Utilisateur avec cet email n\'existe pas.');
}

// Récupérer l'historique des PDFs générés par l'utilisateur
$fileHistory = $entityManager->getRepository(File::class)->findBy(['account' => $existingUser]);

// Déterminer le nombre de PDF générés par l'utilisateur
$generatedPdfCount = count($fileHistory);

// Récupérer l'abonnement de l'utilisateur via le subscription_id
$subscription = $existingUser->getSubscription();

// Déterminer le nombre maximum de PDF selon l'abonnement
$maxPdf = 10; // Par défaut (Free)
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

// Vérifier si l'utilisateur a dépassé sa limite de PDF
if ($generatedPdfCount >= $maxPdf) {
// Bloquer la génération du PDF si la limite est atteinte
$this->addFlash('error', 'Vous avez atteint la limite de génération de PDF pour votre abonnement.');
return $this->redirectToRoute('generate_pdf');
}

// Génération du nom de fichier basé sur la date et l'heure
$timestamp = (new \DateTime())->format('Y-m-d_H-i-s');
$pdfFileName = "document_{$timestamp}.pdf";

// Création de l'entité File
$pdfFile = new File();
$pdfFile
->setName($pdfFileName)
->setCreatedAt(new \DateTimeImmutable('now'))
->setAccount($existingUser); // Utilise l'utilisateur existant, pas besoin de persister l'utilisateur

// Générer le chemin du fichier (ex : 'uploads/pdfs/document_2025-03-17_12-30-45.pdf')
$pdfFilePath = 'uploads/pdfs/' . $pdfFileName;
$pdfFile->setPath($pdfFilePath); // Assurez-vous que le champ 'path' est défini dans l'entité File

// Persister le fichier uniquement (pas l'utilisateur)
$entityManager->persist($pdfFile);
$entityManager->flush(); // Sauvegarde du fichier dans la base de données

// Vérification de la source d'entrée (URL)
if ($url) {
// Générer le PDF à partir de l'URL
$response = $pdfGeneratorService->generatePdfFromUrl($url, $pdfFilePath);
} else {
// Si aucun contenu n'est fourni
$this->addFlash('error', 'Veuillez fournir une URL.');
return $this->redirectToRoute('generate_pdf');
}

// Retourner directement la réponse générée par le service
return $response;
}

// Affichage du formulaire
return $this->render('pdf/index.html.twig', [
'form' => $form->createView(),
]);
}
}
