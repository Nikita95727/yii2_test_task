<?php

use yii\db\Migration;

/**
 * Creates album table.
 */
class m250303_000003_create_album_table extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%album}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'title' => $this->string(255)->notNull(),
        ], $tableOptions);

        $this->createIndex('idx-album-user_id', '{{%album}}', 'user_id');
        $this->addForeignKey(
            'fk-album-user_id',
            '{{%album}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-album-user_id', '{{%album}}');
        $this->dropTable('{{%album}}');
    }
}
