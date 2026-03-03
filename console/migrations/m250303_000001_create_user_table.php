<?php

use yii\db\Migration;

/**
 * Creates user table. Replaces default yii2 user structure.
 */
class m250303_000001_create_user_table extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
            $this->db->createCommand('SET FOREIGN_KEY_CHECKS=0')->execute();
        }

        $this->dropTableIfExists('{{%user}}');

        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'first_name' => $this->string(100)->notNull(),
            'last_name' => $this->string(100)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        if ($this->db->driverName === 'mysql') {
            $this->db->createCommand('SET FOREIGN_KEY_CHECKS=1')->execute();
        }
    }

    public function safeDown(): void
    {
        if ($this->db->driverName === 'mysql') {
            $this->db->createCommand('SET FOREIGN_KEY_CHECKS=0')->execute();
        }

        $this->dropTable('{{%user}}');

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull()->unique(),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'password_reset_token' => $this->string()->unique(),
            'email' => $this->string()->notNull()->unique(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        if ($this->db->driverName === 'mysql') {
            $this->db->createCommand('SET FOREIGN_KEY_CHECKS=1')->execute();
        }
    }

    private function dropTableIfExists(string $name): void
    {
        $rawName = str_replace(['{{%', '}}'], '', $name);
        if ($this->db->getTableSchema($rawName, true) !== null) {
            $this->dropTable($name);
        }
    }
}
