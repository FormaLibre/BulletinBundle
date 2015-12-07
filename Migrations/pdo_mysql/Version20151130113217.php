<?php

namespace FormaLibre\BulletinBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/11/30 11:32:19
 */
class Version20151130113217 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE formalibre_bulletin_periodes_group (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode 
            ADD matieres VARCHAR(255) DEFAULT NULL, 
            ADD periodesGroup_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode 
            ADD CONSTRAINT FK_A70BD7B8E628E6E5 FOREIGN KEY (periodesGroup_id) 
            REFERENCES formalibre_bulletin_periodes_group (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A70BD7B8E628E6E5 ON formalibre_bulletin_periode (periodesGroup_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode 
            DROP FOREIGN KEY FK_A70BD7B8E628E6E5
        ");
        $this->addSql("
            DROP TABLE formalibre_bulletin_periodes_group
        ");
        $this->addSql("
            DROP INDEX IDX_A70BD7B8E628E6E5 ON formalibre_bulletin_periode
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode 
            DROP matieres, 
            DROP periodesGroup_id
        ");
    }
}