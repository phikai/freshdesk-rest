<?php
use Freshdesk\Model\Note;

class NoteTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $data = array();

    protected function setUp()
    {
        $data = json_decode(
            trim(
                file_get_contents('./tests/_data/tickets.json')
            )
        );
        foreach ($data->singleTicket->helpdesk_ticket->notes as $o)
        {
            $this->data[] = json_decode(
                json_encode(
                    $o
                ),
                true
            );
        }
    }

    /**
     * @dataProvider datePropertiesProvider
     */
    public function testDateProperties(array $values)
    {
        $note = new Note($values);
        $instance = new Note();
        if (isset($values['createdAt'])) {
            $this->assertInstanceOf('DateTime', $note->getCreatedAt(false));
            $this->assertEquals($values['createdAt'], $note->getCreatedAt());
            $instance->setCreatedAt(new DateTime($values['createdAt']));
            $this->assertEquals($values['createdAt'], $instance->getCreatedAt());
        }
        if (isset($values['updatedAt'])) {
            $this->assertInstanceOf('DateTime', $note->getUpdatedAt(false));
            $this->assertEquals($values['updatedAt'], $note->getUpdatedAt());
            $instance->setUpdatedAt(new DateTime($values['updatedAt']));
            $this->assertEquals($values['updatedAt'], $instance->getUpdatedAt());
        }
    }

    /**
     * @return array
     */
    public function datePropertiesProvider()
    {
        return array(
            array(
                array(
                    'createdAt' => '2014-01-02 03:04:05',
                    'updatedAt' => '2014-11-12 13:14:15'
                )
            ),
            array(
                array(
                    'updatedAt' => '2014-11-12 13:14:15'
                )
            ),
            array(
                array(
                    'createdAt' => '2014-01-02 03:04:05'
                )
            ),
            array(
                array(
                    'createdAt' => '2013-01-02 03:04:05',
                    'updatedAt' => '2014-11-12 13:14:15'
                )
            )
        );
    }

    public function testSetAll()
    {
        $getters = array();
        foreach ($this->data as $data) {
            $note = new Note($data);
            $setAll = new Note();
            $setAll->setAll($data);
            $this->assertEquals(
                $note->toJsonData(),
                $setAll->toJsonData(),
                'String compare fails'
            );
            $this->assertEquals(
                $note,
                $setAll,
                'Object compare fails'
            );
            $data = isset($data[Note::RESPONSE_KEY]) ? $data[Note::RESPONSE_KEY] : $data;
            foreach ($data as $k => $v) {
                if (!isset($getters[$k])) {
                    $getter = 'get' . implode(
                            '',
                            array_map(
                                'ucfirst',
                                explode(
                                    '_',
                                    $k
                                )
                            )
                        );
                    $getters[$k] = $getter;
                }
                $getter = $getters[$k];
                if (!method_exists($note, $getter)) {
                    continue;
                }
                if ($note->{$getter}(false) instanceof DateTime) {
                    $this->assertInstanceOf(
                        'DateTime',
                        $setAll->{$getter}(false)
                    );
                    //transform ISO 8601 format to Y-m-d H:i:s
                    $ymdhis = substr(
                        str_replace('T', ' ', $v),
                        0, -6
                    );
                    $this->assertEquals($v, $note->{$getter}(false)->format('c'));
                    $this->assertEquals($v, $setAll->{$getter}(false)->format('c'));
                    $this->assertEquals($ymdhis, $setAll->{$getter}(), $ymdhis);
                    $this->assertEquals($ymdhis, $note->{$getter}(), $ymdhis);
                } else {
                    $this->assertEquals($v, $note->{$getter}());
                    $this->assertEquals($v, $setAll->{$getter}());

                }
                $this->assertEquals(
                    $note->{$getter}(),
                    $setAll->{$getter}()
                );
            }
        }
    }
}