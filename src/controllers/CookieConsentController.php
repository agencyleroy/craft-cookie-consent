<?php

namespace agencyleroy\craftcookieconsent\controllers;

use agencyleroy\craftcookieconsent\Plugin;
use agencyleroy\craftcookieconsent\records\CookieConsentRecord;

use Craft;
use craft\web\Controller;

/**
 *
 */
class CookieconsentController extends Controller
{

    /**
     *
     */
    public function actionIndex()
    {
        $request = Craft::$app->getRequest();
        $sites = Craft::$app->getSites();

        $currentSiteHandle = $request->getQueryParam('site', $sites->currentSite->handle);
        $currentSite = $sites->getSiteByHandle($currentSiteHandle);

        $record = CookieConsentRecord::findOne(['siteId' => $currentSite->id]);

        if (!$record) {
            $record = new CookieConsentRecord();
        }

        $view = $this->getView();
        $view->setTemplateMode($view::TEMPLATE_MODE_CP);

        return $this->renderTemplate('craft-cookie-consent/content', [
            'cookieConsent' => $record,
            'currentSite' => $currentSite,
        ]);
    }

    /**
     *
     */
    public function actionSave()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $sites = Craft::$app->getSites();

        $siteId = $request->getRequiredBodyParam('siteId');
        $currentSite = $sites->getSiteById($siteId);

        $record = CookieConsentRecord::findOne(['siteId' => $siteId]);

        if (!$record) {
            $record = new CookieConsentRecord();
        }

        // Populate the record.
        $record->siteId = $request->getBodyParam('siteId');
        $record->header = $request->getBodyParam('header');
        $record->message = $request->getBodyParam('message');
        $record->dismiss = $request->getBodyParam('dismiss');
        $record->allow = $request->getBodyParam('allow');
        $record->deny = $request->getBodyParam('deny');
        $record->policy = $request->getBodyParam('policy');
        $record->link = $request->getBodyParam('link');
        $record->entity = $request->getBodyParam('entityId');

        if (!$record->save()) {
            Craft::$app->getSession()->setError(Craft::t('app', 'Couldn’t save cookie consent.'));

            return $this->renderTemplate('craft-cookie-consent/content', [
                'cookieConsent' => $record,
                'currentSite' => $currentSite,
            ]);
        }

        return $this->redirectToPostedUrl();
    }
}
