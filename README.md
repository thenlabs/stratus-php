
# StratusPHP

## Documentación.

- [Español](https://thenlabs.org/es/doc/stratus-php/master/index.html)
- [English](https://thenlabs.org/en/doc/stratus-php/master/index.html)

## Ejecutando las pruebas.

Antes de ejecutar las pruebas es necesario iniciar el servidor web con el siguiente comando:

    $ php -S localhost:8000 -t tests/public

Seguidamente se deberá iniciar Selenium Server de la siguiente manera:

    $ java -jar /path/to/selenium-server-standalone-<x.y.z>.jar

Sustituya `<x.y.z>` por su valor correspondiente.

Una vez realizados los pasos anteriores será posible ejecutar las pruebas con el siguiente comando:

    $ ./vendor/bin/phpunit

