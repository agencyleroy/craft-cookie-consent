<?php

namespace agencyleroy\craftcookieconsent\migrations;

use craft\db\Migration;
use craft\db\Table;

/**
 *
 */
class Install extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable(
            '{{%cookie_consent_content}}',
            [
                'id' => $this->primaryKey(),
                'siteId' => $this->integer(),
                'header' => $this->text(),
                'message' => $this->text(),
                'dismiss' => $this->text(),
                'allow' => $this->text(),
                'deny' => $this->text(),
                'policy' => $this->text(),
                'link' => $this->text(),
                'entityId' => $this->integer(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid()->notNull(),
            ]
        );

        $this->addForeignKey(
            null,
            '{{%cookie_consent_content}}',
            ['siteId'],
            Table::SITES,
            ['id'],
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%cookie_consent_content}}');
    }
}
