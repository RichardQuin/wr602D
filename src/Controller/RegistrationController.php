<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Subscription;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class RegistrationController extends AbstractController
{
#[Route('/register', name: 'app_register')]
public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, AuthenticationUtils $authenticationUtils, EntityManagerInterface $entityManager): Response
{
// Créer une nouvelle instance de l'utilisateur
$user = new User();

// Créer le formulaire d'inscription
$form = $this->createForm(RegistrationFormType::class, $user);
$form->handleRequest($request);

// Si le formulaire est soumis et valide
if ($form->isSubmitted() && $form->isValid()) {
// Récupérer le mot de passe en texte clair
/** @var string $plainPassword */
$plainPassword = $form->get('plainPassword')->getData();

// Encoder le mot de passe en texte clair
$user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

// Récupérer l'ID de l'abonnement sélectionné depuis le formulaire
$subscriptionId = $form->get('subscription')->getData();


// Charger l'abonnement en utilisant l'ID
$subscription = $entityManager->getRepository(Subscription::class)->find($subscriptionId);

// Vérifier si l'abonnement existe
if ($subscription) {
// Associer l'abonnement à l'utilisateur
$user->setSubscription($subscription);

// Persister l'utilisateur dans la base de données
$entityManager->persist($user);
$entityManager->flush();

    if ($form->isSubmitted() && $form->isValid()) {
        // Vérifier les données du formulaire avant de persister
        dump($form->getData());

        // Encoder le mot de passe et persister l'utilisateur
        $plainPassword = $form->get('plainPassword')->getData();
        $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

        // Persister et flush
        $entityManager->persist($user);
        $entityManager->flush();

        // Redirection après inscription
        return $this->redirectToRoute('/');
    }


// Authentifier l'utilisateur après l'inscription
return $this->redirectToRoute('app_home'); // Modifier selon votre page d'accueil
} else {
// Gérer l'erreur si l'abonnement n'est pas trouvé
$this->addFlash('error', 'Abonnement introuvable.');
return $this->redirectToRoute('app_register');
}
}

// Afficher le formulaire d'inscription
return $this->render('registration/register.html.twig', [
'registrationForm' => $form->createView(),
]);
}
}
