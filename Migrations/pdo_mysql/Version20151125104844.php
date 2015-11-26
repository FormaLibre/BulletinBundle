<?php

namespace FormaLibre\BulletinBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/11/25 10:48:45
 */
class Version20151125104844 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE formalibre_bulletin_lock_status (
                id INT AUTO_INCREMENT NOT NULL, 
                teacher_id INT DEFAULT NULL, 
                periode_id INT DEFAULT NULL, 
                matiere_id INT DEFAULT NULL, 
                `lock` TINYINT(1) NOT NULL, 
                INDEX IDX_3044364A41807E1D (teacher_id), 
                INDEX IDX_3044364AF384C1CF (periode_id), 
                INDEX IDX_3044364AF46CD258 (matiere_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_lock_status 
            ADD CONSTRAINT FK_3044364A41807E1D FOREIGN KEY (teacher_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_lock_status 
            ADD CONSTRAINT FK_3044364AF384C1CF FOREIGN KEY (periode_id) 
            REFERENCES formalibre_bulletin_periode (id)
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_lock_status 
            ADD CONSTRAINT FK_3044364AF46CD258 FOREIGN KEY (matiere_id) 
            REFERENCES claro_cursusbundle_course_session (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE formalibre_bulletin_lock_status
        ");
    }
}