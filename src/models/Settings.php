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
     * The compliance type, which refers to the compliance above.
     * The standard cookie consent popup is purely informational.
     */
    public $type = 'info';

    /**
     * Name of the cookie that keeps track of users choice.
     */
    public $cookieName = 'cookieconsent_status';

    /**
     * The cookies expire date, specified in days.
     */
    public $cookieExpiryDays = 365;

    /**
     * Wheter the whitelist or blacklist should be used for blocking scripts.
     */
    public $whiteorblacklist = self::BLACKLIST;

    /**
     * List of whitelisted domains.
     */
    public $whitelist = '';

    /**
     * List of blacklisted domains.
     */
    public $blacklist = '';
}
