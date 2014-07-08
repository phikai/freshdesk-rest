<?php

namespace Freshdesk\Model;

use \Traversable,
    \InvalidArgumentException,
    \stdClass,
    \Iterator,
    \DateTime;


abstract class Base implements Iterator
{
    const RESPONSE_KEY = '';
    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var array
     */
    private $getters = array();

    /**
     * @var array
     */
    protected $toDateTime = array();

    /**
     * $data should be an array, an instance of stdClass
     * OR an object that implements the \Traversable interface
     * @param null|array|\stdClass|\Traversable $data = null
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function __construct($data = null)
    {
        if (self::RESPONSE_KEY === '')
            throw new \RuntimeException(
                sprintf(
                    '%s does not have a RESPONSE_KEY defined!',
                    get_class($this)
                )
            );
        $methods = get_class_methods($this);
        foreach ($methods as $method)
        {//use GETTERS for iterator interface
            if (substr($method, 0, 3) === 'get')
                $this->getters[] = $method;
        }
        if ($data === null)
            return $this;
        return $this->setAll($data);
    }

    /**
     * Set all properties by traversabele, stdClass or array
     * @param $mixed
     * @return $this
     * @throws \InvalidArgumentException
     */
    final public function setAll($mixed)
    {
        if ($mixed instanceof Traversable)
            return $this->setByTraversable($mixed);
        elseif (is_object($mixed) && !$mixed instanceof stdClass)
            throw new InvalidArgumentException(
                sprintf(
                    '%s expects array, stdClass instance or Traversable object',
                    __METHOD__
                )
            );
        return $this->setByObject(
            (object) $mixed
        );
    }

    /**
     * Non-final, as extended models might implement specific methods
     * used in child classes of related models...
     * ATM, this is a copy-paste version of the setByObj function, though
     * @param Traversable $obj
     * @return $this
     */
    protected function setByTraversable(Traversable $obj)
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
     * @param \stdClass $obj
     * @return $this
     * @throws \InvalidArgumentException
     */
    final protected function setByObject(\stdClass $obj)
    {
        if (property_exists($obj, 'errors'))
            throw new InvalidArgumentException(
                sprintf(
                    'Failed to set %s, data was error response: %s',
                    __CLASS__,
                    $obj->errors->error
                )
            );
        if (property_exists($obj, self::RESPONSE_KEY))
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
     * Every object must have toJsonDate method...
     * @return string
     */
    abstract function toJsonData();

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->{$this->getters[$this->position]}();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return (
            isset($this->getters[$this->position])
            && is_callable(array($this, $this->getters[$this->position]))
        );
    }

}