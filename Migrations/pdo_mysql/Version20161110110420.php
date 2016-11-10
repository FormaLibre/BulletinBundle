<?php

namespace FormaLibre\BulletinBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2016/11/10 11:04:22
 */
class Version20161110110420 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE UNIQUE INDEX pepdp_unique_periode_user_session ON formalibre_bulletin_periode_eleve_pointdivers_point (periode_id, eleve_id, divers_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX pemp_unique_periode_user_session ON formalibre_bulletin_periode_eleve_matiere_point (periode_id, eleve_id, matiere_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX pemp_unique_periode_user_session ON formalibre_bulletin_periode_eleve_matiere_point
        ");
        $this->addSql("
            DROP INDEX pepdp_unique_periode_user_session ON formalibre_bulletin_periode_eleve_pointdivers_point
        ");
    }
}