<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Tables for OAuth 2 server
 *
 * See: https://bshaffer.github.io/oauth2-server-php-docs/cookbook/
 */
class Version20240111171524 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {

    $this->addSql('CREATE TABLE `modules` (
                      `id` INT(11) NOT NULL AUTO_INCREMENT,
                      `id_competition` INT(11) NOT NULL,
                      `id_role` VARCHAR(45) NOT NULL,
                      `name` VARCHAR(255) NOT NULL,
                      `type` VARCHAR(50) NOT NULL,
                      `sequence` INT(5) NOT NULL,
                      `info_content` TEXT NULL,
                      PRIMARY KEY (`id`))');
    $this->addSql('CREATE TABLE `tasks` (
                      `id` INT(11) NOT NULL AUTO_INCREMENT,
                      `id_module` INT(11) NOT NULL,
                      `type` VARCHAR(45) NOT NULL,
                      `label` VARCHAR(255) NULL DEFAULT NULL,
                      `validationRules` VARCHAR(45) NULL DEFAULT NULL,
                      `defaultValue` TEXT NULL DEFAULT NULL,
                      `sort` INT(5) NOT NULL,
                      PRIMARY KEY (`id`))');
    $this->addSql('CREATE TABLE `task_properties` (
                      `id` INT(11) NOT NULL AUTO_INCREMENT,
                      `id_task` INT(11) NOT NULL,
                      `name` VARCHAR(100) NULL,
                      PRIMARY KEY (`id`))');
    $this->addSql('CREATE TABLE `property_values` (
                      `id` INT(11) NOT NULL AUTO_INCREMENT,
                      `id_property` VARCHAR(45) NULL,
                      `value` VARCHAR(100) NULL,
                      PRIMARY KEY (`id`))');
    $this->addSql('CREATE TABLE `task_answers` (
                      `id` INT(11) NOT NULL AUTO_INCREMENT,
                      `id_task` INT(11) NOT NULL,
                      `id_user` INT(11) NOT NULL,
                      `value` TEXT NULL,
                      PRIMARY KEY (`id`))');
    $this->addSql('CREATE TABLE `module_flows_users` (
                      `id` INT(11) NOT NULL AUTO_INCREMENT,
                      `date_entered` DATETIME NOT NULL,
                      `date_modified` DATETIME NOT NULL,
                      `assigned_user_id` INT(11) NOT NULL,
                      `modified_user_id` INT(11) NOT NULL,
                      `id_module` INT(11) NOT NULL,
                      `status` VARCHAR(45) NULL,
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
