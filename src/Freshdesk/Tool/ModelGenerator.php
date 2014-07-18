<?php

namespace Freshdesk\Tool;
use Freshdesk\Config\Connection,
    Freshdesk\Model\Ticket,
    Freshdesk\Rest,
    Freshdesk\Ticket as TicketAPI;

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

    private static $arrayFormat = array(
        '    /**',
        '     * @var %s',
        '     */',
        '    protected $%s = array('
    );

    private static $arrayValue = '        \'%s\',';

    private static $arrayClose = '    );';

    private static $propertyFormat = array(
        '    /**',
        '     * @var %s',
        '     */',
        '    protected $%s = null;'
    );

    private static $setterFormat = array(
        '    /**',
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
        '    /**',
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
        self::$arrayFormat = PHP_EOL.implode(
            PHP_EOL,
            self::$arrayFormat
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
     * Since this method should use a GET request (query data), only GET requests are supported
     * @param string $callString URI for the request to get the data
     * @param string $name Name of the class to generate
     * @param bool $overWrite = falsea
     * @throws \RuntimeException
     */
    public function generateNewModel($callString, $name, $overWrite = false)
    {
        $path = realpath(__DIR__.'/../Model/').'/'.$name.'.php';
        if ($overWrite === false && file_exists($path))
            throw new \RuntimeException(
                sprintf(
                    'File %s already exists, where %s class would be stored',
                    $path,
                    $name
                )
            );
        $api = $this->getRestApi();
        $response = json_decode(
            $api->getCall(
                $callString
            )
        );
        if (property_exists($response, 'errors'))
            throw new \RuntimeException(
                sprintf(
                    '%s call resulted in errors: %s%sRaw response => %s',
                    $callString,
                    $response->errors->error,
                    PHP_EOL,
                    json_encode(
                        $response
                    )
                )
            );
        $constCandidates = array(
            'names' => array(),
            'count' => array()
        );
        $j = 0;
        foreach ($response as $cand => $data)
        {
            ++$j;
            $constCandidates['names'][] = $cand;
            $constCandidates['count'][] = count( (array) $data);
        }
        $max = 0;
        $const = '';
        for ($i=0;$i<$j;++$i)
        {
            if ($constCandidates['count'][$i] > $max)
            {
                $const = $constCandidates['names'][$i];
                $max = $constCandidates['count'][$i];
            }
        }
        $string = array(
            '<?php',
            'namespace Freshdesk\\Model;',
            'class '.$name.' extends Base',
            '{',
            '    const RESPONSE_KEY = \''.$const.'\';'
        );
        $components = array(
            'properties'    => array(),
            'methods'       => array(),
            'toDateTime'    => array()
        );
        $data = $response->{$const};
        foreach ($data as $p => $val)
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
            if (is_array($val))
                $type = 'array';
            elseif (is_object($val))
                $type = '\\stdClass';
            elseif ($val === null)
                $type = 'mixed';
            elseif(preg_match('/[\d-]{10}T[\d:+]{14}/', $val))
                $type = '\\DateTime';
            elseif(is_numeric($val))
                $type = 'int';
            else
                $type = gettype($val);
            $p = strtolower(
                    $setter{3}
                ).substr($setter, 4);
            if ($type === '\\DateTime')
                $components['toDateTime'][] = $setter;
            $components['properties'][$p] = $type;
            $components['methods'][$p] = array(
                $setter,
                'g'.substr($setter, 1)
            );
        }
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
                $components['methods'][$p][1],
                $p
            );
            $setter =  sprintf(
                self::$setterFormat,
                $type,
                $components['methods'][$p][0],
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
        $string[] = sprintf(
            self::$arrayFormat,
            'array',
            'toDateTime'
        );
        foreach ($components['toDateTime'] as $setter)
            $string[] = sprintf(
                self::$arrayValue,
                $setter
            );
        $string[] = self::$arrayClose;
        $methods[] = '    public function toJsonData(){}';
        $methods[] = '}';
        $string = implode(
                PHP_EOL,
                array_merge($string, $methods)
            ).PHP_EOL;
        file_put_contents(
            $path,
            $string
        );
        return $string;
    }

    /**
     * If storeIn is false, then the class string will be returned
     * For testing purposes only, pass absolute path, and the class will be written there
     * set $overwrite to true if you want to overwrite an existing class
     * @param Ticket $ticket
     * @param string $name
     * @param string|bool $storeIn = false
     * @param bool $overwrite = false
     * @throws \InvalidArgumentException
     */
    public function generateTicketClass(Ticket $ticket, $name = 'TicketX', $storeIn = false, $overwrite = false)
    {
        $path = null;
        if ($storeIn !== false)
        {//make sure class doesn't exist yet
            if (substr($storeIn, -4) !== '.php')
                $path = $storeIn.'/'.$name.'.php';
            else
                $path = $storeIn;
            if ($overwrite === false && file_exists($path))
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
        if (strstr($name, '_'))
        {//check pseudo-namespaced class, don't add namespace component
         //instead, add use statement for base-class
            unset($components['namespace']);
            $components['use'] = implode('\\', $class).'\\'.$className;
        }
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
            '<?php'
        );
        if (isset($components['namespace']))
            $string[] = 'namespace '.$components['namespace'].';';
        else
            $string[] = 'use '.$components['use'].';';
        $string[] = 'class '.$components['name'].' extends '.$components['extends'];
        $string[] = '{';
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
