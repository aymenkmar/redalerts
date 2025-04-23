FROM nexus.satoripop.io:8083/repository/php-images/php-8.3-nginx-node20:latest
LABEL maintainer="ridha.bennjima@satoripop.com"
USER www-data
COPY --chown=www-data . .

ENV COMPOSER_MEMORY_LIMIT=-1

USER root
#COPY NGINX CONF (LARAVEL)
RUN cp k8s/nginx.conf /etc/nginx/nginx.conf
RUN apk add --no-cache  freetype-dev libjpeg-turbo-dev libwebp-dev libpng-dev 
RUN apk add --no-cache php8.3-yaml
RUN docker-php-ext-configure gd --with-freetype --with-webp --with-jpeg && docker-php-ext-install gd 


#RUN cp k8s/queue-reverb.ini /etc/supervisor.d/queue-reverb.ini
#RUN cp k8s/default.ini /etc/supervisor.d/default.ini
USER www-data
#START TO CHANGE BY DEVELOPPER
RUN cp k8s/int/.env.k8s-int .env
RUN composer install
#RUN composer require symfony/yaml
#RUN composer require plan2net/webp

RUN php artisan migrate --force
#RUN php artisan db:seed --class=AdminPanelUserSeeder --force
#RUN php artisan db:seed --class=ShieldSeeder --force
#RUN npm install
#RUN npm run build
RUN php artisan storage:link
RUN php artisan optimize

#END TO CHANGE BY DEVELOPPER
RUN mkdir -p storage/app/kubeconfigs 
RUN chmod -R 777 storage
RUN chown -R www-data.www-data storage

USER root
RUN mkdir /tmpstorage
RUN cp -arv storage/* /tmpstorage/

EXPOSE 80
EXPOSE 8080