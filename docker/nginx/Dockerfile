FROM nginx:stable-alpine

# Self-signed certificate for local HTTPS (generated at build time, never committed).
# In production TLS is terminated by a reverse proxy with a real certificate (see README, deploy).
RUN apk add --no-cache openssl \
    && mkdir -p /etc/nginx/certs \
    && openssl req -x509 -nodes -newkey rsa:2048 -days 825 \
        -keyout /etc/nginx/certs/localhost.key \
        -out /etc/nginx/certs/localhost.crt \
        -subj "/CN=localhost" \
        -addext "subjectAltName=DNS:localhost,IP:127.0.0.1" \
    && chmod 600 /etc/nginx/certs/localhost.key \
    && apk del openssl

# The official image renders *.template files with envsubst (only variables set in the environment).
COPY docker/nginx/nginx.conf /etc/nginx/templates/default.conf.template
WORKDIR /app
