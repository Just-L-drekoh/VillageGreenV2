<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241126091452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery ADD ord_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC10E636D3F5 FOREIGN KEY (ord_id) REFERENCES `order` (id)');
        $this->addSql('CREATE INDEX IDX_3781EC10E636D3F5 ON delivery (ord_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery DROP FOREIGN KEY FK_3781EC10E636D3F5');
        $this->addSql('DROP INDEX IDX_3781EC10E636D3F5 ON delivery');
        $this->addSql('ALTER TABLE delivery DROP ord_id');
    }
}
