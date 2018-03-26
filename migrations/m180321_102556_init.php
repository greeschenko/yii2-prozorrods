<?php

use yii\db\Migration;

/**
 * Class m180321_102556_init.
 */
class m180321_102556_init extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%ds_upload_candidates}}', [
            'id' => $this->primaryKey(),
            'main_proid' => $this->integer(),
            'main_class' => $this->string(),
            'child_proid' => $this->integer(),
            'child_class' => $this->string(),
            'groupstoupload' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%ds_upload_candidates}}');

        return false;
    }
}
