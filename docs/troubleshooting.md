## Troubleshooting common issues

### Downloads fail in Windows

If you're using code or commands, e.g. [the `chromedriver:update` one](commands.md#chromedriverupdate), that download files and those keep failing with a message like the following:

```
File ... download failed: SSL certificate problem: unable to get local issuer certificate
```

It's likely the issue originates from PHP not having access to the system certificate store.

You can fix this by downloading the [certificates](https://curl.haxx.se/docs/caextract.html) file and setting the `curl.cainfo` and `openssl.cafile` PHP configuration options to point to it:

```ini
curl.cainfo = "C:\path\to\cacert.pem"
openssl.cafile = "C:\path\to\cacert.pem"
```
