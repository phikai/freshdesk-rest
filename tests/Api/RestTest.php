<?php
use Freshdesk\Ticket;
use Freshdesk\Model\Ticket as TicketM;
use Freshdesk\Config\Connection;

class RestTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $restMock = null;

    protected function setUp()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject $this->restMock */
        $this->restMock = $this->getMockBuilder('\Freshdesk\Ticket')
            ->setConstructorArgs(
                array(
                    new Connection('https://token:x@test.freshdesk.com')
                )
            )->getMock();
    }

    public function testGetRaw()
    {
        $error = '{"errors":{"error":"test error message"}}';
        $this->restMock->method('getRawTicket')
            ->willReturn(
                json_decode(
                    $error
                )
            );
        $return = $this->restMock->getRawTicket(
            new TicketM(
                array(
                    'displayId'   => 1
                )
            )
        );
        $this->assertInstanceof(
            'stdClass', $return
        );
        $this->assertEquals($error, json_encode($return));
    }
}