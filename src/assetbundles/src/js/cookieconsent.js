let yett = require('yett');

import 'cookieconsent';

class CookieConsent {
    constructor(CC, settings) {
        CC.initialise({
            revokable: true,
            cookie: {
                name: settings.cookieName,
                expiryDays: settings.cookieExpiryDays,
            },
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
            window: '<div role="dialog" aria-live="polite" aria-label="cookieconsent" aria-describedby="cookieconsent:desc" class="cc-window {{classes}}"><div class="cc-content-container"><!--googleoff: all-->{{children}}<!--googleon: all--></div></div>',
            type: settings.type,
            onInitialise: function(status) {
                if (this.hasConsented()) {
                    yett.unblock();
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
