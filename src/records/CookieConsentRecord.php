<?php

namespace agencyleroy\craftcookieconsent\records;

use Craft;
use craft\db\ActiveRecord;

class CookieConsentRecord extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%cookie_consent_content}}';
    }

    /**
     * Entry|null
     */
    private $_entity;

    /**
     * Url|null
     */
    private $_href;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['header'] = Craft::t('app', 'Header');
        $labels['message'] = Craft::t('app', 'Message');
        $labels['dismiss'] = Craft::t('app', 'Dismiss');
        $labels['allow'] = Craft::t('app', 'Allow');
        $labels['deny'] = Craft::t('app', 'Deny');
        $labels['policy'] = Craft::t('app', 'Cookie policy');
        $labels['link'] = Craft::t('app', 'Link');
        $labels['entityId'] = Craft::t('app', 'Cookie policy entry');

        return $labels;
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        return ['href'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['header', 'message', 'dismiss', 'allow', 'deny', 'policy', 'link', 'entityId'], 'required'];

        return $rules;
    }

    /**
     * Returns the cookie consent's entity url.
     */
    public function getHref()
    {
        if ($this->entity !== null) {
            return $this->entity->url;
        }
    }

    /**
     * Returns the cookie consent's entity.
     *
     * @return Entry|null
     * @throws InvalidConfigException if [[entityId]] is set but invalid
     */
    public function getEntity()
    {
        if ($this->_entity !== null) {
            return $this->_entity;
        }

        if ($this->entityId === null || $this->entityId === '') {
            return null;
        }

        if (($this->_entity = Craft::$app->getEntries()->getEntryById($this->entityId)) === null) {
            throw new InvalidConfigException('Invalid entry ID: ' . $this->entityId);
        }

        return $this->_entity;
    }

    /**
     * Sets the cookie consent's entity.
     *
     * @param Entry|null $entity
     */
    public function setEntity($value)
    {
        if (is_array($value)) {
            $this->entityId = reset($value);
        }

        if (is_string($value)) {
            $this->entityId = $value;
        }
    }
}
