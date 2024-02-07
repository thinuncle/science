<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Tables for OAuth 2 server
 *
 * See: https://bshaffer.github.io/oauth2-server-php-docs/cookbook/
 */
class Version20240110171524 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {

    $this->addSql('CREATE TABLE `blocks` (
                      `id` INT NOT NULL AUTO_INCREMENT,
                      `name` VARCHAR(45) NULL,
                      `sort` INT(5) NULL DEFAULT NULL,
                      PRIMARY KEY (`id`))');
    $this->addSql('CREATE TABLE `competitions` (
                      `id` INT NOT NULL AUTO_INCREMENT,
                      `name` VARCHAR(45) NULL,
                      `parent_id` INT(10) NULL DEFAULT NULL,
                      `sort` INT(5) NULL DEFAULT NULL,
                      PRIMARY KEY (`id`))');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('blocks');
        $schema->dropTable('competitions');

    }
}
