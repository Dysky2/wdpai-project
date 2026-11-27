#!/bin/sh
# Issues and renews the Let's Encrypt certificate for ${DUCKDNS_SUBDOMAIN}.duckdns.org with the
# DNS-01 challenge (DuckDNS TXT record), so no inbound port 80/443 is needed. Runs forever:
# the renewal loop replaces a host crontab (no sudo on the VPS).
set -eu

: "${DUCKDNS_SUBDOMAIN:?set DUCKDNS_SUBDOMAIN (only the name, without .duckdns.org)}"
: "${DUCKDNS_TOKEN:?set DUCKDNS_TOKEN}"
: "${ACME_EMAIL:?set ACME_EMAIL}"

case "$DUCKDNS_SUBDOMAIN" in
    *[!a-z0-9-]* | "")
        echo "[acme] DUCKDNS_SUBDOMAIN must be the bare name (a-z, 0-9, -), e.g. 'myapp' for myapp.duckdns.org" >&2
        exit 1
        ;;
esac

DOMAIN="${DUCKDNS_SUBDOMAIN}.duckdns.org"
SERVER="${ACME_SERVER:-https://acme-v02.api.letsencrypt.org/directory}"
DATA=/data                                       # lego account + certificates (acme-data volume)
CERTS=/certs                                     # fullchain.pem + privkey.pem for nginx (certs volume)
RENEW_INTERVAL="${ACME_RENEW_INTERVAL:-43200}"   # 12 h between renewal checks
RETRY_INTERVAL="${ACME_RETRY_INTERVAL:-3600}"    # 1 h after a failure (Let's Encrypt rate limits)
CRT="$DATA/certificates/$DOMAIN.crt"
KEY="$DATA/certificates/$DOMAIN.key"

export DUCKDNS_TOKEN
export DUCKDNS_PROPAGATION_TIMEOUT="${DUCKDNS_PROPAGATION_TIMEOUT:-300}"

log() { echo "[acme] $(date -u +%Y-%m-%dT%H:%M:%SZ) $*"; }

lego_cmd() {
    /lego --accept-tos --email "$ACME_EMAIL" --server "$SERVER" \
        --dns duckdns --dns.resolvers 1.1.1.1:53 --dns.resolvers 8.8.8.8:53 \
        --domains "$DOMAIN" --path "$DATA" "$@"
}

# Atomic per file (tmp + mv); nginx installs the pair only when certificate and key match.
publish() {
    [ -s "$CRT" ] && [ -s "$KEY" ] || return 1
    umask 077
    cp "$KEY" "$CERTS/.privkey.pem.tmp"
    cp "$CRT" "$CERTS/.fullchain.pem.tmp"
    mv "$CERTS/.privkey.pem.tmp" "$CERTS/privkey.pem"
    mv "$CERTS/.fullchain.pem.tmp" "$CERTS/fullchain.pem"
}

mkdir -p "$DATA" "$CERTS"
log "domain $DOMAIN, CA $SERVER"

# Switching CA (e.g. staging -> production): a `renew` would keep the old certificate, so start over.
if [ -s "$DATA/ca-server" ] && [ "$(cat "$DATA/ca-server")" != "$SERVER" ]; then
    log "CA changed, discarding certificates issued by $(cat "$DATA/ca-server")"
    rm -rf "$DATA/certificates"
fi
echo "$SERVER" > "$DATA/ca-server"

while :; do
    if [ -s "$CRT" ]; then
        log "renewal check (renews when < 30 days left)"
        set -- renew --days 30
    else
        log "requesting the first certificate"
        set -- run
    fi

    if lego_cmd "$@" && publish; then
        log "certificate ready in $CERTS (nginx picks it up within TLS_SYNC_INTERVAL)"
        sleep "$RENEW_INTERVAL"
    else
        log "failed, retrying in ${RETRY_INTERVAL}s"
        sleep "$RETRY_INTERVAL"
    fi
done
