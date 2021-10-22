
# StratusPHP

StratusPHP is a framework for creating PHP applications that have reactive interfaces using the paradigm of event-driven programming between the browser and the server.

>If you like this project gift us a ⭐.

## Documentation.

- [English](https://thenlabs.org/en/doc/stratus-php/master/index.html)
- [Español](https://thenlabs.org/es/doc/stratus-php/master/index.html)

## Running the tests.

Before start the tests it's necesary start the web server with the next command:

    $ php -S localhost:8000 -t tests/public

Then Selenium Server must be started as follows:

    $ java -jar /path/to/selenium-server-standalone-<x.y.z>.jar

Change `<x.y.z>` for respective value.

Once the previous steps have been carried out, it will be possible to run the tests with the following command:

    $ ./vendor/bin/phpunit
