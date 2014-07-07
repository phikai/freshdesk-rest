<?php
namespace Freshdesk;

use Freshdesk\Model\Ticket as TicketM,
    \InvalidArgumentException,
    \RuntimeException;

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


    /**
     * Returns formatted url
     * @param string $email
     * @param string $filter = self::FILTER_ALL
     * @return string
     */
    protected function getTicketUrl($email, $filter = self::FILTER_ALL)
    {
        return sprintf(
            '/helpdesk/tickets.json?email=%s&filter_name=%s',
            $email,
            $filter
        );
    }

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
            $this->getTicketUrl($email),
            self::METHOD_GET
        );
        if (!$json)
            return null;
        return json_decode($json);
    }

    /**
     * @param $email
     * @param int $status
     * @return array|null
     */
    public function getTicketIds($email, $status = TicketM::STATUS_ALL)
    {
        $tickets = $this->getTicketsByEmail($email);
        if (!$tickets)
            return null;
        $return = array();
        for ($i=0, $j=count($tickets);$i<$j;++$i)
        {
            if ($status === TicketM::STATUS_ALL || $tickets[$i]->status == $status)
                $return[] = $tickets[$i]->display_id;
        }
        return $return;
    }

    /**
     * Get open tickets for $email
     * @param string $email
     * @return null|\stdClass|array
     * @throws \InvalidArgumentException
     */
    public function getOpenTickets($email)
    {
        if (!filter_var($email, \FILTER_VALIDATE_EMAIL))
            throw new InvalidArgumentException(
                sprintf(
                    '%s is not a valid email address',
                    $email
                )
            );
        $json = $this->restCall(
            $this->getTicketUrl(
                $email,
                self::FILTER_OPEN
            ),
            self::METHOD_GET
        );
        if (!$json)
            return null;
        return json_decode(
            $json
        );
    }

    /**
     * Get tickets that are neither closed or resolved
     * @param string $email
     * @return null|array
     */
    public function getActiveTickets($email)
    {
        $tickets = $this->getTicketsByEmail($email);
        if (!$tickets)
            return null;
        $return = array();
        for ($i=0, $j=count($tickets);$i<$j;++$i)
        {
            if ($tickets[$i]->status < TicketM::STATUS_RESOLVED)
                $return[] = $tickets[$i];
        }
        return $return;
    }

    /**
     * @param string $email
     * @return array<\stdClass>
     */
    public function getResolvedTickets($email)
    {
        $tickets = $this->getTicketsByEmail($email);
        $return = array();
        for ($i=0, $j=count($tickets);$i<$j;++$i)
        {
            if ($tickets[$i]->status === TicketM::STATUS_RESOLVED)
                $return[] = $tickets[$i]->display_id;
        }
        return $return;
    }

    /**
     * Create new ticket, returns model after setting createdAt property
     * @param TicketM $ticket
     * @return \Freshdesk\Model\Ticket
     * @throws \RuntimeException
     */
    public function createNewTicket(TicketM $ticket)
    {
        $data = $ticket->toJsonData();
        $response = $this->restCall(
            '/helpdesk/tickets.json',
            self::METHOD_POST,
            $data
        );
        if (!$response)
            throw new RuntimeException(
                sprintf(
                    'Failed to create ticket with data: %s',
                    $data
                )
            );
        $json = json_decode(
            $response
        );
        if (property_exists($json, 'created_at'))
            $ticket->setCreatedAt(
                new \DateTime(
                    $json->created_at
                )
            );
        return $ticket;
    }

}
