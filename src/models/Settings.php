<?php

namespace agencyleroy\craftcookieconsent\models;

use craft\base\Model;

/**
 *
 */
class Settings extends Model
{

    /**
     *
     */
    public $type = 'info';

    /**
     *
     */
    public $whitelist = '';

    /**
     *
     */
    public $blacklist = '';
}
