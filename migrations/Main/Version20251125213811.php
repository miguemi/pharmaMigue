<?php

declare(strict_types=1);

namespace DoctrineMigrations\Main;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251125213811 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tenant (id INT AUTO_INCREMENT NOT NULL, tenant_name VARCHAR(255) NOT NULL, tenant_code VARCHAR(127) NOT NULL, UNIQUE INDEX UNIQ_4E59C4623D7A6A4B (tenant_code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tenant_tenant_db_config (tenant_id INT NOT NULL, tenant_db_config_id INT NOT NULL, INDEX IDX_3C0601449033212A (tenant_id), INDEX IDX_3C06014436224B68 (tenant_db_config_id), PRIMARY KEY(tenant_id, tenant_db_config_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tenant_db_config (id INT AUTO_INCREMENT NOT NULL, db_name VARCHAR(255) NOT NULL, driver_type VARCHAR(255) DEFAULT \'mysql\' NOT NULL, db_user_name VARCHAR(255) DEFAULT NULL, db_password VARCHAR(255) DEFAULT NULL, db_host VARCHAR(255) DEFAULT NULL, db_port INT DEFAULT NULL, database_status VARCHAR(255) DEFAULT \'DATABASE_NOT_CREATED\' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tenant_tenant_db_config ADD CONSTRAINT FK_3C0601449033212A FOREIGN KEY (tenant_id) REFERENCES tenant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tenant_tenant_db_config ADD CONSTRAINT FK_3C06014436224B68 FOREIGN KEY (tenant_db_config_id) REFERENCES tenant_db_config (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tenant_tenant_db_config DROP FOREIGN KEY FK_3C0601449033212A');
        $this->addSql('ALTER TABLE tenant_tenant_db_config DROP FOREIGN KEY FK_3C06014436224B68');
        $this->addSql('DROP TABLE tenant');
        $this->addSql('DROP TABLE tenant_tenant_db_config');
        $this->addSql('DROP TABLE tenant_db_config');
    }
}
