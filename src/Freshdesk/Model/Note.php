<?php

namespace Freshdesk\Model;
use \DateTime,
    Freshdesk\Model\Ticket,
    \stdClass,
    \Traversable;

class Note
{
    /**
     * @var \Freshdesk\Model\Ticket
     */
    protected $ticket = null;

    /**
     * @var string
     */
    protected $body = null;

    /**
     * @var bool
     */
    protected $private = false;

    /**
     * @var string
     */
    protected $bodyHtml = null;

    /**
     * @var \DateTime
     */
    protected $createdAt = null;

    /**
     * @var bool
     */
    protected $deleted = false;

    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var bool
     */
    protected $incoming = false;

    /**
     * @var int
     */
    protected $source = 0;

    /**
     * @var \DateTime
     */
    protected $updatedAt = null;

    /**
     * @var int
     */
    protected $userId = null;

    /**
     * @var array
     */
    protected $attachments = null;

    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $bodyHtml
     * @return $this
     */
    public function setBodyHtml($bodyHtml)
    {
        $this->bodyHtml = $bodyHtml;
        return $this;
    }

    /**
     * @return string
     */
    public function getBodyHtml()
    {
        return $this->bodyHtml;
    }

    /**
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param boolean $deleted
     * @return $this
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param boolean $incoming
     * @return $this
     */
    public function setIncoming($incoming)
    {
        $this->incoming = $incoming;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIncoming()
    {
        return $this->incoming;
    }

    /**
     * @param boolean $private
     * @return $this
     */
    public function setPrivate($private)
    {
        $this->private = $private;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getPrivate()
    {
        return $this->private;
    }

    /**
     * @param int $source
     * @return $this
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return int
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param \Freshdesk\Model\Ticket $ticket
     * @return $this
     */
    public function setTicket(Ticket $ticket)
    {
        $this->ticket = $ticket;
        return $this;
    }

    /**
     * @return \Freshdesk\Model\Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @param array $attachments
     * @return $this
     */
    public function setAttachments(array $attachments)
    {
        $this->attachments = $attachments;
        return $this;
    }

    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param \DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt(DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param int $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Get the json-string for this ticket instance
     * Ready-made to create a new freshdesk ticket
     * @return string
     */
    public function toJsonData()
    {
        return json_encode(
            array(
                'helpdesk_note'   => array(
                    'body'      => $this->body,
                    'private'   => $this->private
                )
            )
        );
    }

} 