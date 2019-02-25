FROM vesica/php72:latest

RUN a2enmod proxy && \
  a2enmod proxy_http && \
  a2enmod proxy_ajp && \
  a2enmod rewrite && \
  a2enmod deflate && \
  a2enmod headers && \
  a2enmod proxy_balancer && \
  a2enmod proxy_connect && \
  a2enmod ssl && \
  a2enmod cache

COPY /etc/apache2/mods-enabled/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

RUN cd ../ && rm -rf /var/www/html
COPY . /var/www/

# Run Composer
RUN cd /var/www && composer install --no-dev

RUN chown -R www-data:www-data /var/www/

## MEMCACHED
ENV MEMCACHED_HOST "proxy-memcached"
ENV MEMCACHED_PORT "11211"

# PROXY BASE URL WITHOUT trailing slash
ENV PROXY_URL "https://api.aladhan.com"
ENV WAF_CONFIG_URL "http://vesica.ws/waf.yml"
ENV WAF_CONFIG_EXPIRY "300"
ENV WAF_PROXY_NAMESPACE "SomeApiWaf"
ENV WAF_NAME "VESICA-WAF"
ENV WAF_KEY "someKeyHere"

ENV LOG_LEVEL "DEBUG"