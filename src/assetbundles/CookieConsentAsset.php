<?php

namespace agencyleroy\craftcookieconsent\assetbundles;

use craft\web\AssetBundle;
use craft\web\View;
use agencyleroy\craftcookieconsent\Plugin;
use agencyleroy\craftcookieconsent\web\twig\Extension;

/**
 *
 */
class CookieConsentAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = "@agencyleroy/craftcookieconsent/assetbundles/dist";

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->css = [
            'css/cookieconsent.css',
        ];

        $this->js = [
            ['js/cookieconsent.js', 'position' => Extension::POS_HEAD_BEGIN],
        ];

        parent::init();
    }
}
