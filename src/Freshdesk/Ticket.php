<?php
namespace Freshdesk;

use Freshdesk\Model\Ticket as TicketM,
    Freshdesk\Model\Note,
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
     * Get tickets in view, specify page, defaults to 0 === get all pages
     * @param int $viewId
     * @param int $page = 0
     * @return array
     */
    public function getTicketsByView($viewId, $page = 0)
    {
        if ($page === 0)
        {
            $data = array();
            $current = 1;
            while ($tickets = $this->getTicketsByView($viewId, $current))
                $data[$current++] = $tickets;
            return $data;
        }
        $request = sprintf(
            '/helpdesk/tickets/view/%d?format=json&page=%d',
            (int) $viewId,
            (int) $page
        );
        return json_decode(
            $this->restCall(
                $request,
                self::METHOD_GET
            )
        );
    }

    /**
     * @param int $id
     * @param TicketM $model = null
     * @return TicketM
     * @throws \RuntimeException
     */
    public function getTicketById($id, TicketM $model = null)
    {
        $ticket = json_decode(
            $this->restCall(
                sprintf(
                    '/helpdesk/tickets/%s.json',
                    (int) $id
                ),
                self::METHOD_GET
            )
        );
        if (property_exists($ticket, 'errors'))
            throw new RuntimeException(
                sprintf(
                    'Ticket %d not found: %s',
                    $id,
                    $ticket->errors->error
                )
            );
        if ($model)
            return $model->setAll(
                $ticket->helpdesk_ticket
            );
        return new TicketM($ticket->helpdesk_ticket);
    }

    /**
     * get "pure" json data
     * @param TicketM $model
     * @return \stdClass
     */
    public function getRawTicket(TicketM $model)
    {
        return json_decode(
            $this->restCall(
                sprintf(
                    '/helpdesk/tickets/%s.json',
                    $model->getDisplayId()
                ),
                self::METHOD_GET
            )
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
     * Set displayId on model, pass to this function to auto-complete
     * @param TicketM $ticket
     * @return TicketM
     */
    public function getFullTicket(TicketM $ticket)
    {
        $response = json_decode(
            $this->restCall(
                sprintf(
                    '/helpdesk/tickets/%d.json',
                    $ticket->getDisplayId()
                ),
                self::METHOD_GET
            )
        );
        return $ticket->setAll($response);
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
        //update ticket model, set ids and created timestamp
        return $ticket->setAll(
            $json->helpdesk_ticket
        );
    }

    /**
     * Update the ticket
     * @param TicketM $ticket
     * @return $this
     */
    public function updateTicket(TicketM $ticket)
    {
        $url = sprintf(
            '/helpdesk/tickets/%d.json',
            $ticket->getDisplayId()
        );
        $data = $ticket->toJsonData();
        $response = json_decode(
            $this->restCall(
                $url,
                self::METHOD_PUT,
                $data
            )
        );
        return $ticket->setAll(
            $response->ticket
        );
    }

    /**
     * Add note to ticket, ticket model is expected to be set on Note model
     * @param Note $note
     * @return Note
     * @throws \RuntimeException
     */
    public function addNoteToTicket(Note $note)
    {
        $url = sprintf(
            '/helpdesk/tickets/%d/conversations/note.json',
            $note->getTicket()
                ->getDisplayId()
        );
        $response = json_decode(
            $this->restCall(
                $url,
                self::METHOD_POST,
                $note->toJsonData()
            )
        );
        if (!property_exists($response, 'note'))
            throw new RuntimeException(
                sprintf(
                    'Failed to add note: %s',
                    json_encode($response)
                )
            );
        //todo set properties on Note instance
        return $note;
    }
}
