<?php

namespace Freshdesk\Model;

use \DateTime,
    \InvalidArgumentException,
    \Traversable,
    \stdClass;

class Ticket
{

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
     * @var array - add all setters that require a DateTime instsance as argument
     */
    protected $toDateTime = array(
        'setCreatedAt'
    );

    /**
     * $data should be an array, an instance of stdClass
     * OR an object that implements the \Traversable interface
     * @param null|array|\stdClass|\Traversable $data = null
     * @throws \InvalidArgumentException
     */
    public function __construct($data = null)
    {
        if ($data === null)
            return $this;
        if (!$data instanceof Traversable && !$data instanceof \stdClass && !is_array($data))
            throw new InvalidArgumentException(
                sprintf(
                    '%s::%s expects no arguments, or an array, stdClass instance or Traversable object',
                    __CLASS__,
                    __FUNCTION__
                )
            );
        //allow for json responses to be passed directly
        if ($data instanceof stdClass)
            return $this->setByObject($data);
        $set = array();
        foreach ($data as $k => $v)
            $set[$k] = $v;//create array
        return $this->setByObject(
            (object) $set//cast to stdClass
        );
    }

    /**
     * Non-final, as extended models might implement specific methods
     * used in child classes of related models...
     * ATM, this is a copy-paste version of the setByObj function, though
     * @param Traversable $obj
     * @return $this
     */
    public function setByTraversable(Traversable $obj)
    {
        foreach ($obj as $p => $v)
        {
            $setter = 'set'.implode(
                    '',
                    array_map(
                        'ucfirst',
                        explode(
                            '_',
                            $p
                        )
                    )
                );
            if (method_exists($this, $setter))
                $this->{$setter}(
                    in_array($setter, $this->toDateTime) ? new DateTime($v) : $v
                );
        }
        return $this;
    }

    /**
     * Quick alias, to avoid having to juggle data outside of this class
     * @param array $data
     * @return $this
     */
    final public function setByArray(array $data)
    {
        return $this->setByObject(
            (object) $data
        );
    }

    /**
     * @param \stdClass $obj
     * @return $this
     * @throws \InvalidArgumentException
     */
    final public function setByObject(\stdClass $obj)
    {
        if (property_exists($obj, 'errors'))
            throw new InvalidArgumentException(
                sprintf(
                    'Failed to set %s, data was error response: %s',
                    __CLASS__,
                    $obj->errors->error
                )
            );
        if (property_exists($obj, 'helpdesk_ticket'))
            $obj = $obj->helpdesk_ticket;
        foreach ($obj as $p => $v)
        {
            $setter = 'set'.implode(
                '',
                array_map(
                    'ucfirst',
                    explode(
                        '_',
                        $p
                    )
                )
            );
            if (method_exists($this, $setter))
                $this->{$setter}(
                    in_array($setter, $this->toDateTime) ? new DateTime($v) : $v
                );
        }
        return $this;
    }

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
     * Get the json-string for this ticket instance
     * Ready-made to create a new freshdesk ticket
     * @return string
     */
    public function toJsonData()
    {
        return json_encode(
            array(
                'helpdesk_ticket'   => array(
                    'description'   => $this->description,
                    'subject'       => $this->subject,
                    'email'         => $this->email,
                    'priority'      => $this->priority,
                    'status'        => $this->status
                ),
                'cc_emails' => self::CC_EMAIL
            )
        );
    }
}
