#!/bin/sh
# Production nginx entrypoint: makes sure a TLS certificate exists before nginx starts and
# picks up renewed certificates from the shared `certs` volume (written by the acme service).
set -eu

SRC=/certs                                  # acme service output (read-only mount)
DST=/etc/nginx/tls                          # what nginx.prod.conf reads
INTERVAL="${TLS_SYNC_INTERVAL:-300}"        # seconds between checks for a new certificate

log() { echo "[tls] $*"; }

# Copies the certificate pair from the volume when it is complete, valid and different from
# the one in use. Returns 0 only when something new was installed.
install_from_volume() {
    [ -s "$SRC/fullchain.pem" ] && [ -s "$SRC/privkey.pem" ] || return 1
    if cmp -s "$SRC/fullchain.pem" "$DST/fullchain.pem" && cmp -s "$SRC/privkey.pem" "$DST/privkey.pem"; then
        return 1
    fi
    # The two files are replaced separately by the acme service; install only a matching pair.
    cert_key=$(openssl x509 -noout -pubkey -in "$SRC/fullchain.pem" 2>/dev/null) || return 1
    priv_key=$(openssl pkey -pubout -in "$SRC/privkey.pem" 2>/dev/null) || return 1
    [ "$cert_key" = "$priv_key" ] || { log "certificate and key do not match yet, waiting"; return 1; }

    cp "$SRC/fullchain.pem" "$DST/fullchain.pem.new"
    cp "$SRC/privkey.pem" "$DST/privkey.pem.new"
    chmod 600 "$DST/privkey.pem.new"
    mv "$DST/privkey.pem.new" "$DST/privkey.pem"
    mv "$DST/fullchain.pem.new" "$DST/fullchain.pem"
    log "installed certificate: $(openssl x509 -noout -subject -enddate -in "$DST/fullchain.pem" | tr '\n' ' ')"
    return 0
}

mkdir -p "$DST"
if ! install_from_volume && [ ! -s "$DST/fullchain.pem" ]; then
    log "no certificate in $SRC yet, generating a temporary self-signed one"
    openssl req -x509 -nodes -newkey rsa:2048 -days 30 -subj "/CN=apexsteward-temporary" \
        -keyout "$DST/privkey.pem" -out "$DST/fullchain.pem" 2>/dev/null
    chmod 600 "$DST/privkey.pem"
fi

# Background watcher: reload nginx (graceful, no dropped connections) after a new certificate.
(
    while sleep "$INTERVAL"; do
        if install_from_volume; then
            if nginx -t -q; then
                nginx -s reload && log "nginx reloaded"
            else
                log "nginx -t failed, keeping the running configuration"
            fi
        fi
    done
) &

exec /docker-entrypoint.sh "$@"
