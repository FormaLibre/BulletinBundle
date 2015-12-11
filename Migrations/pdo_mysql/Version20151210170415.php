<?php

namespace FormaLibre\BulletinBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/12/10 05:04:17
 */
class Version20151210170415 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode 
            ADD oldPeriode1_id INT DEFAULT NULL, 
            ADD oldPeriode2_id INT DEFAULT NULL, 
            ADD oldPeriode3_id INT DEFAULT NULL, 
            ADD oldPeriode4_id INT DEFAULT NULL, 
            ADD oldPeriode5_id INT DEFAULT NULL, 
            DROP matieres
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode 
            ADD CONSTRAINT FK_A70BD7B884B03E82 FOREIGN KEY (oldPeriode1_id) 
            REFERENCES formalibre_bulletin_periode (id)
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode 
            ADD CONSTRAINT FK_A70BD7B89605916C FOREIGN KEY (oldPeriode2_id) 
            REFERENCES formalibre_bulletin_periode (id)
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode 
            ADD CONSTRAINT FK_A70BD7B82EB9F609 FOREIGN KEY (oldPeriode3_id) 
            REFERENCES formalibre_bulletin_periode (id)
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode 
            ADD CONSTRAINT FK_A70BD7B8B36ECEB0 FOREIGN KEY (oldPeriode4_id) 
            REFERENCES formalibre_bulletin_periode (id)
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode 
            ADD CONSTRAINT FK_A70BD7B8BD2A9D5 FOREIGN KEY (oldPeriode5_id) 
            REFERENCES formalibre_bulletin_periode (id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A70BD7B884B03E82 ON formalibre_bulletin_periode (oldPeriode1_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A70BD7B89605916C ON formalibre_bulletin_periode (oldPeriode2_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A70BD7B82EB9F609 ON formalibre_bulletin_periode (oldPeriode3_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A70BD7B8B36ECEB0 ON formalibre_bulletin_periode (oldPeriode4_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_A70BD7B8BD2A9D5 ON formalibre_bulletin_periode (oldPeriode5_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode 
            DROP FOREIGN KEY FK_A70BD7B884B03E82
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode 
            DROP FOREIGN KEY FK_A70BD7B89605916C
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode 
            DROP FOREIGN KEY FK_A70BD7B82EB9F609
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode 
            DROP FOREIGN KEY FK_A70BD7B8B36ECEB0
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode 
            DROP FOREIGN KEY FK_A70BD7B8BD2A9D5
        ");
        $this->addSql("
            DROP INDEX IDX_A70BD7B884B03E82 ON formalibre_bulletin_periode
        ");
        $this->addSql("
            DROP INDEX IDX_A70BD7B89605916C ON formalibre_bulletin_periode
        ");
        $this->addSql("
            DROP INDEX IDX_A70BD7B82EB9F609 ON formalibre_bulletin_periode
        ");
        $this->addSql("
            DROP INDEX IDX_A70BD7B8B36ECEB0 ON formalibre_bulletin_periode
        ");
        $this->addSql("
            DROP INDEX IDX_A70BD7B8BD2A9D5 ON formalibre_bulletin_periode
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode 
            ADD matieres VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
            DROP oldPeriode1_id, 
            DROP oldPeriode2_id, 
            DROP oldPeriode3_id, 
            DROP oldPeriode4_id, 
            DROP oldPeriode5_id
        ");
    }
}