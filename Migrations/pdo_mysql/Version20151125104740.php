<?php

namespace FormaLibre\BulletinBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/11/25 10:47:41
 */
class Version20151125104740 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE formalibre_bulletin_periode (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                start_date DATETIME DEFAULT NULL, 
                end_date DATETIME DEFAULT NULL, 
                degre INT DEFAULT NULL, 
                annee INT DEFAULT NULL, 
                ReunionParent VARCHAR(255) NOT NULL, 
                template VARCHAR(255) NOT NULL, 
                onlyPoint TINYINT(1) DEFAULT NULL, 
                coefficient DOUBLE PRECISION NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE formalibre_bulletin_periode_matieres (
                periode_id INT NOT NULL, 
                coursesession_id INT NOT NULL, 
                INDEX IDX_5A2D5E9EF384C1CF (periode_id), 
                INDEX IDX_5A2D5E9EAE020D6E (coursesession_id), 
                PRIMARY KEY(periode_id, coursesession_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE formalibre_bulletin_periode_point_divers (
                periode_id INT NOT NULL, 
                pointdivers_id INT NOT NULL, 
                INDEX IDX_ECA7F0A7F384C1CF (periode_id), 
                INDEX IDX_ECA7F0A7209EBB93 (pointdivers_id), 
                PRIMARY KEY(periode_id, pointdivers_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE formalibre_bulletin_groupe_titulaire (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                group_id INT NOT NULL, 
                INDEX IDX_7D44F3B3A76ED395 (user_id), 
                INDEX IDX_7D44F3B3FE54D947 (group_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE formalibre_bulletin_pointDivers (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                officialName VARCHAR(255) NOT NULL, 
                withTotal TINYINT(1) DEFAULT NULL, 
                total INT DEFAULT NULL, 
                position INT DEFAULT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE formalibre_bulletin_decision (
                id INT AUTO_INCREMENT NOT NULL, 
                content LONGTEXT NOT NULL, 
                with_matiere TINYINT(1) NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE formalibre_bulletin_periode_eleve_matiere_remarque (
                id INT AUTO_INCREMENT NOT NULL, 
                periode_id INT DEFAULT NULL, 
                matiere_id INT DEFAULT NULL, 
                eleve_id INT DEFAULT NULL, 
                remarque LONGTEXT DEFAULT NULL, 
                INDEX IDX_5D6FB6A0F384C1CF (periode_id), 
                INDEX IDX_5D6FB6A0F46CD258 (matiere_id), 
                INDEX IDX_5D6FB6A0A6CC7B2 (eleve_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE formalibre_bulletin_periode_eleve_decision (
                id INT AUTO_INCREMENT NOT NULL, 
                periode_id INT NOT NULL, 
                user_id INT NOT NULL, 
                decision_id INT NOT NULL, 
                INDEX IDX_2B83F4D5F384C1CF (periode_id), 
                INDEX IDX_2B83F4D5A76ED395 (user_id), 
                INDEX IDX_2B83F4D5BDEE7539 (decision_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE formalibre_bulletin_periode_eleve_decision_matieres (
                periodeelevedecision_id INT NOT NULL, 
                coursesession_id INT NOT NULL, 
                INDEX IDX_52732DEE150B1E55 (periodeelevedecision_id), 
                INDEX IDX_52732DEEAE020D6E (coursesession_id), 
                PRIMARY KEY(
                    periodeelevedecision_id, coursesession_id
                )
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE formalibre_bulletin_matiere_options (
                id INT AUTO_INCREMENT NOT NULL, 
                matiere_id INT DEFAULT NULL, 
                total INT DEFAULT NULL, 
                position INT DEFAULT NULL, 
                color VARCHAR(255) DEFAULT NULL, 
                INDEX IDX_AEF04CC8F46CD258 (matiere_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE formalibre_bulletin_periode_eleve_pointdivers_point (
                id INT AUTO_INCREMENT NOT NULL, 
                periode_id INT DEFAULT NULL, 
                divers_id INT DEFAULT NULL, 
                eleve_id INT DEFAULT NULL, 
                total INT DEFAULT NULL, 
                point DOUBLE PRECISION DEFAULT NULL, 
                position INT DEFAULT NULL, 
                comment LONGTEXT DEFAULT NULL, 
                INDEX IDX_134BF6F8F384C1CF (periode_id), 
                INDEX IDX_134BF6F89C3BA491 (divers_id), 
                INDEX IDX_134BF6F8A6CC7B2 (eleve_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE formalibre_bulletin_periode_eleve_matiere_point (
                id INT AUTO_INCREMENT NOT NULL, 
                periode_id INT DEFAULT NULL, 
                matiere_id INT DEFAULT NULL, 
                eleve_id INT DEFAULT NULL, 
                total INT DEFAULT NULL, 
                point DOUBLE PRECISION DEFAULT NULL, 
                comportement DOUBLE PRECISION DEFAULT NULL, 
                presence DOUBLE PRECISION DEFAULT NULL, 
                position INT DEFAULT NULL, 
                comment LONGTEXT DEFAULT NULL, 
                INDEX IDX_FCB4A752F384C1CF (periode_id), 
                INDEX IDX_FCB4A752F46CD258 (matiere_id), 
                INDEX IDX_FCB4A752A6CC7B2 (eleve_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE lock_status (
                id INT AUTO_INCREMENT NOT NULL, 
                teacher_id INT DEFAULT NULL, 
                periode_id INT DEFAULT NULL, 
                matiere_id INT DEFAULT NULL, 
                `lock` TINYINT(1) NOT NULL, 
                INDEX IDX_E695B68341807E1D (teacher_id), 
                INDEX IDX_E695B683F384C1CF (periode_id), 
                INDEX IDX_E695B683F46CD258 (matiere_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_matieres 
            ADD CONSTRAINT FK_5A2D5E9EF384C1CF FOREIGN KEY (periode_id) 
            REFERENCES formalibre_bulletin_periode (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_matieres 
            ADD CONSTRAINT FK_5A2D5E9EAE020D6E FOREIGN KEY (coursesession_id) 
            REFERENCES claro_cursusbundle_course_session (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_point_divers 
            ADD CONSTRAINT FK_ECA7F0A7F384C1CF FOREIGN KEY (periode_id) 
            REFERENCES formalibre_bulletin_periode (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_point_divers 
            ADD CONSTRAINT FK_ECA7F0A7209EBB93 FOREIGN KEY (pointdivers_id) 
            REFERENCES formalibre_bulletin_pointDivers (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_groupe_titulaire 
            ADD CONSTRAINT FK_7D44F3B3A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_groupe_titulaire 
            ADD CONSTRAINT FK_7D44F3B3FE54D947 FOREIGN KEY (group_id) 
            REFERENCES claro_group (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_matiere_remarque 
            ADD CONSTRAINT FK_5D6FB6A0F384C1CF FOREIGN KEY (periode_id) 
            REFERENCES formalibre_bulletin_periode (id)
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_matiere_remarque 
            ADD CONSTRAINT FK_5D6FB6A0F46CD258 FOREIGN KEY (matiere_id) 
            REFERENCES claro_cursusbundle_course_session (id)
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_matiere_remarque 
            ADD CONSTRAINT FK_5D6FB6A0A6CC7B2 FOREIGN KEY (eleve_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_decision 
            ADD CONSTRAINT FK_2B83F4D5F384C1CF FOREIGN KEY (periode_id) 
            REFERENCES formalibre_bulletin_periode (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_decision 
            ADD CONSTRAINT FK_2B83F4D5A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_decision 
            ADD CONSTRAINT FK_2B83F4D5BDEE7539 FOREIGN KEY (decision_id) 
            REFERENCES formalibre_bulletin_decision (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_decision_matieres 
            ADD CONSTRAINT FK_52732DEE150B1E55 FOREIGN KEY (periodeelevedecision_id) 
            REFERENCES formalibre_bulletin_periode_eleve_decision (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_decision_matieres 
            ADD CONSTRAINT FK_52732DEEAE020D6E FOREIGN KEY (coursesession_id) 
            REFERENCES claro_cursusbundle_course_session (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_matiere_options 
            ADD CONSTRAINT FK_AEF04CC8F46CD258 FOREIGN KEY (matiere_id) 
            REFERENCES claro_cursusbundle_course_session (id)
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_pointdivers_point 
            ADD CONSTRAINT FK_134BF6F8F384C1CF FOREIGN KEY (periode_id) 
            REFERENCES formalibre_bulletin_periode (id)
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_pointdivers_point 
            ADD CONSTRAINT FK_134BF6F89C3BA491 FOREIGN KEY (divers_id) 
            REFERENCES formalibre_bulletin_pointDivers (id)
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_pointdivers_point 
            ADD CONSTRAINT FK_134BF6F8A6CC7B2 FOREIGN KEY (eleve_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_matiere_point 
            ADD CONSTRAINT FK_FCB4A752F384C1CF FOREIGN KEY (periode_id) 
            REFERENCES formalibre_bulletin_periode (id)
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_matiere_point 
            ADD CONSTRAINT FK_FCB4A752F46CD258 FOREIGN KEY (matiere_id) 
            REFERENCES claro_cursusbundle_course_session (id)
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_matiere_point 
            ADD CONSTRAINT FK_FCB4A752A6CC7B2 FOREIGN KEY (eleve_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE lock_status 
            ADD CONSTRAINT FK_E695B68341807E1D FOREIGN KEY (teacher_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE lock_status 
            ADD CONSTRAINT FK_E695B683F384C1CF FOREIGN KEY (periode_id) 
            REFERENCES formalibre_bulletin_periode (id)
        ");
        $this->addSql("
            ALTER TABLE lock_status 
            ADD CONSTRAINT FK_E695B683F46CD258 FOREIGN KEY (matiere_id) 
            REFERENCES claro_cursusbundle_course_session (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_matieres 
            DROP FOREIGN KEY FK_5A2D5E9EF384C1CF
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_point_divers 
            DROP FOREIGN KEY FK_ECA7F0A7F384C1CF
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_matiere_remarque 
            DROP FOREIGN KEY FK_5D6FB6A0F384C1CF
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_decision 
            DROP FOREIGN KEY FK_2B83F4D5F384C1CF
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_pointdivers_point 
            DROP FOREIGN KEY FK_134BF6F8F384C1CF
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_matiere_point 
            DROP FOREIGN KEY FK_FCB4A752F384C1CF
        ");
        $this->addSql("
            ALTER TABLE lock_status 
            DROP FOREIGN KEY FK_E695B683F384C1CF
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_point_divers 
            DROP FOREIGN KEY FK_ECA7F0A7209EBB93
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_pointdivers_point 
            DROP FOREIGN KEY FK_134BF6F89C3BA491
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_decision 
            DROP FOREIGN KEY FK_2B83F4D5BDEE7539
        ");
        $this->addSql("
            ALTER TABLE formalibre_bulletin_periode_eleve_decision_matieres 
            DROP FOREIGN KEY FK_52732DEE150B1E55
        ");
        $this->addSql("
            DROP TABLE formalibre_bulletin_periode
        ");
        $this->addSql("
            DROP TABLE formalibre_bulletin_periode_matieres
        ");
        $this->addSql("
            DROP TABLE formalibre_bulletin_periode_point_divers
        ");
        $this->addSql("
            DROP TABLE formalibre_bulletin_groupe_titulaire
        ");
        $this->addSql("
            DROP TABLE formalibre_bulletin_pointDivers
        ");
        $this->addSql("
            DROP TABLE formalibre_bulletin_decision
        ");
        $this->addSql("
            DROP TABLE formalibre_bulletin_periode_eleve_matiere_remarque
        ");
        $this->addSql("
            DROP TABLE formalibre_bulletin_periode_eleve_decision
        ");
        $this->addSql("
            DROP TABLE formalibre_bulletin_periode_eleve_decision_matieres
        ");
        $this->addSql("
            DROP TABLE formalibre_bulletin_matiere_options
        ");
        $this->addSql("
            DROP TABLE formalibre_bulletin_periode_eleve_pointdivers_point
        ");
        $this->addSql("
            DROP TABLE formalibre_bulletin_periode_eleve_matiere_point
        ");
        $this->addSql("
            DROP TABLE lock_status
        ");
    }
}