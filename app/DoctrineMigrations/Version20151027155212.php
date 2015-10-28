<?php

namespace Surfnet\Conext\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151027155212 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE jira_report CHANGE entity_id entity_id VARCHAR(255) NOT NULL COMMENT \'(DC2Type:evf_entity_id)\', CHANGE entity_type entity_type VARCHAR(10) NOT NULL COMMENT \'(DC2Type:evf_entity_type)\'');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE jira_report CHANGE entity_id entity_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE entity_type entity_type VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci');
    }
}
