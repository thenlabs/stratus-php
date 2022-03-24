
# StratusPHP

StratusPHP is a framework for creating PHP applications that have reactive interfaces using the paradigm of event-driven programming between the browser and the server.

>If you like this project gift us a ⭐.

## Documentation.

- [English](https://github.com/thenlabs/doc/blob/master/stratus-php/master/en/index.md)
- [Español](https://github.com/thenlabs/doc/blob/master/stratus-php/master/es/index.md)

## Running the tests.

Before start the tests it's necesary start the web server with the next command:

    $ php -S localhost:8000 -t tests/public

Then Selenium Server must be started as follows:

    $ java -jar /path/to/selenium-server-standalone-<x.y.z>.jar

Change `<x.y.z>` for respective value.

Once the previous steps have been carried out, it will be possible to run the tests with the following command:

    $ ./vendor/bin/phpunit
