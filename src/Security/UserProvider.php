<?php

namespace App\Security;

use App\Repository\UserRepository;  // Assurez-vous que vous avez correctement importé le repository
use App\Entity\User;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class UserProvider implements UserProviderInterface
{
    private UserRepository $userRepository;

    // Injectez UserRepository via le constructeur
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Charge un utilisateur par son identifiant (généralement l'email).
     */
    public function loadUserByIdentifier($identifier): UserInterface
    {
        // Recherche de l'utilisateur dans la base de données par email
        $user = $this->userRepository->findOneBy(['email' => $identifier]);

        // Si l'utilisateur n'est pas trouvé, on lance une exception
        if (!$user) {
            throw new UserNotFoundException('Utilisateur non trouvé.');
        }

        return $user;
    }

    /**
     * Méthode dépréciée dans Symfony 5.3 et plus.
     * Elle est redirigée vers loadUserByIdentifier().
     */
    public function loadUserByUsername($username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    /**
     * Cette méthode permet de rafraîchir les données de l'utilisateur.
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        // Vérifiez que l'utilisateur est bien une instance de User
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', $user::class));
        }

        // Retourner l'utilisateur après avoir rafraîchi ses données (si nécessaire)
        return $user;
    }

    /**
     * Indique si ce provider prend en charge la classe spécifiée (User dans ce cas).
     */
    public function supportsClass(string $class): bool
    {
        // Vérifiez que la classe fournie est bien la classe User
        return User::class === $class || is_subclass_of($class, User::class);
    }

    /**
     * Met à jour le mot de passe d'un utilisateur, par exemple avec un nouvel algorithme de hachage.
     */
    public function upgradePassword(UserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException('User must be an instance of App\Entity\User');
        }

        // Mettez à jour le mot de passe de l'utilisateur
        $user->setPassword($newHashedPassword);
    }
}
