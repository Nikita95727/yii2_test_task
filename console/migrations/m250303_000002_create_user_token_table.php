<?php

use yii\db\Migration;

/**
 * Creates user_token table for Bearer auth (token_hash).
 */
class m250303_000002_create_user_token_table extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user_token}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'token_hash' => $this->string(64)->notNull()->unique(),
            'created_at' => $this->integer()->notNull(),
            'revoked_at' => $this->integer()->null(),
        ], $tableOptions);

        $this->createIndex('idx-user_token-user_id', '{{%user_token}}', 'user_id');
        $this->addForeignKey(
            'fk-user_token-user_id',
            '{{%user_token}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-user_token-user_id', '{{%user_token}}');
        $this->dropTable('{{%user_token}}');
    }
}
