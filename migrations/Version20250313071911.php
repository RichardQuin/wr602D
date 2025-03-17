<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250313071911 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_subscription_history (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, subscription_id INT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, is_active TINYINT(1) NOT NULL, payment_reference VARCHAR(255) DEFAULT NULL, pdf_count INT NOT NULL, pdf_used INT NOT NULL, INDEX IDX_EA8E43AFA76ED395 (user_id), INDEX IDX_EA8E43AF9A1887DC (subscription_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_subscription_history ADD CONSTRAINT FK_EA8E43AFA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user_subscription_history ADD CONSTRAINT FK_EA8E43AF9A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_subscription_history DROP FOREIGN KEY FK_EA8E43AFA76ED395');
        $this->addSql('ALTER TABLE user_subscription_history DROP FOREIGN KEY FK_EA8E43AF9A1887DC');
        $this->addSql('DROP TABLE user_subscription_history');
    }
}
