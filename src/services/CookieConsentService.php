<?php

namespace agencyleroy\craftcookieconsent\services;

use agencyleroy\craftcookieconsent\Plugin;
use agencyleroy\craftcookieconsent\records\CookieConsentRecord;
use agencyleroy\craftcookieconsent\models\Settings;

use Craft;
use craft\base\Component;
use craft\models\Site;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;

/**
 *
 */
class CookieConsentService extends Component
{

    /**
     * Returns cookie consent site specific settings.
     *
     * @param Site|null $site
     * @return array
     */
    public function getCookieConsentSettings(Site $site = null)
    {
        if (!$site) {
            $site = Craft::$app->sites->currentSite;
        }

        $content = [];

        $cookieConsent = CookieConsentRecord::findOne(['siteId' => $site->id]);

        if ($cookieConsent) {
            $content = $cookieConsent->toArray([
                'header',
                'message',
                'dismiss',
                'allow',
                'deny',
                'policy',
                'link',
            ], ['href']);
        }

        $settings = Plugin::getInstance()->getSettings()->toArray(['type']);

        return ArrayHelper::merge($content, $settings);
    }

    /**
     * Returns the whitelist.
     *
     * @param Boolean $jsArray
     * - Whether the return value should be as a JS array or regex string.
     *
     * @return string|null
     */
    public function getWhiteList(Bool $jsArray = true)
    {
        $settings = Plugin::getInstance()->getSettings();

        return $this->_regexify($settings->whitelist, $jsArray);
    }

    /**
     * Returns the blacklist.
     *
     * @param Boolean $jsArray
     * - Whether the return value should be as a JS array or regex string.
     *
     * @return string|null
     */
    public function getBlackList(Bool $jsArray = true)
    {
        $settings = Plugin::getInstance()->getSettings();

        return $this->_regexify($settings->blacklist, $jsArray);
    }

    /**
     *
     */
    public function getWhiteOrBlackList(Bool $jsArray = true)
    {
        $settings = Plugin::getInstance()->getSettings();

        if ($settings->whiteorblacklist == Settings::WHITELIST) {
            return [
                'whiteOrBlack' => $settings->whiteorblacklist,
                'name' => 'YETT_WHITELIST',
                'value' => $this->getWhiteList($jsArray),
            ];
        }

        if ($settings->whiteorblacklist == Settings::BLACKLIST) {
            return [
                'whiteOrBlack' => $settings->whiteorblacklist,
                'name' => 'YETT_BLACKLIST',
                'value' => $this->getBlackList($jsArray),
            ];
        }
    }

    /**
     * Returns a regexified string.
     *
     * @param String $string
     * @param Bool $jsArray
     *
     * @return string|null
     */
    private function _regexify(String $string = '', Bool $jsArray = true)
    {
        if (!$string) return;

        $lines = preg_split('/[\s,]+/', $string);
        $parts = [];

        foreach ($lines as $line) {
            $escaped = addcslashes($line, './');

            if ($jsArray) {
                $parts[] = '/' . $escaped . '/';
            } else {
                $parts[] = $escaped;
            }
        }

        if ($jsArray) {
            return '[' . implode(', ', $parts) . ']';
        }

        return '/(' . implode('|', $parts) . ')/';
    }
}
