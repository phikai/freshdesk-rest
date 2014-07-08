<?php

namespace Freshdesk\Tool;
use Freshdesk\Config\Connection,
    Freshdesk\Model\Ticket,
    Freshdesk\Rest,
    Freshdesk\Ticket as TicketAPI,
    \stdClass;

class ModelGenerator
{
    /**
     * @var \Freshdesk\Config\Connection
     */
    protected $connection = null;

    /**
     * @var \Freshdesk\Rest
     */
    protected $api = null;

    private static $propertyFormat = array(
        '    /*',
        '     * @var %s',
        '     */',
        '    protected $%s = null;'
    );

    private static $setterFormat = array(
        '    /*',
        '     * @param %s $arg',
        '     * @return $this',
        '     */',
        '    public function %s($arg)',
        '    {',
        '        $this->%s = $arg;',
        '        return $this;',
        '    }'
    );

    private static $getterFormat = array(
        '    /*',
        '     * @return %s',
        '     */',
        '    public function %s()',
        '    {',
        '        return $this->%s;',
        '    }'
    );

    /**
     * @param Connection $con
     */
    public function __construct(Connection $con)
    {
        self::$setterFormat = PHP_EOL.implode(
            PHP_EOL,
            self::$setterFormat
        );
        self::$getterFormat = PHP_EOL.implode(
            PHP_EOL,
            self::$getterFormat
        );
        self::$propertyFormat = PHP_EOL.implode(
            PHP_EOL,
            self::$propertyFormat
        );
        $this->connection = $con;
    }

    /**
     * If storeIn is false, then the class string will be returned
     * For testing purposes only, pass absolute path, and the class will be written there
     * @param Ticket $ticket
     * @param string $name
     * @param string|bool $storeIn = false
     * @throws \InvalidArgumentException
     */
    public function generateTicketClass(Ticket $ticket, $name = 'TicketX', $storeIn = false)
    {
        $path = null;
        if ($storeIn !== false)
        {//make sure class doesn't exist yet
            $path = $storeIn.'/'.$name.'.php';
            if (file_exists($path))
                throw new \RuntimeException(
                    sprintf(
                        'Cannot create new class %s in %s, file %s already exists',
                        $name,
                        $storeIn,
                        $path
                    )
                );
        }
        $api = $this->getTicketApi();
        $model = $api->getRawTicket(
            $ticket
        );
        if (property_exists($model, 'errors'))
            throw new \InvalidArgumentException(
                'Ticket not found, cannot generate model'
            );
        $base = $model->helpdesk_ticket;
        $class = explode(
            '\\',
            get_class($ticket)
        );
        $className = array_pop($class);
        $components = array(
            'namespace'     => implode('\\', $class). ';',
            'name'          => $name,
            'extends'       => $className,
            'properties'    => array(),
            'methods'       => array()
        );
        foreach ($base as $p => $v)
        {
            $setter = 'get'.implode(
                '',
                array_map(
                    'ucfirst',
                    explode(
                        '_',
                        $p
                    )
                )
            );
            if (!method_exists($ticket, $setter))
            {
                if (is_array($v))
                    $type = 'array';
                elseif (is_object($v))
                    $type = '\\stdClass';
                elseif ($v === null)
                    $type = 'mixed';
                elseif(preg_match('/[\d-]{10}T[\d:+]{14}/', $v))
                    $type = '\\DateTime';
                elseif(is_numeric($v))
                    $type = 'int';
                else
                    $type = gettype($v);
                $p = strtolower(
                    $setter{3}
                ).substr($setter, 4);
                $components['properties'][$p] = $type;
                $components['methods'][$p] = array(
                    $setter,
                    's'.substr($setter, 1)
                );
            }
        }
        $string = array(
            '<?php',
            'namespace '.$components['namespace'],
            'class '.$components['name'].' extends '.$components['extends'],
            '{',
        );
        $methods = array();
        foreach ($components['properties'] as $p => $type)
        {
            $string[] = sprintf(
                self::$propertyFormat,
                $type,
                $p
            );
            $methods[] = sprintf(
                self::$getterFormat,
                $type,
                $components['methods'][$p][0],
                $p
            );
            $setter =  sprintf(
                self::$setterFormat,
                $type,
                $components['methods'][$p][1],
                $p
            );
            if ($type{0} === '\\' || $type === 'array')
                $setter = str_replace(
                    '($arg)',
                    '('.$type.' $arg)',
                    $setter
                );
            $methods[] = $setter;
        }
        $methods[] = '}';
        $string = implode(
            PHP_EOL,
            array_merge($string, $methods)
        ).PHP_EOL;
        if ($path !== null)
            file_put_contents(
                $path,
                $string
            );
        return $string;
    }

    /**
     * @return \Freshdesk\Ticket
     */
    protected function getTicketApi()
    {
        if (!$this->api instanceof TicketAPI)
            $this->api = new TicketAPI(
                $this->connection
            );
        return $this->api;
    }

    /**
     * @return \Freshdesk\Rest
     */
    public function getRestApi()
    {
        if (!$this->api instanceof Rest)
            $this->api = new Rest(
                $this->connection
            );
        return $this->api;
    }
}