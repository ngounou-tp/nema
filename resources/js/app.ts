import { createInertiaApp } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';
import { installI18n, syncLocaleWithServer } from '@/lib/i18n';

// The product name, whatever APP_NAME says in a given environment.
const appName = 'NEMA';

void createInertiaApp({
    title: (title) => (title ? `${title} · ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
            case name === 'Error':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    withApp: (app, { page }) => {
        installI18n(app, page.props.locale);

        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });
    },
    progress: {
        color: '#006786',
    },
});

// Keeps the interface language in step with the server after each visit...
syncLocaleWithServer();

// This will listen for flash toast data from the server...
initializeFlashToast();
