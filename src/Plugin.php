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
        Event::on(View::class, View::EVENT_AFTER_RENDER_TEMPLATE, function($event) {
            $event->output = strtr($event->output, [
                Extension::PH_HEAD_BEGIN => $this->_renderCookieJs($event->sender),
            ]);
        });

        Event::on(View::class, View::EVENT_AFTER_RENDER_PAGE_TEMPLATE, function($event) {
            $whiteOrBlackList = Plugin::getInstance()->cookieconsent->getWhiteOrBlackList(false);

            if ($whiteOrBlackList) {
                $html = $event->output;

                $dom = Dom::loadHtml($html);

                foreach ($dom->getElementsByTagName('script') as $script) {
                    $src = $script->getAttribute('src');

                    if ($whiteOrBlackList['whiteOrBlack'] == Settings::WHITELIST && $whiteOrBlackList['value']) {
                        if ($src && !preg_match($whiteOrBlackList['value'], $src)) {
                            $script->setAttribute('type', 'javascript/blocked');
                        }
                    }

                    if ($whiteOrBlackList['whiteOrBlack'] == Settings::BLACKLIST && $whiteOrBlackList['value']) {
                        if ($src && preg_match($whiteOrBlackList['value'], $src)) {
                            $script->setAttribute('type', 'javascript/blocked');
                        }
                    }
                }

                $event->output = $dom->saveHTML();
            }
        });
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
}
