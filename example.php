<?php
/**
 * File: example.php
 * Project: freshdesk-solutions
 */
require 'vendor/autoload.php';
//use the classes
use Freshdesk\Config\Connection,
    Freshdesk\Rest,
    Freshdesk\Ticket,
    Freshdesk\Model\Contact,
    Freshdesk\Model\Ticket as TicketM,
    Freshdesk\Tool\ModelGenerator;
$url = 'http://API-key:X@domain.freshdesk.com';

$conf = new Connection($url);
$t = new Ticket(
    $conf
);
$m = new Contact(
    array(
        'email' => 'foo@bar.com'
    )
);
//get an assoc array of tickets
//keys are statusName values
$tickets = $t->getGroupedTickets($m);
//same as before, status values (1,2,3...) are the keys now
$tickets = $t->getGroupedTickets('foobar@zar.com', false);
//choose a ticket
$model = new TicketM(
    array(
        'display_id'    => 12345
    )
);
$t = new Ticket($conf);
//get all data associated with this id
$model = $t->getFullTicket($model);
//close a ticket
$ticket = $t->updateTicket(
    $model->setStatus(4)
);




//fire up the generator
$gen = new ModelGenerator($conf);
//generate class, extending from the TicketM class
//will create properties, setters and getters for all
//properties not present in base class
echo $gen->generateTicketClass(
    $model,
    'YourTicket',
    '/home/user/abs/path/to/YourTicket.php'
);

//basic/general rest calls
$fd = new Rest($conf);
//get ticket, this call will be removed from Rest class & moved to Ticket class
$json = $fd->getSingleTicket(1701);
print_r($json);
//for ticket-calls:
$t = new Ticket($conf);
//create new ticket
$model = new TicketM(
    array(
        'description'   => 'Ignore this ticket, it is a test',
        'subject'       => 'API-test',
        'email'         => 'foo@bar.com'
    )
);

//create new ticket, basic example
$t->createNewTicket($model);
//Assign a ticket to an agent/responder:
$responderId = 123456;
$t->assignTicket($model, $responderId);
//delete a ticket:
$t->deleteTicket($model);//pass true as second argument to force a reload of the ticket
//restore a ticket that was deleted via the api:
$t->restoreTicket($model);
