<?php

namespace agencyleroy\craftcookieconsent;

use agencyleroy\craftcookieconsent\models\Settings;
use agencyleroy\craftcookieconsent\services\CookieConsentService;
use agencyleroy\craftcookieconsent\assetbundles\CookieConsentAsset;
use agencyleroy\craftcookieconsent\records\CookieConsentRecord;
use agencyleroy\craftcookieconsent\web\twig\Extension;
use agencyleroy\craftcookieconsent\helpers\Dom;

use Craft;
use yii\base\Event;
use craft\web\View;
use craft\web\UrlManager;
use craft\web\twig\variables\Cp;
use craft\helpers\Json;
use craft\helpers\Html;

/**
 *
 */
class Plugin extends \craft\base\Plugin
{

    /**
     * @inheritdoc
     */
    public $hasCpSettings = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->setComponents([
            'cookieconsent' => CookieConsentService::class,
        ]);

        $this->_registerCpUrlRules();
        $this->_registerCpNavItems();
        $this->_showCookieCompilance();
        $this->_viewEvents();

        Craft::$app->view->twig->addExtension(new Extension());
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('craft-cookie-consent/settings', [
            'settings' => $this->getSettings()
        ]);
    }

    /**
     * Registers Control panel URL rules.
     */
    public function _registerCpUrlRules()
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (Event $event) {
                $event->rules['craftcookieconsent'] = 'craft-cookie-consent/cookieconsent';
            }
        );
    }

    /**
     * Register Control panel navigation items.
     */
    private function _registerCpNavItems()
    {
        Event::on(
            Cp::class,
            Cp::EVENT_REGISTER_CP_NAV_ITEMS,
            function (Event $event) {
                $event->navItems[] = [
                    'url' => 'craftcookieconsent',
                    'label' => 'Cookie consent',
                ];
            }
        );
    }

    /**
     * Register assets for the cookie compilance banner on site requests.
     */
    private function _showCookieCompilance()
    {
        if (Craft::$app->request->isSiteRequest) {
            $cookieConsentSettings = Plugin::getInstance()->cookieconsent->getCookieConsentSettings();
            $whiteOrBlackList = Plugin::getInstance()->cookieconsent->getWhiteOrBlackList();

            Craft::$app->getView()->registerJsVar('COOKIE_CONSENT_SETTINGS', $cookieConsentSettings, Extension::POS_HEAD_BEGIN);

            if ($whiteOrBlackList) {
                $name = $whiteOrBlackList['name'];
                $value = $whiteOrBlackList['value'];
                Craft::$app->getView()->registerJs("var $name = $value ;", Extension::POS_HEAD_BEGIN);
            }

            Craft::$app->getView()->registerAssetBundle(CookieConsentAsset::class);
        }
    }

    /**
     * Register view events.
     *
     * This includes:
     * - Return JS code registered with [[registerJs()]] with the position set to [[POS_HEAD_BEGIN]].
     * - Set type attribute to `javascript/blocked` for script tags with a src to blacklisted domains.
     */
    private function _viewEvents()
    {
        Event::on(
            View::class,
            View::EVENT_AFTER_RENDER_TEMPLATE,
            function (Event $event) {
                $event->output = strtr($event->output, [
                    Extension::PH_HEAD_BEGIN => $this->_renderCookieJs($event->sender),
                ]);
            }
        );

        Event::on(
            View::class,
            View::EVENT_AFTER_RENDER_PAGE_TEMPLATE,
            function(Event $event) {
                if (!Craft::$app->request->isSiteRequest) return $event->output;

                $event->output = self::blockScripts($event->output);
            }
        );
    }

    /**
     * Returns the content to be inserted at the beginning of the head section.
     */
    private function _renderCookieJs(View $view)
    {
        $lines = [];

        if (!empty($view->js[Extension::POS_HEAD_BEGIN])) {
            $lines[] = Html::script(implode("\n", $view->js[Extension::POS_HEAD_BEGIN]));
        }

        if (!empty($view->jsFiles[Extension::POS_HEAD_BEGIN])) {
            $lines[] = implode("\n", $view->jsFiles[Extension::POS_HEAD_BEGIN]);
        }

        return empty($lines) ? '' : implode("\n", $lines);
    }

    /**
     * Find script tags with src attributes that match the white or blacklist
     * and add a type="javascript/blocked" attribute.
     *
     * @param string $html The html for the page being rendered.
     * @return string
     */
    protected static function blockScripts(string $html)
    {
        $html = preg_replace_callback(
            '/<script\b[^>]*src="(?<src>[^"]*)"[^>]?>/',
            ['self', 'blockScript'],
            $html
        );

        return $html;
    }

    /**
     * Filter script tags based on their src attribute.
     *
     * @param array $matches Regex matches containing a script tag and src attribute.
     * @return string
     */
    protected static function blockScript(array $matches): string
    {
        $script = $matches[0];
        $src = $matches['src'];

        // Exit early for cookie consent script
        if (preg_match('/cookieconsent\.js/', $src)) return $script;

        $whiteOrBlackList = Plugin::getInstance()->cookieconsent->getWhiteOrBlackList(false);

        // Find scripts not included in the whitelist
        if ($whiteOrBlackList['whiteOrBlack'] == Settings::WHITELIST && $whiteOrBlackList['value']) {
            if ($src && !preg_match($whiteOrBlackList['value'], $src)) {
                $script = self::addReplaceType($script);
            }
        }

        // Find scripts included in the blacklist
        if ($whiteOrBlackList['whiteOrBlack'] == Settings::BLACKLIST && $whiteOrBlackList['value']) {
            if ($src && preg_match($whiteOrBlackList['value'], $src)) {
                $script = self::addReplaceType($script);
            }
        }

        return $script;
    }

    /**
     * Add or replace the src attribute with "javascript/blocked".
     *
     * @param string $script The script tag that should be blocked.
     * @return string
     */
    protected static function addReplaceType(string $script)
    {
        // Find a type attribute to replace
        if (preg_match('/(?<type>type="[^"]*")/', $script, $matches, PREG_OFFSET_CAPTURE)) {
            $startPos = $matches['type'][1];
            $endPos = $startPos + strlen($matches['type'][0]);
        }
        // Add the type attribute on the first whitespace
        else {
            $startPos = strpos($script, ' ') + 1;
            $endPos = $startPos - 1;
        }

        $preSplitHtml = substr($script, 0, $startPos);
        $postSplitHtml = substr($script, $endPos);

        $script = $preSplitHtml . 'type="javascript/blocked"' . $postSplitHtml;

        return $script;
    }
}
