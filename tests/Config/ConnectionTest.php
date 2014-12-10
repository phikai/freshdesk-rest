<?php
use Freshdesk\Config\Connection;

class ConnectionTest extends PHPUnit_Framework_TestCase
{
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