# FreshDesk.com PHP API Wrapper

The "composer-aware edition".

## Highlights

Composer, obviously, but apart from that, this feature branch offers:

- Namespace support
- An easily extendable "Rest" class
- Will become 100% Object Oriented (no more passing around tons of strings of data)
- Easy integration with MVC frameworks like Zend, Symfony2


## Ways To Improve
1. Still being work in progress: separation of concern is something that needs a lot of work
2. Example child classes in the Freshdesk namespace (ie Tickets, for all ticket-related API calls)
3. Better documentation
4. Out of the box support for filters, statusses and the like (through class-constants)
5. Unit-tests are a glaring omission ATM

## Usage
1. Add this repo to your composer.json file

```
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/EVODelavega/freshdesk-rest.git"
        }
    ],
    "require": {
        "EVODelavega/freshdesk-rest": "dev-feature/composer"
    }
}
```

2. Example usage (taken from the example.php file)

```
<?php
use Freshdesk\Config\Connection,
    Freshdesk\Rest;
$fd = new Rest(
    new Connection(
        'https://<user>:<password>@<domain>'
    )
);
$apiExample = new Rest(
    new Connection(
        'http://<API_KEY>:X@<domain>'
    )
);
```
