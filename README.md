
# StratusPHP

PHP framework for creating single page applications using the event driven programming paradigm between browser and server.

## Documentation.

- [Español](https://thenlabs.org/es/doc/stratus-php/master/index.html)
- [English](https://thenlabs.org/en/doc/stratus-php/master/index.html)

## Running the tests.

Before start the tests it's necesary start the web server with the next command:

    $ php -S localhost:8000 -t tests/public

Then Selenium Server must be started as follows:

    $ java -jar /path/to/selenium-server-standalone-<x.y.z>.jar

Change `<x.y.z>` for respective value.

Once the previous steps have been carried out, it will be possible to run the tests with the following command:

    $ ./vendor/bin/phpunit

