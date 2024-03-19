Airplane Mode
========================

## Contributors
* [Andrew Norcross](https://github.com/norcross)
* [John Blackbourn](https://github.com/johnbillion)
* [Andy Fragen](https://github.com/afragen)
* [Viktor Szépe](https://github.com/szepeviktor)
* [Chris Christoff](https://github.com/chriscct7)
* [Mark Jaquith](https://github.com/markjaquith)

## About
Control loading of external files when developing locally. WP loads certain external files (fonts, Gravatar, etc.) and makes external HTTP calls. This isn't usually an issue, unless you're working in an evironment without a web connection. This plugin removes/unhooks those actions to reduce load time and avoid errors due to missing files.

## Current Actions
* removes external JS and CSS files from loading
* replaces all instances of Gravatar with a local image to remove external call
* removes all HTTP requests
* disables all WP update checks for core, languages, themes, and plugins
* includes toggle in admin bar for quick enable / disable

## Changelog

See [CHANGES.md](CHANGES.md).

## Notes
If you need offline activation, see [this script](https://gist.github.com/solepixel/e1d03f4dcd1b9e86552b3cc6937325bf) written by [Brian DiChiara](https://github.com/solepixel)

## Roadmap
* fine tune HTTP request removal
* find other calls from core
* add other requests from popular plugins

#### [Pull requests](https://github.com/norcross/airplane-mode/pulls) are very much welcome and encouraged.
