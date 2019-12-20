<?php

namespace agencyleroy\craftcookieconsent;

use agencyleroy\craftcookieconsent\models\Settings;
use agencyleroy\craftcookieconsent\services\CookieConsentService;
use agencyleroy\craftcookieconsent\assetbundles\CookieConsentAsset;
use agencyleroy\craftcookieconsent\records\CookieConsentRecord;
use agencyleroy\craftcookieconsent\web\twig\Extension;

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
     *
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
     *
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
     *
     */
    private function _showCookieCompilance()
    {
        if (Craft::$app->request->isSiteRequest) {
            $cookieConsentSettings = Plugin::getInstance()->cookieconsent->getCookieConsentSettings();
            $blackListSettings = Plugin::getInstance()->cookieconsent->getBlackList();
            $whiteListSettings = Plugin::getInstance()->cookieconsent->getWhiteList();

            Craft::$app->getView()->registerJsVar('COOKIE_CONSENT_SETTINGS', $cookieConsentSettings, Extension::POS_HEAD_BEGIN);

            if ($blackListSettings) {
                Craft::$app->getView()->registerJs("var YETT_BLACKLIST = $blackListSettings ;", Extension::POS_HEAD_BEGIN);
            }

            if ($whiteListSettings) {
                Craft::$app->getView()->registerJs("var YETT_WHITELIST = $whiteListSettings ;", Extension::POS_HEAD_BEGIN);
            }

            Craft::$app->getView()->registerAssetBundle(CookieConsentAsset::class);
        }
    }

    /**
     *
     */
    private function _viewEvents()
    {
        Event::on(View::class, View::EVENT_AFTER_RENDER_TEMPLATE, function($event) {
            $event->output = strtr($event->output, [
                Extension::PH_HEAD_BEGIN => $this->_renderCookieJs($event->sender),
            ]);
        });
    }

    /**
     *
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
