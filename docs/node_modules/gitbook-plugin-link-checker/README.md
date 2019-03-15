# gitbook-plugin-link-checker

This plugin checks two things:

- If links to documents within the book are not with a domain (i. e. don't start
with `http://` or `https://`)
- If links to documents are not pointing to non-existing files

You can either set the plugin to break the build or flush warnings. It works
only for markdown link syntax `[text](url)`, it doesn't work for plain HTML.

Options:

- `fqdn` - List of domains that the checker should check. If any of those occur in a link, it's bad.
- `dieOnError` - If set to true, the build will stop, otherwise the plugin will flush only warnings

To add this plugin, enter the following in the `book.json` file:

```json
{
    "plugins": [ "link-checker" ],
    "pluginsConfig": {
        "link-checker": {
            "fqdn": [
              "developers.windingtree.com"
            ],
            "dieOnError": false
        }
    }
}
```
