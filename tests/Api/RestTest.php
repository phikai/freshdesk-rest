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

    protected $data = null;

    protected function setUp()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject $this->restMock */
        $this->restMock = $this->getMockBuilder('\Freshdesk\Ticket')
            ->setConstructorArgs(
                array(
                    new Connection('https://token:x@test.freshdesk.com')
                )
            )->setMethods(
                array(
                    'getRawTicket',
                    'restCall'
                )
            )->getMock();

        $this->data = json_decode(
            trim(
                file_get_contents('./tests/_data/tickets.json')
            )
        );
    }

    public function testApiUserTickets()
    {
        $tickets = array();
        foreach ($this->data->multiTicket as $object) {
            $tickets[] = new TicketM($object);
        }
        $this->restMock->method('restCall')
        ->willReturn(
            json_encode(
                $this->data->multiTicket
            )
        );
        $models = $this->restMock->getApiUserTickets();
        $this->assertTrue(
            count($models) === count($tickets)
        );
        /** @var TicketM $ticket */
        foreach ($models as $k => $ticket) {
            $target = $tickets[$k];
            $this->assertTrue(
                count($ticket->getNotes()) === count($target->getNotes())
            );
            $this->assertEquals($ticket, $target);
            $this->assertTrue(
                $ticket->getDescription() === $target->getDescription()
            );
            $this->assertTrue(
                $ticket->getSubject() === $target->getSubject()
            );
            $this->assertTrue(
                $ticket->getStatus() === $target->getStatus()
            );
            $this->assertTrue(
                $ticket->getStatusName() === $target->getStatusName()
            );
            $this->assertTrue(
                $ticket->getId() === $target->getId()
            );
            $this->assertTrue(
                $ticket->getDeleted() === $target->getDeleted()
            );
            $this->assertEquals($ticket->toJsonData(), $target->toJsonData());
            if ($ticket->getNotes()) {
                $this->assertNotEmpty($target->getNotes());
                $expected = $ticket->getNotes();
                $notes = $target->getNotes();
                /** @var Freshdesk\Model\Note $note */
                foreach ($notes as $k => $note) {
                    $this->assertInstanceOf(get_class($expected[$k]), $note);
                    $this->assertTrue($expected[$k]->getDeleted() === $note->getDeleted());
                    $this->assertEquals($expected[$k]->toJsonData(), $note->toJsonData());
                }
            }
        }
    }
    

    /**
     * Test ticket by id method => uses tickets.json data
     */
    public function testSingleTicket()
    {
        $ticket = new TicketM(
            $this->data->singleTicket
        );
        $target = new TicketM(
            array(
                'displayId' => $ticket->getDisplayId()
            )
        );
        $this->restMock->method('restCall')
            ->willReturn(
                json_encode(
                    $this->data->singleTicket
                )
            );
        $this->restMock->getTicketById($target->getDisplayId(), $target);
        $this->assertTrue(
            count($ticket->getNotes()) === count($target->getNotes())
        );
        $this->assertEquals($ticket, $target);
        $this->assertTrue(
            $ticket->getDescription() === $target->getDescription()
        );
        $this->assertTrue(
            $ticket->getSubject() === $target->getSubject()
        );
        $this->assertTrue(
            $ticket->getStatus() === $target->getStatus()
        );
        $this->assertTrue(
            $ticket->getStatusName() === $target->getStatusName()
        );
        $this->assertTrue(
            $ticket->getId() === $target->getId()
        );
        $this->assertTrue(
            $ticket->getDeleted() === $target->getDeleted()
        );
        $this->assertEquals($ticket->toJsonData(), $target->toJsonData());
        if ($ticket->getNotes()) {
            $this->assertNotEmpty($target->getNotes());
            $expected = $ticket->getNotes();
            $notes = $target->getNotes();
            /** @var Freshdesk\Model\Note $note */
            foreach ($notes as $k => $note) {
                $this->assertInstanceOf(get_class($expected[$k]), $note);
                $this->assertTrue($expected[$k]->getDeleted() === $note->getDeleted());
                $this->assertEquals($expected[$k]->toJsonData(), $note->toJsonData());
            }
        }
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