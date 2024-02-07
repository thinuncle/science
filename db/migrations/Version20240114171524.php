<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Tables for OAuth 2 server
 *
 * See: https://bshaffer.github.io/oauth2-server-php-docs/cookbook/
 */
class Version20240114171524 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {

        $this->addSql('ALTER TABLE `tasks` ADD COLUMN `id_role` INT(11) NOT NULL AFTER `id_module`');
        $this->addSql('ALTER TABLE `task_answers` ADD COLUMN `assigned_user_id` INT(11) NOT NULL AFTER `id_user`');

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
