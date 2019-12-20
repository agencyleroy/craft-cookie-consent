<?php

namespace agencyleroy\craftcookieconsent\services;

use Craft;
use craft\base\Component;
use craft\models\Site;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;
use agencyleroy\craftcookieconsent\Plugin;
use agencyleroy\craftcookieconsent\records\CookieConsentRecord;

/**
 *
 */
class CookieConsentService extends Component
{

    /**
     *
     */
    public function getCookieConsentSettings(Site $site = null)
    {
        if (!$site) {
            $site = Craft::$app->sites->currentSite;
        }

        $cookieConsent = CookieConsentRecord::findOne(['siteId' => $site->id]);

        $content = $cookieConsent->toArray([
            'header',
            'message',
            'dismiss',
            'allow',
            'deny',
            'policy',
            'link',
        ], ['href']);

        $settings = Plugin::getInstance()->getSettings()->toArray(['type']);

        return ArrayHelper::merge($content, $settings);
    }

    /**
     *
     */
    public function getWhiteList()
    {
        $settings = Plugin::getInstance()->getSettings();

        return $this->_regexify($settings->whitelist);
    }

    /**
     *
     */
    public function getBlackList()
    {
        $settings = Plugin::getInstance()->getSettings();

        return $this->_regexify($settings->blacklist);
    }

    /**
     *
     */
    private function _regexify(String $string = '')
    {
        if (!$string) return;

        $lines = preg_split('/[\s,]+/', $string);
        $parts = [];

        foreach ($lines as $line) {
            $parts[] = '/' . addcslashes($line, '.') . '/';
        }

        return '[' . implode(', ', $parts) . ']';
    }
}
