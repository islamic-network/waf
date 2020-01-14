FROM islamicnetwork/php73:latest

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
ENV PROXY_URL "https://some.url"
ENV WAF_CONFIG_URL "http://vesica.ws/waf.yml"
ENV WAF_PROXY_NAMESPACE "SomeApiWaf"
ENV WAF_NAME "VESICA-WAF"
ENV WAF_KEY "someKeyHere"

ENV LOG_LEVEL "DEBUG"

# Load Balancer
ENV LOAD_BALANCER_KEY "LB_KEY"
# 0 = false, 1 = true
ENV LOAD_BALANCER_MODE "0"
