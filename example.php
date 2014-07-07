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
require 'src/Freshdesk/Model/Ticket.php';
//use the classes
use Freshdesk\Config\Connection,
    Freshdesk\Rest,
    Freshdesk\Ticket,
    Freshdesk\Model\Ticket as TicketM;
$url = 'http://API-key:X@domain.freshdesk.com';

$conf = new Connection($url);
$fd = new Rest($conf);
$t = new Ticket($conf);
$model = new TicketM(
    array(
        'description'   => 'Ignore this ticket, it is a test',
        'subject'       => 'API-test',
        'email'         => 'foo@bar.com'
    )
);

//create new ticket, basic example
$t->createNewTicket($model);

//Create New FreshDesk API Object
//$fd = new FreshdeskRest(FD_URL, FD_API_USER, FD_API_PASS);

$json = $fd->getSingleTicket(1701);

//$json = $fd->getTicketSurvey(31701);

print_r($json);
