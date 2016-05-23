<?php

namespace FormaLibre\BulletinBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2016/05/23 11:55:27
 */
class Version20160523115525 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode 
            ADD periode_set INT DEFAULT 1 NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode 
            DROP periode_set
        ");
    }
}