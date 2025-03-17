<?php
// src/Controller/FileController.php

namespace App\Controller;

use App\Entity\File;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileController extends AbstractController
{
#[Route('/file/download/{id}', name: 'file_download')]
public function download(File $file): Response
{
// Vérifie si l'utilisateur a accès au fichier
if ($file->getAccount() !== $this->getUser()) {
throw new NotFoundHttpException('Fichier non trouvé.');
}

// Retourner le fichier en réponse
$filePath = $file->getPath(); // Assurez-vous que le chemin du fichier est correctement stocké
return new StreamedResponse(function() use ($filePath) {
readfile($filePath);
}, 200, [
'Content-Type' => 'application/pdf',
'Content-Disposition' => 'attachment; filename="' . basename($filePath) . '"'
]);
}

#[Route('/file/view/{id}', name: 'file_view')]
public function view(File $file): Response
{
// Vérifie si l'utilisateur a accès au fichier
if ($file->getAccount() !== $this->getUser()) {
throw new NotFoundHttpException('Fichier non trouvé.');
}

// Retourner le fichier pour l'afficher dans le navigateur
$filePath = $file->getPath();
return new Response(file_get_contents($filePath), 200, [
'Content-Type' => 'application/pdf'
]);
}
}
