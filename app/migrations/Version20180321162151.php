<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180321162151 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE abstract_organizational_chart_item (id INT UNSIGNED AUTO_INCREMENT NOT NULL, parent_id INT UNSIGNED DEFAULT NULL, label VARCHAR(255) DEFAULT NULL, type VARCHAR(20) NOT NULL, INDEX IDX_4300BEE5727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE referent_person_link (id INT UNSIGNED AUTO_INCREMENT NOT NULL, person_item_id INT UNSIGNED DEFAULT NULL, referent_id SMALLINT UNSIGNED DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, postal_address VARCHAR(255) DEFAULT NULL, INDEX IDX_BC75A60A30F1E1D7 (person_item_id), INDEX IDX_BC75A60A35E47E35 (referent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE abstract_organizational_chart_item ADD CONSTRAINT FK_4300BEE5727ACA70 FOREIGN KEY (parent_id) REFERENCES abstract_organizational_chart_item (id)');
        $this->addSql('ALTER TABLE referent_person_link ADD CONSTRAINT FK_BC75A60A30F1E1D7 FOREIGN KEY (person_item_id) REFERENCES abstract_organizational_chart_item (id)');
        $this->addSql('ALTER TABLE referent_person_link ADD CONSTRAINT FK_BC75A60A35E47E35 FOREIGN KEY (referent_id) REFERENCES referent (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE abstract_organizational_chart_item DROP FOREIGN KEY FK_4300BEE5727ACA70');
        $this->addSql('ALTER TABLE referent_person_link DROP FOREIGN KEY FK_BC75A60A30F1E1D7');
        $this->addSql('DROP TABLE abstract_organizational_chart_item');
        $this->addSql('DROP TABLE referent_person_link');
    }
}
