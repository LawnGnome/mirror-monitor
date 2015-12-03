# Mirror Monitor

An incredibly basic tool to check how many mirrors a string has appeared on.
Useful when you're waiting for, say, the PHP 7 release to occur.

## Requirements

This needs PHP 7, because PHPception.

## Installation

```bash
composer install
```

## Basic usage

```bash
./bin/monitor-mirrors monitor -s PHP -u mirrors.txt
```

Replace "PHP" with the text you're interested in. The -u parameter is the name
of a file with a set of whitespace-separated URLs.

By default, checks will occur every 120 seconds, and will time out after 60
seconds. You can adjust those with the -e and -t options, respectively.

You can get a list of mirrors that failed to meet the expectations by using -v
to increase the verbosity of the command.

## Testing

I should probably write some tests at some point. In my defence, I woke up at 5
am to wait for the PHP 7 release, and it's still only 6:20.
