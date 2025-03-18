describe('Gestion des erreurs lors de la connexion', () => {
    it('Affiche un message d\'erreur pour des identifiants incorrects', () => {
        cy.visit('http://symfony.mmi-troyes.fr:8319/login');

        // Entrer un nom d'utilisateur incorrect et un mot de passe incorrect
        cy.get('#username').type('wrong_user@gmail.com');
        cy.get('#password').type('wrong_password');

        // Soumettre le formulaire
        cy.get('button[type="submit"]').click();

        // Vérifier que le message d'erreur "Invalid credentials" est affiché
        cy.contains('Invalid credentials').should('exist');
    });

    it('Affiche un message d\'erreur si le mot de passe est vide', () => {
        cy.visit('http://symfony.mmi-troyes.fr:8319/login');

        // Entrer un nom d'utilisateur et ne pas entrer de mot de passe
        cy.get('#username').type('pokemon@gmail.com');
        cy.get('#password').clear();

        // Soumettre le formulaire
        cy.get('button[type="submit"]').click();

        // Attendre que le message d'erreur apparaisse
        cy.wait(1000); // Attendre 1 seconde pour que l'erreur s'affiche

        // Vérifier que le message d'erreur pour le mot de passe vide est affiché
        cy.get('.error-class').should('contain', 'Veuillez renseigner ce champ.');
    });
});
