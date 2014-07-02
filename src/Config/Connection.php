<?php
namespace Freshdesk\Config;

use \InvalidArgumentException;

class Connection
{
    const SCHEME_HTTP = 'http://';
    const SCHEME_HTTPS = 'https://';

    /**
     * @var string
     */
    protected $scheme = self::SCHEM_HTTP;

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
     * Constructor, expects fully-qualified url
     * @param string $url
     */
    public function __construct($url)
    {
        if (!preg_match('/^https?:\/\/[^:]+:[^@]+@.+$/', $url))
            throw new InvalidArgumentException(
                sprintf(
                    '%s is a malformed url (expected format: <scheme>://<user>:<pass>@<domain>)',
                    $url
            );
        $data = parse_url($url);
        $scheme = $data['scheme'].'://';
        if ($scheme === self::SCHEME_HTTP || $scheme === self::SCHEME_HTTPS)
            $this->scheme = $scheme;
        $this->username = $data['user'];
        $this->password = $data['pass'];
        $this->domain = $url['host'];
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
        $this->baseUrl = null;
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
        if ($this->username)
            $this->baseUrl = null;//force re-load of baseUrl
        $this->username = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
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
        $this->baseUrl = null;
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
