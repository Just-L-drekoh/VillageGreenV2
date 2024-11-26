<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241126094323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_details MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON order_details');
        $this->addSql('ALTER TABLE order_details ADD product_id INT NOT NULL, ADD order_id INT NOT NULL, DROP id');
        $this->addSql('ALTER TABLE order_details ADD CONSTRAINT FK_845CA2C14584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_details ADD CONSTRAINT FK_845CA2C18D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_845CA2C14584665A ON order_details (product_id)');
        $this->addSql('CREATE INDEX IDX_845CA2C18D9F6D38 ON order_details (order_id)');
        $this->addSql('ALTER TABLE order_details ADD PRIMARY KEY (product_id, order_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order_details` DROP FOREIGN KEY FK_845CA2C14584665A');
        $this->addSql('ALTER TABLE `order_details` DROP FOREIGN KEY FK_845CA2C18D9F6D38');
        $this->addSql('DROP INDEX IDX_845CA2C14584665A ON `order_details`');
        $this->addSql('DROP INDEX IDX_845CA2C18D9F6D38 ON `order_details`');
        $this->addSql('ALTER TABLE `order_details` ADD id INT AUTO_INCREMENT NOT NULL, DROP product_id, DROP order_id, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
    }
}
