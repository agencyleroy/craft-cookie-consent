import 'cookieconsent';
import { unblock } from 'yett';

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
            type: settings.type,
            onInitialise: function(status) {
                if (this.hasConsented()) {
                    unblock();
                }
            },
            onStatusChange: function(status, chosenBefore) {
                location.reload();
            }
        })
    }
}

document.addEventListener("DOMContentLoaded", function() {
    new CookieConsent(window.cookieconsent, window.COOKIE_CONSENT_SETTINGS);
})
