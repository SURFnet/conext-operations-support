<?php

namespace Surfnet\Conext\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151021150412 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE jira_report (id VARCHAR(36) NOT NULL, entity_id VARCHAR(255) NOT NULL, entity_type VARCHAR(10) NOT NULL, test_name VARCHAR(255) NOT NULL, reported_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', issue_id VARCHAR(255) NOT NULL, issue_status VARCHAR(255) NOT NULL, issue_priority VARCHAR(255) NOT NULL, issue_summary VARCHAR(255) NOT NULL, issue_description VARCHAR(255) NOT NULL, UNIQUE INDEX conops_jirareport_uniq_entity_id_type_testname (entity_id, entity_type, test_name, issue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE jira_report');
    }
}
