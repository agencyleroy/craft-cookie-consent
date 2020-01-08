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
    const WHITELIST = 1;

    /**
     *
     */
    const BLACKLIST = 2;

    /**
     *
     */
    public $type = 'info';

    /**
     *
     */
    public $whiteorblacklist = self::BLACKLIST;

    /**
     *
     */
    public $whitelist = '';

    /**
     *
     */
    public $blacklist = '';
}
