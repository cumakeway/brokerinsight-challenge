from php:8.2-apache
RUN apt-get update
RUN apt-get install sudo
RUN apt-get install -y git
RUN docker-php-ext-install pdo pdo_mysql 
RUN sudo apt-get install -y mariadb-server
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer
RUN a2enmod rewrite
CMD ["/bin/bash"]