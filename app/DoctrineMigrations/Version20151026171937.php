<?php

namespace Surfnet\Conext\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151026171937 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE jira_report DROP issue_status, DROP issue_priority, DROP issue_summary, DROP issue_description');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE jira_report ADD issue_status VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD issue_priority VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD issue_summary VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD issue_description VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
