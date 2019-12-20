import 'cookieconsent';
import { unblock } from 'yett';

const cookieName = 'cookieconsent_status';

const cookieConsentStatus = cookieconsent.utils.getCookie(cookieName);

if (cookieConsentStatus == 'allow' || cookieConsentStatus == 'dismiss') {
    unblock();
}

class CookieConsent {
    constructor(CC, settings) {
        CC.initialise({
            revokable: true,
            content: {
                header: settings.header,
                message: settings.message,
                dismiss: settings.dismiss,
                allow: settings.allow,
                deny: settings.deny,
                link: settings.link,
                href: settings.href,
                close: '&#x274c;',
                policy: settings.policy,
                target: '_blank',
            },
            cookie: {
                name: cookieName,
            },
            type: settings.type,
            onInitialise: (status) => this.onInitialise(status),
            onStatusChange: (status, chosenBefore) => this.onStatusChange(status, chosenBefore),
        })
    }

    onInitialise(status) {
        if (status == 'allow' || status == 'dismiss') {
            unblock();
        }
    }

    onStatusChange(status, chosenBefore) {
        if (status == 'allow' || status == 'dismiss') {
            unblock();
            location.reload();
        }
    }
}

document.addEventListener("DOMContentLoaded", function() {
    new CookieConsent(window.cookieconsent, window.COOKIE_CONSENT_SETTINGS);
})
