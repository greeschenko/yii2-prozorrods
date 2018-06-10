<?php

use yii\db\Migration;

/**
 * Class m180610_195707_fix_main_proid.
 */
class m180610_195707_fix_main_proid extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('ds_upload_candidates', 'main_proid', 'varchar(255) NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
