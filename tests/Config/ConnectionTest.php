<?php
use Freshdesk\Config\Connection;

class ConnectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Connection
     */
    protected $instance = null;

    protected function setUp()
    {
        //pass valid url to instance first
        $this->instance = new Connection('https://user:pass@test.freshdesk.com');
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessageRegExp /expects \$url to be parsable, "/
     */
    public function testUrlSetterFalse()
    {
        $this->instance->setByUrl(':');
    }
    
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp /expects \$url to be a string not a "/
     * @dataProvider setUrlMixedProvider
     */
    public function testUrlSetterType($argument)
    {
        $this->instance->setByUrl($argument);
    }

    /**
     * @return array
     */
    public function setUrlMixedProvider()
    {
        return array(
            array(
                null
            ),
            array(
                array('some array')
            ),
            array(
                1234
            ),
            array(
                new stdClass()
            ),
            array(
                $this->instance
            )
        );
    }

    /**
     * @dataProvider setUrlStringProvider
     */
    public function testUrlSetterError($url, $success = true)
    {
        if ($success === false) {
            $this->setExpectedException('RuntimeException');
            $this->instance->setByUrl($url);
        } else {
            $this->instance->setByUrl($url);
            $parsed = parse_url($url);
            $this->assertEquals(
                $parsed['pass'],
                $this->instance->getPassword(),
                sprintf(
                    '%s expected to be equal to %s',
                    $this->instance->getPassword(),
                    $parsed['pass']
                )
            );
            $this->assertEquals(
                $parsed['scheme'].'://',
                $this->instance->getScheme(),
                sprintf(
                    '%s expected to be equal to %s',
                    $this->instance->getScheme(),
                    $parsed['scheme'].'://'
                )
            );
            $this->assertEquals(
                $parsed['host'],
                $this->instance->getDomain(),
                sprintf(
                    '%s expected to be equal to %s',
                    $this->instance->getDomain(),
                    $parsed['host']
                )
            );
            $this->assertEquals(
                $parsed['user'],
                $this->instance->getUserName(),
                sprintf(
                    '%s expected to be equal to %s',
                    $this->instance->getUserName(),
                    $parsed['user']
                )
            );
        }
    }

    /**
     * @return array
     */
    public function setUrlStringProvider()
    {
        return array(
            array(
                'ftps://user:pass@www.google.com',
                false
            ),
            array(
                'https://valid:pass@test.freshdesk.com'
            ),
            array(
                'file://user:pass@host',
                false
            ),
            array(
                'http://non:secure@some.domain.co.uk'
            )
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp /[^:]+::__construct.*expects \$url to be a string, instead saw/
     * @dataProvider invalidArgumentDataProvider
     */
    public function testInvalidArgumentType($argument)
    {
        new Connection($argument);
    }

    /**
     * @return array
     */
    public function invalidArgumentDataProvider()
    {
        return array(
            array(
                new stdClass
            ),
            array(
                null
            ),
            array(
                array()
            )
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp /is a malformed url \(expected format: <scheme>:\/\/<user>:<pass>@<domain>\)/
     * @dataProvider invalidConnectionUrlsDataProvider
     */
    public function testInvalidConnectionUrls($url)
    {
        $test = new Connection($url);
    }

    /**
     * @return array
     */
    public function invalidConnectionUrlsDataProvider()
    {
        return array(
            array(
                'ftp://some.bad.url',
            ),
            array(
                'a random string'
            ),
            array(
                'http://www.google.com'
            )
        );
    }
}