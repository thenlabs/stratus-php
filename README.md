
# StratusPHP

StratusPHP is a framework for creating PHP applications that have reactive interfaces using the paradigm of event-driven programming between the browser and the server.

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

## Contribute.

This project is developed from Cuba by independent programmers.

Unfortunately for us, the cubans programmers we have seriously limitations for receive monetary donations for our open source projects and, in our case, ONLY we have the next alternatives:

- [Donation of 6 euros through Tropipay](https://www.tropipay.com/money-request/05988f30-5dda-11eb-9924-13eacace0c71)
- [Donation of 12 euros through Tropipay](https://www.tropipay.com/money-request/28cd29c0-5dda-11eb-9924-13eacace0c71)
- [Donation of 24 euros through Tropipay](https://www.tropipay.com/money-request/4c02aeb0-5dda-11eb-9924-13eacace0c71)

Thank you very much!.
