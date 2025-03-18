describe('Test de la réactivité du formulaire de connexion', () => {
    it('Vérifie que le formulaire s\'affiche correctement sur des écrans plus petits', () => {
        cy.visit('http://symfony.mmi-troyes.fr:8319/login');

        // Redimensionner la fenêtre pour simuler un mobile
        cy.viewport('iphone-6');

        // Attendre que les éléments soient présents avant de vérifier leur visibilité
        cy.get('#username').should('exist').and('be.visible');
        cy.get('#password').should('exist').and('be.visible');
        cy.get('button[type="submit"]').should('exist').and('be.visible');
    });
});
