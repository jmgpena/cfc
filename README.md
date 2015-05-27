[![Build Status](https://travis-ci.org/jmgpena/cfc.svg)](https://travis-ci.org/jmgpena/cfc)

# Project description

Message processing framework using:

- Nginx
- Beanstalkd
- Supervisor (monitoring and starting scripts)
- PHP 5.5 (using FastCGI Process Manager)
- Silex
- Ratchet (PHP Async Server Library - Websocket Server)
- Pheanstalk (client library for beanstalkd)
- Websocket (PHP library for connecting to websocket server)

This project was fun to work on and I ended up learning new stuff on the way. I could also have used node.js for the various components and probably would have been easier because of the asynchronous architecture already in place.

# Architecture

I use nginx as the webserver and proxy for the websocket server. Also
implemented rate limiting for the API in there (50 msg/s). My server could
handle more easily (tested at 100 msg/s) but this seems a reasonable default.

The first component is a Silex application that handles the api endpoint and
writes json messages to the beanstalkd queue. For the scope of this project
probably using Silex was a bit overkill and we could improve the performance by
using something much simpler. Initially I thought Silex could prove useful in
organizing and struturing the app but for such a small task we don't get those
benefits.

The next component is the message processor (bin/message-processor.php) an it
is a very simple script that reads messages from the queue and posts them to a
running websocket server process. The queue reader will block and wait when the
queue is empty. As for the pushing of the messages to the websocket server it
expects a response after each message and if it does not get a successfull
response it wil release the current message on the queue and try again in 1
second. Given that both the websocket server and the beanstalkd queue can
handle multiple connections we could run more than one of this processe in
parallel (useful if we did more costly processing of the messages). I use
supervisord for managing the process and that gives flexibility to handle
automatic restarts and pools of processes without having more complex code.

The websocket server (bin/ws-server.php) is also handled by supervisord and is
a basic server that can handle connections from workers (message processors)
and clients (browser). It will accept messages from workers (clients running in
this machine) and broadcast them to all connected clients. If there are no
available clients it will return a 'WAIT' code to the worker that will wait and
retry the message later.

The browser client is just a html page with some simple javascript that
connects to the websocket server and displays messages on the page. Since the
connection will close after some time by default I added some code to reconnect
automatically after a disconnection.

# Deployment

The project is deployed automatically to my VPS via travis-ci. The url for the
frontend is (http://cfc.jmgpena.net), and the api endpoint for messages is
(http://cfc.jmgpena.net/api/message).

I used a postman and loadtest to test the api. To run a basic test open the
frontend on a web browser and run the following command or post manually
messages to the api endpoint.

```
loadtest http://cfc.jmgpena.net/api/message -P '{"userId": "134259","currencyFrom": " EUR","currencyTo": "GBP","amountSell": 1000,"amountBuy": 747.10,"rate": 0.7471,"timePla ced" : "24-JAN-15 10:27:44","originatingCountry" : "FR"}' -c 10 --rps 50 -n 100
```

This will post 100 messages to the server that should display on the browser almost instantly.

# Benefits

This architecture using several distributed and loosely coupled components
gives us a lot of flexibility. I can choose to replace any of the components
without having to change the whole thing. If I wanted to have a module to
gather statistics about the messages or save them to a database I could add it
at several point in the flow without changing any of the other ones.

# Missing Stuff

- Tests (PHPUnit is in place but no tests created)
- Message validation
- Nicer message display on frontend
