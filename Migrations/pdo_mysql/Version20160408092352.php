<?php

namespace FormaLibre\BulletinBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2016/04/08 09:23:53
 */
class Version20160408092352 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE formalibre_bulletin_point_code (
                id INT AUTO_INCREMENT NOT NULL, 
                code INT NOT NULL, 
                info VARCHAR(255) NOT NULL, 
                short_info VARCHAR(255) NOT NULL, 
                is_default_value TINYINT(1) NOT NULL, 
                ignored TINYINT(1) NOT NULL, 
                UNIQUE INDEX UNIQ_F9B6F65A77153098 (code), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode 
            ADD locked TINYINT(1) DEFAULT '0' NOT NULL, 
            ADD published TINYINT(1) DEFAULT '1' NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE formalibre_bulletin_point_code
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode 
            DROP locked, 
            DROP published
        ");
    }
}