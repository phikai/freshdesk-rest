<?php
/**
 * Implements FreshDesk API methods for Tickets and Surveys in PHP.
 * See README.md
 * Forked from: https://github.com/blak3r/freshdesk-solutions
 * Big thanks to Blake for building the initial API Object Methods
 */

class FreshdeskRest
{

    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';
    const METHOD_DEL = 'DELETE';
    const METHOD_PUT = 'PUT';

    const SCHEME_HTTP = 'http://';
    const SCHEME_HTTPS = 'https://';

    /**
     * @var string
     */
    protected $scheme = self::SCHEME_HTTP;

    /**
     * @var string
     */
    protected $domain = null;

    /**
     * @var string
     */
    protected $username = null;

    /**
     * @var string
     */
    protected $password = null;

    /**
     * @var int
     */
    protected $lastHttpStatusCode = null;

    /**
     * @var string
     */
    protected $lastHttpResponseText = '';

    /**
     * @var string
     */
    protected $proxyServer = "";

    /**
     * @var array<string>
     */
    protected $debugLogs = array();

    /**
     * Constructor - Passing a full url is enough,but you can pass domain, user, pass and scheme separatly.
     *               Note the order: scheme sits in between user & password, because API keys default
     *               To the generic "X" password. If a password is provided, $scheme will become the password
     * @param string $domain - yourname.freshdesk.com - but will also accept http://yourname.freshdesk.com/, etc.
                        pass "http[s]://user:pass@yourname.freshdesk.com" to set everything in one go
     * @param string $username = null
     * @param string $scheme = null
     * @param string $password = null
     */
    public function __construct($domain, $username = null, $scheme = null,  $password = null)
    {
        $url = parse_url($domain);

        if ($username === null && $password === null)
        {//assume $domain is full url, parse and set (after checking, of course)
            if (!preg_match('/^https?:\/\/[^:]+:[^@]+@.+$/',$domain))
                throw new \InvalidArgumentException(
                    sprintf(
                        '%s should be a fully qualified domain (http[s]://user:pass@domain)',
                        $domain
                    )
                );
            $url = parse_url($domain);
            $this->scheme = $url['scheme'].'://';
            $this->password = $url['pass'];
            $this->username = $url['user'];
            $this->domain = $url['host'];
            return $this;//constructor is done here, return here, to avoid having to write an else-block
        }
        //remove leading http[s]://, if present. strpos + substr === fast functions
        $i = strpos($domain, '://');
        if ($i !== false)
            $domain = substr($domain, $i+3);
        //while a slash is found in the $domain string, substr it out
        while(($i = strpos($domain, '/')) && $i !== false)
            $domain = substr($domain, 0, $i);

        if ($scheme && $password === null && $scheme !== self::SCHEME_HTTP && $scheme !== self::SCHEME_HTTPS)
        {//$scheme is actually $password, added this not to break existing code
            $password = $scheme;
            $scheme = null;
        }
        if ($scheme)
            $this->scheme = $scheme;
        $this->domain = $domain;
        $this->password = $password === null ? 'X' : $password;
        $this->username = $username;
    }

    /**
     * @param $urlMinusDomain - should start with /... example /solutions/categories.xml
     * @param $method - should be either GET, POST, PUT (and theoretically DELETE but that's untested).
     * @param string $postData - only specified if $method == POST or PUT
     * @param $debugMode {bool} optional - prints the request and response with headers
     * @return the raw response
     */
    protected function restCall($urlMinusDomain, $method, $postData = '',$debugMode=false)
    {
        if ($urlMinusDomain{0} !== '/')
            $urlMinusDomain = '/'.$urlMinusDomain;
        $url = $this->scheme.$this->domain.$urlMinusDomain;

        $header = array(
            "Content-type: application/json"
        );

        $opts = array(
            \CURLOPT_USERPWD        => $this->username.':'.$this->password,
            \CURLOPT_HTTPHEADER     => array(
                'Content-type: application/json'
            ),
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_HTTPAUTH       => \CURLAUTH_BASIC,
            \CURLOPT_SSL_VERIFYHOST => 0,
            \CURLOPT_SSL_VERIFYPEER => 0
        );
        if ($this->proxyServer)
            $opts[\CURLOPT_PROXY] = $this->proxyServer;
        if ($debugMode)
        {
            // CURLOPT_VERBOSE: TRUE to output verbose information. Writes output to STDERR,
            // or the file specified using CURLOPT_STDERR.
            $opts[\CURLOPT_STDERR] = fopen('php://temp', 'rw+');
            $opts[\CURLOPT_VERBOSE] = true;
        }
        switch (strtoupper(trim($method)))
        {
            case self::METHOD_POST:
                if (empty($postData))
                    $opts[\CURLOPT_HTTPHEADER][] = 'Content-length: 0';
                $opts[\CURLOPT_POST] = true;
                $opts[\CURLOPT_POSTFIELDS] = $postData;
                break;
            case self::METHOD_PUT:
                $opts[\CURLOPT_CUSTOMREQUEST] =  'PUT';
                $opts[\CURLOPT_POSTFIELDS] = $postData;
            case self::METHOD_DEL:
                $opts[\CURLOPT_CUSTOMREQUEST] = 'DELETE';
                break;
            case self::METHOD_GET:
                $opts[\CURLOPT_POST] = false;
                break;
            default:
                if ($debugMode)
                    fclose($opts[\CURLOPT_STDERR]);//close stream, we have an error
                throw new \InvalidArgumentException(
                    sprintf(
                        'Method "%s" is not a valid method, use %s::METHOD_* constants',
                        $method,
                        __CLASS__
                    )
                );
        }
        $ch = curl_init($url);
        if (!is_resource($ch))
        {
            fclose($opts[\CURLOPT_STDERR]);//close stream
            throw new \RuntimeException(
                'Could not init curl request'
            );
        }
        if (!curl_setopt_array($ch, $opts))
        {
            fclose($opts[\CURLOPT_STDERR]);
            throw new \RuntimeException('Could not set curl options');
        }

        $this->lastHttpResponseText = $httpResponse = curl_exec($ch);
        $this->lastHttpStatusCode = $httpCode = (int) curl_getinfo(
            $ch,
            \CURLINFO_HTTP_CODE
        );
        if ($httpCode < 200 || $httpCode > 299)
        {
            if (!$debugMode)
            {
                fclose($opts[\CURLOPT_STDERR]);//close stream
                curl_close($ch);//close curl
                throw new \RuntimeException(
                    sprintf(
                        '%s action to %s returned unexpected HTTP code (%d), repsonse: %s',
                        $method,
                        $url,
                        $httpCode,
                        $httpRespnse
                    )
                );
            }
        }
        if ( $debugMode )
        {
            if (rewind($opts[\CURLOPT_STDERR]))
                $this->debugLogs[] = array(
                    'URL'       => $url,
                    'Method'    => $method,
                    'HTTPCode'  => $httpCode,
                    'Stream'    => stream_get_contents($opts[\CURLOPT_STDERR])
                );
            else
                $this->debugLogs[] = array(
                    'URL'       => $url,
                    'Method'    => $method,
                    'HTTPCode'  => $httpCode,
                    'Stream'    => 'ERROR: rewind stream failed!'
                );
            fclose($opts[\CURLOPT_STDERR]);
        }
        curl_close($http);

        return $httpResponse;
    }

    /**
     * Get the log data if calls were made in debug mode
     * Unless you have a good reason not to, stick to the default behaviour
     * take care of the log-output yourself!
     * @param bool $return = true
     * @return array|null
     */
    public function logDebugData($return = true)
    {
        if (empty($this->debugLogs))
            return '';//nothing to log
        //first line => headers
        $data = array(
            implode(
                ' | ',
                array_keys(
                    $this->debugLogs[0]
                )
            )
        );
        while ($log = array_shift($this->debugLogs))
        {//keep shifting from the array, until it's empty
            $data[]  = implode(' | ', $log);
        }
        if ($return)
            return $data;
        //NOT DEFAULT BEHAVIOUR, only use in rare cases. This class should not generate output!
        echo implode(
            PHP_EOL,
            $data
        );
    }

    /**
     * Set the scheme (using the class' constants, preferably)
     * @param string $scheme
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setScheme($scheme)
    {
        if ($scheme !== self::SCHEME_HTTP && $scheme !== self::SCHEME_HTTPS)
            throw new \InvalidArgumentException(
                sprintf(
                    '%s is not a valid scheme, use the %s::SCHEME_* constants',
                    $scheme,
                    __CLASS__
                )
            );
        $this->scheme = $scheme;
        return $this;
    }

    /**
     * Returns the HTTP status code of the last call, useful for error checking.
     * @return int
     */
    public function getLastHttpStatus()
    {
        return $this->lastHttpStatusCode;
    }

    /**
     * Returns the HTTP Response Text of the last curl call, useful for error checking.
     * @return int
     */
    public function getLastHttpResponseText()
    {
        return $this->lastHttpResponseText;
    }

    /**
     * Will force cURL requests to use the proxy.  Can be useful to debug requests and responses
     * using Fiddler2 or WireShark.
     * curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888'); // Use with Fiddler to debug
     * @param $proxyServer - example for fiddler2 default: '127.0.0.1:8888'
     */
    public function setProxyServer($proxyServer)
    {
        $this->proxyServer = $proxyServer;
    }


    /**
     * Returns all the open tickets of the API user's credentials used for the request
     * @return null|\stdClass
     */
    public function getApiUserTickets()
    {
        $json = $this->restCall(
            '/helpdesk/tickets.json',
            self::METHOD_GET
        );

        if (!$json)
            return null;
        return json_decode($json);
    }


    /**
     * Returns all the tickets
     * @param int $page
     * @return null|\stdClass
     */
    public function getAllTickets($page)
    {
        $json = $this->restCall(
            '/helpdesk/tickets.json?filter_name=all_tickets&page='.$page,
            self::METHOD_GET
        );

        if (!$json)
            return null;
        return json_decode($json);
    }


    /**
     * Returns the Ticket, this method takes in the IDs for a ticket.
     * @param int $ticketId
     * @return null|\stdClass
     */
    public function getSingleTicket($ticketId)
    {
        $json = $this->restCall(
            '/helpdesk/tickets/'.$ticketId.'.json',
            self::METHOD_GET
        );
        if (!$json)
            return null;
        return json_decodE($json);
    }


    /**
     * Returns all tickets from the user specified by email address
     * @param string $email
     * @return null|\stdClass
     */
    public function getUserTickets($email)
    {
        $json = $this->restCall(
            '/helpdesk/ticket/user_ticket.json?email='.$email,
            self::METHOD_GET
        );
        if (!$json)
            return null;
        return json_decode($json);
    }


    /**
     * Returns tickets for a specific view
     * @param int $viewId
     * @param int $page
     * @return null|\stdClass
     */
    public function getTicketView($viewId, $page)
    {
        $json = $this->restCall(
            '/helpdesk/tickets/view/'.$viedId.'?format=json&page='.$page,
            self::METHOD_GET
        );
        if (!$json)
            return null;
        return json_decode($json);
    }


    /**
     * Returns the fields available to helpdesk tickets
     * @return null|\stdClass
     */
    public function getTicketFields()
    {
        $json = $this->restCall(
            '/ticket_fields.json',
            self::METHOD_GET
        );
        if (!$json)
            return null;
        return json_decode($json);
    }


    /**
     * Returns the Survey for a given ticket, this method takes in the IDs for a ticket
     * @param int $ticketId
     * @return null|\stdClass
     */
    public function getTicketSurvey($ticketId)
    {
        $json = $this->restCall(
            '/helpdesk/tickets/'.$ticketId.'/surveys.json',
            self::METHOD_GET
        );
        if (!$json)
            return null;
        return json_decode($json);
    }
}
