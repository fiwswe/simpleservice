# simpleservice
PHP template to illustrate running an action more often than once per minute 

## Description
Dummycode written in PHP to perform an action in intervals that can be shorter than 60s. This code is meant to illustrate the important aspects of the basic structure. Many aspects are kept simple and very primitive on purpose.

Look for FIXME to see where your own modification should go.

## Background
Often PHP is used for scripting things that need to run at regular intervals.

On *NIX systems cron(8) is often used to trigger a script. However cron(8) only allows for a 1 minute (60 second) granularity when defining the interval. Somtimes an action needs to be performed more often or with fractional second granularity, e.g. every 1.5s.

## Limitations
This code is not meant to be a daemon or a service (in the *NIX sense). It stays connected to its starting terminal. This script might be a starting point for developing a daemon though.

When this script is launched automatically at e.g. system start, it will run until it is interupted by a signal or until it encounters a fatal error. There are no provisions for finding the running process or restarting it after a failure.

PHP is a fairly heavy process to launch. OTOH the language is very flexible and allows good control of error handling, less obscure (IMHO) than e.g. shell scripts or perl. But using PHP is definitively a personal preference not everyone may agree with. If you don't then this code is not for you. Though the general flow of the code may be applicable to other languages as well.

## Example
```
$ time php-8.2 ./simpleservice.php;echo "Status: $?" 
2023-05-08T23:02:32.411+00:00 Start.
2023-05-08T23:02:32.411+00:00 Action!
2023-05-08T23:02:42.424+00:00 Action!
2023-05-08T23:02:52.424+00:00 Action!
2023-05-08T23:03:02.424+00:00 Action!
2023-05-08T23:03:12.424+00:00 Action!
2023-05-08T23:03:22.424+00:00 Action!
2023-05-08T23:03:32.424+00:00 Action!
2023-05-08T23:03:42.424+00:00 Action!
2023-05-08T23:03:52.425+00:00 Action!
^C2023-05-08T23:03:55.484+00:00 Cleaning up!
2023-05-08T23:03:55.484+00:00 Done.
    1m23.20s real     0m00.06s user     0m00.07s system
Status: 2
$ 
```
* Almost drift free execution of Action.
* Efficient use of CPU as evidenced by very low user and system times compared to real time.
* Almost immediate termination with `^C`.

## Requirements
* [PHP](https://www.php.net) (version ≥7.4, lower versions may also work)
* PHP extension [pcntl](https://www.php.net/manual/en/book.pcntl)

This code was tested with PHP 8.2.5, 8.1.18, 8.0.28 and 7.4.33 on [OpenBSD 7.3](https://openbsd.org/73.html).
