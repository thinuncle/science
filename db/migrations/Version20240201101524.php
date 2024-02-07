<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Tables for OAuth 2 server
 *
 * See: https://bshaffer.github.io/oauth2-server-php-docs/cookbook/
 */
class Version20240201101524 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {

        $this->addSql('ALTER TABLE `oauth_users`
                        ADD COLUMN `firstName` VARCHAR(255) NULL DEFAULT NULL AFTER `parent_id`,
                        ADD COLUMN `lastName` VARCHAR(255) NULL DEFAULT NULL AFTER `firstName`,
                        ADD COLUMN `subRole` VARCHAR(100) NULL DEFAULT NULL AFTER `lastName`');
        $this->addSql('CREATE TABLE `faqs` (
                                      `id` INT NOT NULL AUTO_INCREMENT,
                                      `value` TEXT NOT NULL,
                                      PRIMARY KEY (`id`))');

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
