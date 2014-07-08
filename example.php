<?php
/**
 * File: example.php
 * Project: freshdesk-solutions
 */
//Require the FreshDesk API Library
//autoloaded, in real-life, of course
require 'src/Freshdesk/Config/Connection.php';
require 'src/Freshdesk/Rest.php';
require 'src/Freshdesk/Ticket.php';
require 'src/Freshdesk/Model/Base.php';
require 'src/Freshdesk/Model/Ticket.php';
require 'src/Freshdesk/Tool/ModelGenerator.php';
//use the classes
use Freshdesk\Config\Connection,
    Freshdesk\Rest,
    Freshdesk\Ticket,
    Freshdesk\Model\Ticket as TicketM,
    Freshdesk\Tool\ModelGenerator;
$url = 'http://API-key:X@domain.freshdesk.com';

$conf = new Connection($url);
//choose a ticket
$model = new TicketM(
    array(
        'display_id'    => 12345
    )
);
//fire up the generator
$gen = new ModelGenerator($conf);
//generate class, extending from the TicketM class
//will create properties, setters and getters for all
//properties not present in base class
echo $gen->generateTicketClass(
    $model,
    'FullTicket'
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
