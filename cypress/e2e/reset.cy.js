describe('Réinitialisation du mot de passe', () => {
    it('Vérifie que le lien de réinitialisation redirige correctement', () => {
        cy.visit('http://symfony.mmi-troyes.fr:8319/login');

        // Vérifie que le lien "Mot de passe oublié ?" est présent
        cy.get('a').contains('Mot de passe oublié ?').should('be.visible');

        // Clique sur le lien de réinitialisation
        cy.get('a').contains('Mot de passe oublié ?').click();

        // Attendre que la page de réinitialisation soit chargée
        cy.url().should('include', '/reset-password');

        // Optionnel : Vérifie que l'élément de la page de réinitialisation est visible
        cy.get('h1').contains('Reset your password').should('be.visible'); // Ajustez en fonction de votre texte de titre de page
    });
});
