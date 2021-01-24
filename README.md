
# StratusPHP

StratusPHP is a framework for creating PHP applications that have reactive interfaces using the paradigm of event-driven programming between the browser and the server.

Besides this, StratusPHP can be interpreted in many other ways. For example, it could also be said that it constitutes a platform that integrates the frontend and the backend which allows users to focus on more properly implementing the logic of their applications since it avoids having to implement communication between both environments either with apis, ajax calls, etc.

StratusPHP will take care of communicating data and events which will allow the user to have to write less JavaScript code since it will allow PHP to be used to manipulate the DOM of the page. The main advantage this offers is that it can just save a lot of work since the validations are implemented on the server only.

In order to teach you how to use StratusPHP and quickly show all its possibilities in action, we have prepared a series of practical examples that you can find at the following link.

[See examples](doc/examples/index.md)

If you want start to use StratusPHP use the next guide:

[How to use StratusPHP?](doc/how-to-use.md)

## Running the tests.

Before start the tests it's necesary start the web server with the next command:

    $ php -S localhost:8000 -t tests/public

Then Selenium Server must be started as follows:

    $ java -jar /path/to/selenium-server-standalone-<x.y.z>.jar

Change `<x.y.z>` for respective value.

Once the previous steps have been carried out, it will be possible to run the tests with the following command:

    $ ./vendor/bin/phpunit

