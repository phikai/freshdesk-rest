<?php
namespace Freshdesk;

use \InvalidArgumentException;

class Ticket extends Rest
{

    const FILTER_ALL = 'all_tickets';
    const FILTER_OPEN = 'open';
    const FILTER_HOLD = 'on_hold';
    const FILTER_OVERDUE = 'overdue';
    const FILTER_TODAY = 'due_today';
    const FILTER_NEW = 'new';
    const FILTER_SPAM = 'spam';
    const FILTER_DELETED = 'deleted';

    const STATUS_ALL = 1;
    const STATUS_OPEN = 2;
    const STATUS_PENDING = 3;
    const STATUS_RESOLVED = 4;
    const STATUS_CLOSED = 5;
    /**
     * Get all tickets from user (based on email)
     * @param string $email
     * @return null|\stdClass|array
     * @throws \InvalidArgumentException
     */
    public function getTicketsByEmail($email)
    {
        if (!filter_var($email,\FILTER_VALIDATE_EMAIL))
            throw new InvalidArgumentException(
                sprintf(
                    '%s is not a valid email address',
                    $email
                )
            );
        $json = $this->restCall(
            '/helpdesk/tickets.json?email='.$email.'&filter_name=all_tickets',
            self::METHOD_GET
        );
        if (!$json)
            return null;
        return json_decode($json);
    }

    public function getTicketIds($email, $status = self::STATUS_ALL)
    {
        $tickets = $this->getTicketsByEmail($email);
        if (!$tickets)
            return null;
        $return = array();
        for ($i=0, $j=count($tickets);$i<$j;++$i)
        {
            if ($status === self::STATUS_ALL || $tickets[$i]->status == $status)
                $return[] = $tickets[$i]->display_id;
        }
        return $return;
    }

    public function getActiveTickets($email)
    {
        $tickets = $this->getTicketsByEmail($email);
        if (!$tickets)
            return array();
        $return = array();
        for ($i=0, $j=count($tickets);$i<$j;++$i)
        {
            if ($tickets[$i]->status < self::STATUS_RESOLVED)
                $return[] = $tickets[$i];
        }
        return $return;
    }

    public function getResolvedTickets($email)
    {
        //
    }

}
