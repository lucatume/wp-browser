FROM billryan/gitbook

MAINTAINER Luca Tumedei <luca@theaveragedev.com>

# Fix a NOENT issue; see https://github.com/GitbookIO/gitbook-cli/issues/55#issuecomment-455150519
RUN v="$(gitbook -V | grep ^GitBook | cut -f3 -d' ')" \
    && echo "Version: $v" \
    && sed -i 's/confirm: true/confirm: false/g' "$HOME/.gitbook/versions/$v/lib/output/website/copyPluginAssets.js"

# Expose live-reload port.
EXPOSE 35729
