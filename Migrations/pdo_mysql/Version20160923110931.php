<?php

namespace FormaLibre\BulletinBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2016/09/23 11:09:33
 */
class Version20160923110931 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_bulletin_matiere_options 
            DROP FOREIGN KEY FK_AEF04CC8F46CD258
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_matiere_options CHANGE matiere_id matiere_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_matiere_options 
            ADD CONSTRAINT FK_AEF04CC8F46CD258 FOREIGN KEY (matiere_id) 
            REFERENCES claro_cursusbundle_course_session (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_bulletin_matiere_options 
            DROP FOREIGN KEY FK_AEF04CC8F46CD258
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_matiere_options CHANGE matiere_id matiere_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_matiere_options 
            ADD CONSTRAINT FK_AEF04CC8F46CD258 FOREIGN KEY (matiere_id) 
            REFERENCES claro_cursusbundle_course_session (id)
        ");
    }
}