<?php
use Freshdesk\Model\Ticket;

class TicketTest extends PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider bulkSetterProvider
     */
    public function testSetAll(array $data, $success = true)
    {
        $keys = array_keys($data);
        $getters = array();
        foreach ($keys as $key) {
            $getters[$key] = 'get'.implode('',
                array_map(
                    'ucfirst',
                    explode(
                        '_',
                            $key
                    )
                )
            );
        }
        $data = (object) $data; // cast to object
        if ($success !== true) {
            //not successful, probably expecting exceptions to be thrown:
            try {
                $constructor = new Ticket($data);
            } catch (\Exception $e) {
                $this->assertEquals($success, get_class($e));
            }
            $ticket = new Ticket();
            try {
                $ticket->setAll($data);
            } catch (\Exception $e) {
                $this->assertEquals($success, get_class($e));
            }
            $this->setExpectedException($success);
            $ticket->setAll($data);
            return;
        }
        $constructor = new Ticket($data);
        $ticket = new Ticket($data);
        $this->assertEquals(
            $constructor->toJsonData(),
            $ticket->toJsonData(),
            sprintf(
                'expected %s and %s to match, but it would seem they do not',
                $ticket->toJsonData(),
                $constructor->toJsonData()
            )
        );
        foreach ($data as $key => $value) {
            $getter = $getters[$key];
            $this->assertEquals(
                $value,
                $ticket->{$getter}(),
                sprintf(
                    'TICKET: Expected %s (via %s->%s) to be "%s", instead say "%s"',
                    $key,
                    get_class($ticket),
                    $getter,
                    $value,
                    $ticket->{$getter}()
                )
            );
            $this->assertEquals(
                $value,
                $constructor->{$getter}(),
                sprintf(
                    'CONSTRUCTOR: Expected %s (via %s->%s) to be "%s", instead say "%s"',
                    $key,
                    get_class($constructor),
                    $getter,
                    $value,
                    $constructor->{$getter}()
                )
            );
        }
    }

    /**
     * @return array
     */
    public function bulkSetterProvider()
    {
        return array(
            array(
                array(
                    'id' => 12,
                    'display_id' => 12,
                    'requester_id' => 1,
                    'description' => 'Short description string',
                    'subject' => 'A test',
                    'email' => 'test@foobar.com', // ensure valid email!
                    'priority' => Ticket::PRIORITY_MEDIUM,
                    'status' => Ticket::STATUS_OPEN,
                    'status_name' => 'open'
                ),
                true
            ),
            array(
                array(
                    'id' => 12,
                    'display_id' => 12,
                    'requester_id' => 1,
                    'description' => 'Short description string',
                    'subject' => 'A test',
                    'email' => '""""""***', // Invalid email!
                    'priority' => Ticket::PRIORITY_MEDIUM,
                    'status' => Ticket::STATUS_OPEN,
                    'status_name' => 'open'
                ),
                'InvalidArgumentException'
            )
        );
    }
}