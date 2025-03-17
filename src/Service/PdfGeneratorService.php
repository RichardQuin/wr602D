<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;

class PdfGeneratorService
{
    private HttpClientInterface $client;
    private string $gotenbergUrl;

    public function __construct(HttpClientInterface $client, string $gotenbergUrl)
    {
        $this->client = $client;
        $this->gotenbergUrl = rtrim($gotenbergUrl, '/'); // Évite un double "//"
    }

    /**
     * Génère un PDF à partir d'une URL et l'enregistre avec un nom basé sur la date/heure.
     */
    public function generatePdfFromUrl(string $url ,$pdfFileName): ?Response
    {
        try {
            $response = $this->client->request('POST', "{$this->gotenbergUrl}/forms/chromium/convert/url", [
                'headers' => ['Content-Type' => 'multipart/form-data'],
                'body' => ['url' => $url],
            ]);

            if ($response->getStatusCode() !== Response::HTTP_OK) {
                throw new \Exception('Erreur Gotenberg: ' . $response->getStatusCode() . ' - ' . $response->getContent(false));
            }

            // 🕒 Générer un nom unique basé sur la date et l'heure
            $pdfPath = "pdf/{$pdfFileName}"; // 📂 Enregistrement dans /pdf directement

            // 📂 Vérifier si le dossier "pdf" existe, sinon le créer
            if (!is_dir('pdf')) {
                mkdir('pdf', 0775, true);
            }

            // 💾 Sauvegarder le PDF généré dans /pdf
            file_put_contents($pdfPath, $response->getContent(false));

            return new Response(
                $response->getContent(false),
                Response::HTTP_OK,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => "attachment; filename=\"{$pdfFileName}\"",
                    'Content-Length' => strlen($response->getContent(false)), // 🛠️ Assure la bonne transmission
                ]
            );
        } catch (\Exception $e) {
            return new Response('Erreur: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
