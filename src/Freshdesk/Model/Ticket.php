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

    const PRRIORITY_LOW = 1;
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
     * @var int
     */
    protected $priority = 1;

    /**
     * @var int
     */
    protected $status = 2;

    /**
     * @var \DateTime
     */
    protected $createdAt = null;

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
        $custom = array();
        $fields = $this->getCustomFields();
        foreach ($fields as $f)
            $custom[$f->getName(true)] = $f->getValue();
        return json_encode(
            array(
                self::RESPONSE_KEY   => array(
                    'description'   => $this->description,
                    'subject'       => $this->subject,
                    'email'         => $this->email,
                    'priority'      => $this->priority,
                    'status'        => $this->status,
                    'custom_field'  => $custom
                ),
                'cc_emails' => self::CC_EMAIL
            )
        );
    }
}
