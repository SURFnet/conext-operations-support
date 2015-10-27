<?php

namespace Surfnet\Conext\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151026172049 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX conops_jirareport_uniq_entity_id_type_testname ON jira_report');
        $this->addSql('ALTER TABLE jira_report CHANGE issue_id issue_key VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX conops_jirareport_uniq_entity_id_type_testname ON jira_report (entity_id, entity_type, test_name, issue_key)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX conops_jirareport_uniq_entity_id_type_testname ON jira_report');
        $this->addSql('ALTER TABLE jira_report CHANGE issue_key issue_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('CREATE UNIQUE INDEX conops_jirareport_uniq_entity_id_type_testname ON jira_report (entity_id, entity_type, test_name, issue_id)');
    }
}
