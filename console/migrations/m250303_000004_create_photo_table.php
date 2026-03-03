<?php

use yii\db\Migration;

/**
 * Creates photo table. URL is virtual (not stored in DB).
 */
class m250303_000004_create_photo_table extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%photo}}', [
            'id' => $this->primaryKey(),
            'album_id' => $this->integer()->notNull(),
            'title' => $this->string(255)->notNull(),
        ], $tableOptions);

        $this->createIndex('idx-photo-album_id', '{{%photo}}', 'album_id');
        $this->addForeignKey(
            'fk-photo-album_id',
            '{{%photo}}',
            'album_id',
            '{{%album}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-photo-album_id', '{{%photo}}');
        $this->dropTable('{{%photo}}');
    }
}
