<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
     * Génère un PDF à partir d'une URL
     */
    public function generatePdfFromUrl(string $url): ?Response
    {
        try {
            $response = $this->client->request('POST', "{$this->gotenbergUrl}/forms/chromium/convert/url", [
                'headers' => ['Content-Type' => 'multipart/form-data'],
                'body' => ['url' => $url],
            ]);

            if ($response->getStatusCode() !== Response::HTTP_OK) {
                throw new \Exception('Erreur lors de la génération du PDF');
            }

            return new StreamedResponse(function () use ($response) {
                echo $response->getContent();
            }, Response::HTTP_OK, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="document.pdf"',
            ]);

        } catch (\Exception $e) {
            return new Response('Erreur: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Génère un PDF à partir d'un contenu HTML
     */
    public function generatePdfFromHtml(string $htmlContent): ?Response
    {
        try {
            $response = $this->client->request('POST', "{$this->gotenbergUrl}/forms/chromium/convert/html", [
                'headers' => ['Content-Type' => 'multipart/form-data'],
                'body' => [
                    'files' => [
                        'document.html' => $htmlContent,
                    ],
                ],
            ]);

            if ($response->getStatusCode() !== Response::HTTP_OK) {
                throw new \Exception('Erreur lors de la génération du PDF');
            }

            return new StreamedResponse(function () use ($response) {
                echo $response->getContent();
            }, Response::HTTP_OK, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="document.pdf"',
            ]);

        } catch (\Exception $e) {
            return new Response('Erreur: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
