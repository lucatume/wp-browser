// This file contains the site configuration for the theme.

// Metadata, SEO, and Social
export const SITE_TITLE = "wp-browswer documentation"
export const SITE_DESCRIPTION = "The wp-browswer project documentation site."
export const SITE_URL = "https://wpbrowser.wptestkit.dev"
export const SITE_DEFAULT_OG_IMAGE = "/assets/og-image.png"

// Docs Sidebar
// Define the left sidebar items here.
// The path should match the folder name in src/content/docs/
export const SIDEBAR_ITEMS = {
    "Get Started": [
        "/docs/get-started/installation",
        "/docs/get-started/introduction"
    ],
    "Tutorials": [
        "/docs/tutorials/build-x",
    ],
    "Guides": [
        "/docs/guides/migrate-from-z",
    ]
}

export const SIDEBAR_V3_ITEMS = {
    "Table of contents": [
        "/v3/faq",
        "/v3/codeception-phpunit-and-wpbrowser",
        "/v3/codeception-4-support",
        "/v3/codeception-5-support",
    ],
    "Migration guides": [
        "/v3/migration/from-version-2-to-version-3",
        "/v3/levels-of-testing",
    ],
    "Getting started": [
        "/v3/requirements",
        "/v3/installation",
        "/v3/setting-up-minimum-wordpress-installation",
        "/v3/configuration"
    ],
    "Tutorials": [
        "/v3/tutorials/automatically-change-db-in-tests",
        "/v3/tutorials/vvv-setup",
        "/v3/tutorials/mamp-mac-setup",
        "/v3/tutorials/wamp-setup",
        "/v3/tutorials/local-flywheel-setup"
    ],
    "Modules": [
        "/v3/modules/wordpress-module",
        "/v3/modules/wpbrowser-module",
        "/v3/modules/wpcli-module",
        "/v3/modules/wpdb-module",
        "/v3/modules/wpfilesystem-module",
        "/v3/modules/wploader-module",
        "/v3/modules/wpqueries-module",
        "/v3/modules/wpwebdriver-module",
    ],
    "Advanced usage": [
        "/v3/advanced/run-in-separate-process"
    ],
    "Links": [
        "/v3/events-api",
        "/v3/extensions",
        "/v3/commands",
        "/v3/contributing",
        "/v3/sponsors",
        "https://github.com/lucatume/wp-browser/blob/master/changelog"
    ]
}
