# Let's Encrypt client (lego) with the DuckDNS DNS-01 provider; see docker/acme/run.sh.
FROM goacme/lego:v4.21.0

COPY docker/acme/run.sh /usr/local/bin/acme-run.sh
# Strip CRLF (Windows checkouts) so the shebang line works.
RUN sed -i 's/\r$//' /usr/local/bin/acme-run.sh \
    && chmod +x /usr/local/bin/acme-run.sh

ENTRYPOINT ["/usr/local/bin/acme-run.sh"]
