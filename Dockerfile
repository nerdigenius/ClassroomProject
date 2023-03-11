FROM php:7.4.2-apache
COPY . /
WORKDIR /classroomproject
CMD [ "php", "./index.php" ]