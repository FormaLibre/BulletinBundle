<?php

namespace FormaLibre\BulletinBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/12/08 11:04:55
 */
class Version20151208110453 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_bulletin_lock_status 
            DROP FOREIGN KEY FK_3044364AF384C1CF
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_lock_status CHANGE periode_id periode_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_lock_status 
            ADD CONSTRAINT FK_3044364AF384C1CF FOREIGN KEY (periode_id) 
            REFERENCES formalibre_bulletin_periode (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_matiere_remarque 
            DROP FOREIGN KEY FK_5D6FB6A0F384C1CF
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_matiere_remarque CHANGE periode_id periode_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_matiere_remarque 
            ADD CONSTRAINT FK_5D6FB6A0F384C1CF FOREIGN KEY (periode_id) 
            REFERENCES formalibre_bulletin_periode (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_matiere_point 
            DROP FOREIGN KEY FK_FCB4A752F384C1CF
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_matiere_point CHANGE periode_id periode_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_matiere_point 
            ADD CONSTRAINT FK_FCB4A752F384C1CF FOREIGN KEY (periode_id) 
            REFERENCES formalibre_bulletin_periode (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_pointdivers_point 
            DROP FOREIGN KEY FK_134BF6F8F384C1CF
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_pointdivers_point CHANGE periode_id periode_id INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_pointdivers_point 
            ADD CONSTRAINT FK_134BF6F8F384C1CF FOREIGN KEY (periode_id) 
            REFERENCES formalibre_bulletin_periode (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_bulletin_lock_status 
            DROP FOREIGN KEY FK_3044364AF384C1CF
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_lock_status CHANGE periode_id periode_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_lock_status 
            ADD CONSTRAINT FK_3044364AF384C1CF FOREIGN KEY (periode_id) 
            REFERENCES formalibre_bulletin_periode (id)
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_matiere_point 
            DROP FOREIGN KEY FK_FCB4A752F384C1CF
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_matiere_point CHANGE periode_id periode_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_matiere_point 
            ADD CONSTRAINT FK_FCB4A752F384C1CF FOREIGN KEY (periode_id) 
            REFERENCES formalibre_bulletin_periode (id)
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_matiere_remarque 
            DROP FOREIGN KEY FK_5D6FB6A0F384C1CF
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_matiere_remarque CHANGE periode_id periode_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_matiere_remarque 
            ADD CONSTRAINT FK_5D6FB6A0F384C1CF FOREIGN KEY (periode_id) 
            REFERENCES formalibre_bulletin_periode (id)
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_pointdivers_point 
            DROP FOREIGN KEY FK_134BF6F8F384C1CF
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_pointdivers_point CHANGE periode_id periode_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_pointdivers_point 
            ADD CONSTRAINT FK_134BF6F8F384C1CF FOREIGN KEY (periode_id) 
            REFERENCES formalibre_bulletin_periode (id)
        ");
    }
}