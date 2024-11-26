<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241125152801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supplier_details ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supplier_details ADD CONSTRAINT FK_F3437F95A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_F3437F95A76ED395 ON supplier_details (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE supplier_details DROP FOREIGN KEY FK_F3437F95A76ED395');
        $this->addSql('DROP INDEX IDX_F3437F95A76ED395 ON supplier_details');
        $this->addSql('ALTER TABLE supplier_details DROP user_id');
    }
}
