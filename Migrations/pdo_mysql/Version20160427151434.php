<?php

namespace FormaLibre\BulletinBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2016/04/27 03:14:36
 */
class Version20160427151434 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE formalibre_bulletin_eleve_matiere_options (
                id INT AUTO_INCREMENT NOT NULL, 
                eleve_id INT NOT NULL, 
                matiere_id INT NOT NULL, 
                deliberated TINYINT(1) NOT NULL, 
                options LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', 
                INDEX IDX_844DDFA3A6CC7B2 (eleve_id), 
                INDEX IDX_844DDFA3F46CD258 (matiere_id), 
                UNIQUE INDEX bulletin_unique_eleve_matiere_option (matiere_id, eleve_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_eleve_matiere_options 
            ADD CONSTRAINT FK_844DDFA3A6CC7B2 FOREIGN KEY (eleve_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_eleve_matiere_options 
            ADD CONSTRAINT FK_844DDFA3F46CD258 FOREIGN KEY (matiere_id) 
            REFERENCES claro_cursusbundle_course_session (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE formalibre_bulletin_eleve_matiere_options
        ");
    }
}