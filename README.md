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
Add this repo to your composer.json file to use this repository as a direct dependency.

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/EVODelavega/freshdesk-rest.git"
        }
    ],
    "require": {
        "EVODelavega/freshdesk-rest": "dev-master"
    }
}
```
This package has been added to packagist, too. Packagist only accepts lower-case package names, so simply adding the following will work:

```json
{
    "require": {
        "evodelavega/freshdesk-rest": "dev-master"
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

## Notes
This code follows the coding standards as layed out by the PHP-FIG, with some Symfony2 style sprinkled on top.
Exceptions' messages are returned by the `sprintf` and `vsprintf` functions, for example.

I have, however, granted myself one guilty pleasure: The entire code-base uses Allman-style indentation.

##Contributing
Any contributions are, of course, greatly appreciated. If you wish to contribute, a couple of things to keep in mind

1. Adding support for certain API calls that are currently missing is done by adding the corresponding method to the appropriate _child_ class of `Freshdesk\Rest`
2. The Coding standards used throughout this code should be respected. Pull requests that do not follow the standards will not be merged
3. Reporting an issue without a patch or PR is fine, and welcomed, but be complete in your description of the problem and/or suggested solutions
4. Document your code as much as possible. The code we have ATM needs some more documentation as it is. Adding code that is un-documented will only increase the problem...
5. Use the data models (`Freshdesk\Model` namespace) wherever you can. The goal of this API is to offer a _clear & safe_ interface, type-hints are a vital part of this.

As an illustration: initially, deleting and assigning tickets was not supported by this wrapper. Users had to either extend the `Freshdesk\Ticket` class themselves, or create a new child of `Freshdesk\Rest`, and write their own methods. This issue has been addressed as follows:

- Create a `feature/delete-ticket` branch
- Add methods to `Freshdesk\Ticket`, as deleting, restoring and assigning tickets is clearly a matter for the _Ticket_ API class
- Modify the `Freshdesk\Model\Ticket` class accordingly (adding `responderId` and `deleted` properties, each with their getter and setter methods)
- Update the example.php file to demonstrate the usage for the new methods
- Test the code (once the `feature/tests` branch is merged, provide unit-tests)
- commit & merge

The reasons why changes like this _have_ to go in the appropriate child of `Freshdesk\Rest` are twofold. Even though the methods need little else than a ticket id (and in the case of `assignTicket`, a responderId aswell), this approach forces users into using the data models. This, in turn, enables type-hinting for easier debugging and guaranteed value checking.
Another advantage is that objects are passed aroudn as references, which means that passing an object to a method means that all variables, regardless of scope, will reference the most up-to-date instance of the object, and therefore, the data is far more likely to be accurate.

A quick example for completeness, and in order to convince the sceptics:

```php
    public function someMethod()
	{
		$db = new PDO($dsn, $usr, $pass, $options);
		$stmt = $db->prepare('SELECT ticketId FROM helpdesk.tickets WHERE clientId = :cid AND status = :status');
		$stmt->execute([':cid' => 1, ':status' => \Freshdesk\Model\Ticket::STATUS_PENDING]);
		$row = $stmt->fetch(PDO::FETCH_OBJ);
		$ticket = new \Freshdesk\Model\Ticket(['displayId' => $row->ticketId]);//create instance
		//...more code
		$anotherObject->setTicket($ticket);
		//...some more calls, possibly in another method:
		$api = new \Freshdesk\Ticket(
			new \Freshdesk\Config\Connection(
				'https://<api-key>:X@<domain>'
			)
		);
		//complete the ticket via the API
		$api->getFullTicket($ticket);
		//...more code
		$anotherObject->getTicket();//<-- returns the updated ticket model
	}
```

In this case, the ticket instance is being used on various places. Because the entire API uses data models, we can save on expensive API calls, because each property and/or variable references a single instance.
