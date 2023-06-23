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

}
