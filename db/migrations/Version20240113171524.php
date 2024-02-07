<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Tables for OAuth 2 server
 *
 * See: https://bshaffer.github.io/oauth2-server-php-docs/cookbook/
 */
class Version20240113171524 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {

        $this->addSql('ALTER TABLE `competitions`
                      ADD COLUMN `number` INT(11) NOT NULL AFTER `name`');

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
