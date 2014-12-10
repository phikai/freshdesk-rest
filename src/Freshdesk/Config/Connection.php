<?php
namespace Freshdesk\Config;

use \InvalidArgumentException;

/**
 * Class Connection
 * @package Freshdesk\Config
 */
class Connection
{
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
     * @var string
     */
    protected $baseUrl = null;

    /**
     * @var string
     */
    protected $loginString = null;

    /**
     * @var bool false
     */
    protected $debug = false;

    /**
     * Constructor, expects fully-qualified url
     * @param string $url
     * @param bool $debug = false
     * @throws \InvalidArgumentException
     */
    public function __construct($url, $debug = false)
    {
        if (!is_string($url))
        {
            throw new InvalidArgumentException(
                sprintf(
                    '%s expects $url to be a string, instead saw %s',
                    __METHOD__,
                    gettype($url)
                )
            );
        }
        if (!preg_match('/^https?:\/\/[^:]+:[^@]+@.+$/', $url))
            throw new InvalidArgumentException(
                sprintf(
                    '%s is a malformed url (expected format: <scheme>://<user>:<pass>@<domain>)',
                    $url
                )
            );
        $data = parse_url($url);
        $scheme = $data['scheme'].'://';
        if ($scheme === self::SCHEME_HTTP || $scheme === self::SCHEME_HTTPS)
            $this->scheme = $scheme;
        $this->username = $data['user'];
        $this->password = $data['pass'];
        $this->domain = $data['host'];
        $this->debug = $debug;
    }

    /**
     * @param string $url
     * @param bool $debug = false
     * @return $this
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \LogicException
     */
    public function setByUrl($url, $debug = false)
    {
        if (!is_string($url))
        {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s expects $url to be a string not a "%s"',
                    __METHOD__,
                    gettype($url)
                )
            );
        }
        $data = parse_url($url);
        if ($data === false)
        {
            throw new \LogicException(
                sprintf(
                    '%s expects $url to be parsable, "%s" is not',
                    __METHOD__,
                    $url
                )
            );
        }
        $scheme = $data['scheme'].'://';
        if ($scheme !== self::SCHEME_HTTP && $scheme !== self::SCHEME_HTTPS)
        {
            throw new \RuntimeException(
                sprintf(
                    '%s is an invalid scheme, expecting %s or %s',
                    $scheme,
                    self::SCHEME_HTTP,
                    self::SCHEME_HTTPS
                )
            );
        }
        /** @noinspection PhpUndefinedMethodInspection */
        $this->setPassword($data['pass'])
            ->setUserName($data['user'])
            ->setDomain($data['host'])
            ->setScheme($scheme);
        $this->debug = $debug;
        return $this;
    }

    /**
     * Set the domain - Strips redundant info
     * @param string $domain
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->baseUrl = null;
        //remove possible login data <user>:<pass>@<--
        $i = strpos($domain, '@');
        if ($i !== false)
            $domain = substr($domain, $i+1);
        //remove leading scheme, if in string
        $i = strpos($domain, '://');
        if ($i !== false)
            $domain = substr($domain, $i+3);
        //remove trailing slashes
        while(($i = strpos($domain, '/')) && $i !== false)
            $domain = substr($domain, 0, $i);
        $this->domain = $domain;
        return $this;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Passing null defaults to the generic "X" password
     * @param string $pass
     * @return $this
     */
    public function setPassword($pass)
    {
        $this->loginString = $this->baseUrl = null;
        $this->password = $pass === null ? 'X' : $pass;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $user
     * @return $this
     */
    public function setUserName($user)
    {
        $this->loginString = $this->baseUrl = null;//force re-load of baseUrl
        $this->username = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->username;
    }

    /**
     * Scheme setter - validates argument!
     * @param string $scheme
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setScheme($scheme)
    {
        if ($scheme !== self::SCHEME_HTTP && $scheme !== self::SCHEME_HTTPS)
            throw new InvalidArgumentException(
                sprintf(
                    '%s is not a valid scheme, use %s::SCHEME_* constants',
                    $scheme,
                    __CLASS__
                )
            );
        $this->loginString = $this->baseUrl = null;
        $this->scheme = $scheme;
        return $this;
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @param bool $switch
     * @return $this
     */
    public function setDebug($switch = false)
    {
        $this->debug = !!$switch;
        return $this;
    }

    /**
     * @return bool
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * @return string
     */
    public function getLoginString()
    {
        if ($this->loginString === null)
            $this->loginString = sprintf(
                '%s:%s',
                $this->username,
                $this->password
            );
        return $this->loginString;
    }

    /**
     * Lazy-loader/generator of the base-url to which API requests will be sent
     * @return string
     */
    public function getBaseUrl()
    {
        if ($this->baseUrl === null)
            $this->baseUrl = sprintf(
                '%s%s:%s@%s',
                $this->scheme,
                $this->username,
                $this->password,
                $this->domain
            );
        return $this->baseUrl;
    }
}
