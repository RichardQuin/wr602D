<?php

namespace App\Controller;

use App\Service\PdfGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PdfController extends AbstractController
{
    #[Route('/generate-pdf', name: 'generate_pdf', methods: ['GET', 'POST'])]
    public function generatePdf(Request $request, PdfGeneratorService $pdfGeneratorService): Response
    {
        // Création du formulaire avec un champ URL et un bouton de soumission
        $form = $this->createFormBuilder()
            ->add('url', UrlType::class, [
                'label' => 'URL à convertir en PDF',
                'required' => true,
                'attr' => ['placeholder' => 'https://exemple.com'],
                'default_protocol' => 'https', // Ajout pour éviter la dépréciation
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Générer PDF',
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->getForm();

        $form->handleRequest($request);

        // Vérification si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération de l'URL soumise
            $url = $form->getData()['url'];

            // Génération du PDF via le service
            $pdf = $pdfGeneratorService->generatePdfFromUrl($url);

            // Vérification si le PDF a bien été généré
            if (!$pdf) {
                $this->addFlash('error', 'La génération du PDF a échoué.');
                return $this->redirectToRoute('generate_pdf');
            }

            // Retourne le PDF sous forme de téléchargement
            return new Response(
                $pdf,
                Response::HTTP_OK,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="document.pdf"',
                ]
            );
        }

        // Affichage du formulaire
        return $this->render('pdf/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
