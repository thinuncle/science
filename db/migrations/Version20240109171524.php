<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Tables for OAuth 2 server
 *
 * See: https://bshaffer.github.io/oauth2-server-php-docs/cookbook/
 */
class Version20240109171524 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {

    $this->addSql('CREATE TABLE `roles` (
                      `id` INT NOT NULL AUTO_INCREMENT,
                      `name` VARCHAR(45) NULL,
                      PRIMARY KEY (`id`))');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('roles');

    }
}
