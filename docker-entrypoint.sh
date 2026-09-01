#!/bin/sh
set -e

if [ -n "$AIVEN_CA_CERT" ]; then
    printf '%s\n' "$AIVEN_CA_CERT" > /app/ca.pem
    chmod 644 /app/ca.pem
fi

export MYSQL_ATTR_SSL_CA=/app/ca.pem

exec "$@"
