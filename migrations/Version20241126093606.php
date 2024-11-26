<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241126093606 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery_details MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON delivery_details');
        $this->addSql('ALTER TABLE delivery_details ADD product_id INT NOT NULL, ADD delivery_id INT NOT NULL, DROP id');
        $this->addSql('ALTER TABLE delivery_details ADD CONSTRAINT FK_7838B4544584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE delivery_details ADD CONSTRAINT FK_7838B45412136921 FOREIGN KEY (delivery_id) REFERENCES delivery (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_7838B4544584665A ON delivery_details (product_id)');
        $this->addSql('CREATE INDEX IDX_7838B45412136921 ON delivery_details (delivery_id)');
        $this->addSql('ALTER TABLE delivery_details ADD PRIMARY KEY (product_id, delivery_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery_details DROP FOREIGN KEY FK_7838B4544584665A');
        $this->addSql('ALTER TABLE delivery_details DROP FOREIGN KEY FK_7838B45412136921');
        $this->addSql('DROP INDEX IDX_7838B4544584665A ON delivery_details');
        $this->addSql('DROP INDEX IDX_7838B45412136921 ON delivery_details');
        $this->addSql('ALTER TABLE delivery_details ADD id INT AUTO_INCREMENT NOT NULL, DROP product_id, DROP delivery_id, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
    }
}
