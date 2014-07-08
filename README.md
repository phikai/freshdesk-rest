# FreshDesk.com PHP API Wrapper

The "composer-aware edition".

## Highlights

Composer, obviously, but apart from that, this feature branch offers:

- Namespace support
- A "Rest" class, that can be extended easily
- Will become 100% Object Oriented (no more passing around tons of strings of data)
- Easy integration with MVC frameworks like Zend, Symfony2


## Ways To Improve
1. Still being work in progress: separation of concern is something that needs a lot of work
2. Example child classes in the Freshdesk namespace (ie Tickets, for all ticket-related API calls)
3. Better documentation
4. Out of the box support for filters, statuses and the like (through class-constants)
5. Unit-tests are a glaring omission ATM
6. Possibly add some data-models for tickets, customers, users and the like...

## Usage
Add this repo to your composer.json file

```json
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
If you are going to use this wrapper to create new tickets in freshdesk, there is a postUpdate/postInstall script you can use to set the cc-email constant in the Freshdesk\Model\Ticket class. To automatically configure this constant, add the following to your _scripts_ section in the composer.json file:

```json
"scripts": {
    "post-install-cmd": [
        "Freshdesk\\Config\\Composer::postUpdate"
    ],
    "post-update-cmd": [
        "Freshdesk\\Config\\Composer::postUpdate"
    ]
}
```
Example usage (taken from the example.php file)

```php
<?php
use Freshdesk\Config\Connection,
    Freshdesk\Rest;// for backwards compatibility with existing code, use alias: use Freshdesk\Rest as FreshdeskRest;
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
