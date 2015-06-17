<?php

namespace Freshdesk\Model;

use \DateTime,
    \InvalidArgumentException;

class Ticket extends Base
{

    const RESPONSE_KEY = 'helpdesk_ticket';

    const SOURCE_EMAIL = 1;
    const SOURCE_PORTAL = 2;
    const SOURCE_PHONE = 3;
    const SOURCE_FORUM = 4;
    const SOURCE_TWITTER = 5;
    const SOURCE_FACEBOOK = 6;
    const SOURCE_CHAT = 7;

    const PRIORITY_LOW = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_HIGH = 3;
    const PRIORITY_URGENT = 4;

    const STATUS_ALL = 1;
    const STATUS_OPEN = 2;
    const STATUS_PENDING = 3;
    const STATUS_RESOLVED = 4;
    const STATUS_CLOSED = 5;

    const CC_EMAIL = '<your cc_email here>';

    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var int
     */
    protected $displayId = null;

    /**
     * @var bool
     */
    protected $deleted = false;

    /**
     * @var int
     */
    protected $requesterId = null;

    /**
     * @var int
     */
    protected $responderId = null;

    /**
     * @var string
     */
    protected $description = null;

    /**
     * @var string
     */
    protected $subject = null;

    /**
     * @var string
     */
    protected $email = null;

    /**
     * @var array<\Freshdesk\Model\Note>
     */
    protected $notes = array();

    /**
     * @var int
     */
    protected $priority = 1;

    /**
     * @var int
     */
    protected $status = 2;

    /**
     * @var string
     */
    protected $statusName = 'Open';

    /**
     * @var \DateTime
     */
    protected $createdAt = null;

    /**
     * @var string
     */
    protected $ccEmailVal = null;

    /**
     * @var array
     */
    protected $tags = array();

    /**
     * @var array<CustomField>
     */
    protected $customField = array();

    /**
     * @var array - add all setters that require a DateTime instsance as argument
     */
    protected $toDateTime = array(
        'setCreatedAt'
    );

    /**
     * @param string $desc
     * @return $this
     */
    public function setDescription($desc)
    {
        $this->description = (string) $desc;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $subj
     * @return $this
     */
    public function setSubject($subj)
    {
        $this->subject = $subj;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $email
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setEmail($email)
    {
        if (!filter_var($email, \FILTER_VALIDATE_EMAIL))
            throw new InvalidArgumentException(
                sprintf(
                    '%s is not a valid email address',
                    $email
                )
            );
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param int $p
     * @return $this
     */
    public function setPriority($p)
    {
        $this->priority = (int) $p;
        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = (int) $status;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $sName
     * @return $this
     */
    public function setStatusName($sName)
    {
        $this->statusName = $sName;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatusName()
    {
        return $this->statusName;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id === null ? null : (int) $id;
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
     * @param int $dId
     * @return $this
     */
    public function setDisplayId($dId)
    {
        $this->displayId = $dId === null ? null : (int) $dId;
        return $this;
    }

    /**
     * @return int
     */
    public function getDisplayId()
    {
        return $this->displayId;
    }

    /**
     * @param bool $deleted
     * @return $this
     */
    public function setDeleted($deleted)
    {
        $this->deleted = (bool) $deleted;
        return $this;
    }

    /**
     * @return bool
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param int $reqId
     * @return $this
     */
    public function setRequesterId($reqId)
    {
        $this->requesterId = (int) $reqId;
        return $this;
    }

    /**
     * @return int
     */
    public function getRequesterId()
    {
        return $this->requesterId;
    }

    /**
     * @param int $respId
     * @return $this
     */
    public function setResponderId($respId)
    {
        $this->responderId = (int) $respId;
        return $this;
    }

    /**
     * @return int
     */
    public function getResponderId()
    {
        return $this->responderId;
    }

    /**
     * @param array $notes
     * @return $this
     */
    public function setNotes(array $notes)
    {
        if (!empty($this->notes))
            $this->notes = array();
        foreach ($notes as $note)
            $this->notes[] = new Note($note);
        return $this;
    }

    /**
     * @return array
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Return notes that the requester added to ticket
     * @return array
     */
    public function getRequesterNotes()
    {
        $return = array();
        foreach ($this->notes as $note)
        {
            /** @var \Freshdesk\Model\Note $note */
            if ($note->getUserId() == $this->getRequesterId())
                $return[] = $note;
        }
        return $return;
    }

    /**
     * @param DateTime $d
     * @return $this
     */
    public function setCreatedAt(DateTime $d)
    {
        $this->createdAt = $d;
        return $this;
    }

    /**
     * @param bool $asString
     * @return DateTime|string|null
     */
    public function getCreatedAt($asString = true)
    {
        if (!$asString)
            return $this->createdAt;
        return ($this->createdAt === null ? '' : $this->createdAt->format('Y-m-d H:i:s'));
    }

    /**
     * Set multiple tags on the ticket
     *
     * @param mixed $tags Can be either an array, or a comma-delimited string of tags
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setTags($tags)
    {
        if (is_string($tags))
        {
            $tags = explode(',', $tags);
        }
        if (!is_array($tags))
        {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s expects argument to be a string, or an array, %s given',
                    __METHOD__,
                    is_object($tags) ? get_class($tags) : gettype($tags)
                )
            );
        }
        $this->tags = $tags;

        return $this;
    }

    /**
     * Add a single tag to the ticket
     *
     * @param string $tag A single tag to add
     * @return $this
     */
    public function addTag($tag)
    {
        if (is_string($tag))
        {
            $this->tags[] = $tag;
        }

        return $this;
    }

    /**
     * Retrieve any tags set on the ticket
     * @param bool $asString = true
     * @return string|array
     */
    public function getTags($asString = true)
    {
        if ($asString)
            return implode(',', $this->tags);
        return $this->tags;
    }

    /**
     * @param string $ccemail
     * @return $this
     */
    public function setCcEmailVal($ccemail)
    {
        $this->ccEmailVal = $ccemail === null ? null : (string) $ccemail;
        return $this;
    }

    /**
     * @return string
     */
    public function getCcEmailVal()
    {
        if ($this->ccEmailVal === null)
            return self::CC_EMAIL;
        return $this->ccEmailVal;
    }

    /**
     * @param mixed $mixed
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setCustomField($mixed)
    {
        if ($mixed instanceof \stdClass)
            $mixed = (array) $mixed;
        elseif ($mixed instanceof CustomField)
        {
            if (is_array($this->customField))
                return $this->addCustomField($mixed);
            $mixed = array($mixed);
        }
        if (!is_array($mixed))
            throw new InvalidArgumentException(
                sprintf(
                    '%s expects an array, stdClass instance or a CustomField model',
                    __METHOD__
                )
            );
        $this->customField = array();
        foreach ($mixed as $k => $v)
            $this->addCustomField($v, $k);
        return $this;
    }

    /**
     * @param null|string $name
     * @return \Freshdesk\Model\CustomField|null
     */
    public function getCustomField($name = null)
    {
        if ($name === null)
            return $this->customField;
        foreach ($this->customField as $k => $field)
        {
            if ($field->getName() == $name)
                return $field;
        }
        return null;
    }

    /**
     * @param string|\Freshdesk\Model\CustomField $mix
     * @param null|string|int $k
     * @return $this
     */
    public function addCustomField($mix, $k = null)
    {
        if ($mix instanceof CustomField)
            $this->customField[] = $mix;
        else
            $this->customField[] = new CustomField(
                array(
                    'name'  => $k,
                    'value' => $mix,
                    'ticket'=> $this
                )
            );
        return $this;
    }

    public function getCustomFields()
    {
        return $this->customField;
    }

    /**
     * Get the json-string for this ticket instance
     * Ready-made to create a new freshdesk ticket
     * @return string
     */
    public function toJsonData()
    {
        $data = array(
            self::RESPONSE_KEY => array(
                'description'   => $this->description,
                'subject'       => $this->subject,
                'email'         => $this->email,
                'priority'      => $this->priority,
                'status'        => $this->status
            ),
            'cc_emails' => $this->getCcEmailVal()
        );

        $custom = array();
        $customFields = $this->getCustomFields();
        /** @var \Freshdesk\Model\CustomField $f */
        foreach ($customFields as $f)
        {
            $custom[$f->getName(true)] = $f->getValue();
        }

        if (!empty($custom))
        {
            $data[self::RESPONSE_KEY]['custom_field'] = $custom;
        }

        $tags = $this->getTags();
        if (!empty($tags))
        {
            $data['helpdesk']['tags'] = $tags;
        }

        return json_encode($data);
    }
}
