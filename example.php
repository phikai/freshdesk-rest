<?php
/**
 * File: example.php
 * Project: freshdesk-solutions
 */
//Require the FreshDesk API Library
//autoloaded, in real-life, of course
require 'src/Config/Connection.php';
require 'src/Rest.php';
//use the classes
use Freshdesk\Config\Connection,
    Freshdesk\Rest;

$url = 'http://API-key:X@domain.freshdesk.com';
$conf = new Connection($url);
$fd = new Rest($conf);


//Create New FreshDesk API Object
//$fd = new FreshdeskRest(FD_URL, FD_API_USER, FD_API_PASS);

$json = $fd->getSingleTicket(1701);

//$json = $fd->getTicketSurvey(31701);

print_r($json);
