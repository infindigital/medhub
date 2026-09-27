#!/usr/bin/env bash
# WP-CLI for the LocalWP "medhub" site (local copy only): tooling/local/wp.sh <args>
SITE_ID=X9b_JeZrh
PHP="/c/Users/Infin Digital/AppData/Roaming/Local/lightning-services/php-8.2.29+0/bin/win64/php.exe"
INI="/c/Users/Infin Digital/AppData/Roaming/Local/run/$SITE_ID/conf/php/php.ini"
PHAR="$(cd "$(dirname "$0")" && pwd -W)/wp-cli.phar"
"$PHP" -c "$INI" -d display_startup_errors=0 -d mysqli.default_port=10017 "$PHAR" --path="C:/Users/Infin Digital/Local Sites/medhub/app/public" "$@"
