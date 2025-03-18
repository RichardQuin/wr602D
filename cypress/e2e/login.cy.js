describe('Formulaire de Connexion', () => {
    it('test 1 - connexion OK', () => {
        cy.visit('http://symfony.mmi-troyes.fr:8319/login');

        // Entrer le nom d'utilisateur et le mot de passe
        cy.get('#username').type('pokemon@gmail.com');
        cy.get('#password').type('Richard754');

        // Soumettre le formulaire
        cy.get('button[type="submit"]').click();

        // Vérifier que l'utilisateur est connecté
        cy.contains('You are logged in as Richard quin ').should('exist');
    });

    it('test 2 - connexion KO', () => {
        cy.visit('http://symfony.mmi-troyes.fr:8319/login');

        // Entrer un nom d'utilisateur et un mot de passe incorrects
        cy.get('#username').type('pokemon@gmail.com');
        cy.get('#password').type('Richard754');

        // Soumettre le formulaire
        cy.get('button[type="submit"]').click();

        // Vérifier que le message d'erreur est affiché
        cy.contains('Invalid credentials.').should('exist');
    });
});