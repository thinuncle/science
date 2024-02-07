<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Tables for OAuth 2 server
 *
 * See: https://bshaffer.github.io/oauth2-server-php-docs/cookbook/
 */
class Version20240116171524 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {

        $this->addSql('ALTER TABLE `tasks` ADD COLUMN `requireAllFieldToNextStep` TINYINT(1) NULL DEFAULT 0 AFTER `defaultValue`;
');

    }



    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        /*$schema->dropTable('blocks');
        $schema->dropTable('competitions');*/

    }
}
