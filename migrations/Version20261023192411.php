<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261023192411 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE curso (id INT AUTO_INCREMENT NOT NULL, codigo VARCHAR(50) NOT NULL, nombre VARCHAR(150) NOT NULL, descripcion LONGTEXT DEFAULT NULL, creditos INT NOT NULL, fotografia VARCHAR(255) DEFAULT NULL, horario VARCHAR(100) NOT NULL, grupo VARCHAR(20) NOT NULL, cupo_maximo INT NOT NULL, estado VARCHAR(50) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE estudiante (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(150) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE matricula (id INT AUTO_INCREMENT NOT NULL, fecha_matricula DATETIME NOT NULL, estado VARCHAR(50) NOT NULL, estudiante_id INT NOT NULL, curso_id INT NOT NULL, INDEX IDX_15DF188559590C39 (estudiante_id), INDEX IDX_15DF188587CB4A1F (curso_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE matricula ADD CONSTRAINT FK_15DF188559590C39 FOREIGN KEY (estudiante_id) REFERENCES estudiante (id)');
        $this->addSql('ALTER TABLE matricula ADD CONSTRAINT FK_15DF188587CB4A1F FOREIGN KEY (curso_id) REFERENCES curso (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE matricula DROP FOREIGN KEY FK_15DF188559590C39');
        $this->addSql('ALTER TABLE matricula DROP FOREIGN KEY FK_15DF188587CB4A1F');
        $this->addSql('DROP TABLE curso');
        $this->addSql('DROP TABLE estudiante');
        $this->addSql('DROP TABLE matricula');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
