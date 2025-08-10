<?php

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create migrations table
 */
final class CreateMigrationsTable extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->createTable('migrations');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('migration', 'string', ['length' => 255]);
        $table->addColumn('batch', 'integer');
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('migrations');
    }
}
