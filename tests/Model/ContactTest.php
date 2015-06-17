<?php
use Freshdesk\Model\Contact;

class ContactTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \stdClass
     */
    protected $data = null;

    protected function setUp()
    {
        $this->data = json_decode(
            trim(
                file_get_contents('./tests/_data/user.json')
            )
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp /Contact, data was error response: test error message/
     */
    public function testArgumentExceptions()
    {
        $error = (object) array(
            'errors'    => (object) array(
                'error' => 'test error message'
            )
        );
        new Contact($error);
    }

    public function testRawObjectArgument()
    {
        $contact = new Contact(
            $this->data
        );
        $values = array(
            'getCreatedAt'  => new DateTime(
                $this->data->user->created_at
            ),
            'getUpdatedAt'  => new DateTime(
                $this->data->user->updated_at
            )
        );
        foreach ($values as $getter => $date)
        {
            $this->assertInstanceOf(
                get_class($date),
                $contact->{$getter}()
            );
            $this->assertEquals(
                $date,
                $contact->{$getter}()
            );
            $this->assertEquals(
                $date->format('Y-m-d H:i:s'),
                $contact->{$getter}()->format('Y-m-d H:i:s')
            );
        }
        return $contact;
    }

    /**
     * @depends testRawObjectArgument
     * @param Contact $compare
     */
    public function testAssocArgument(Contact $compare)
    {
        $data = json_decode(
            json_encode(
                $this->data->user
            ),
            true
        );
        $contact = new Contact($data);
        $this->assertEquals(
            $compare,
            $contact
        );
        $methods = get_class_methods($contact);
        foreach ($methods as $name)
        {
            if (substr($name, 0, 3) === 'get')
            {
                $this->assertEquals(
                    $compare->{$name}(),
                    $contact->{$name}()
                );
            }
        }
        $date = new DateTime();
        $instanceof = get_class($date);
        $methods = array(
            'getCreatedAt',
            'getUpdatedAt'
        );
        foreach ($methods as $getter)
        {
            $this->assertInstanceOf(
                $instanceof,
                $contact->{$getter}()
            );
            $this->assertEquals(
                $compare->{$getter}()->format('Y-m-d H:i:s'),
                $contact->{$getter}()->format('Y-m-d H:i:s')
            );
        }
    }

    /**
     * @dataProvider dateTimeDataProvider
     */
    public function testDateTimeSetters(array $args, array $expect)
    {
        $contact = new Contact($args);
        foreach ($expect as $getter => $instance) {
            /** @var DateTime $value */
            $value = $contact->{$getter}();
            if ($instance === null) {
                $this->assertNull(
                    $value,
                    sprintf(
                        'expected %s to return null',
                        $getter
                    )
                );
                continue;
            }
            $this->assertInstanceOf(
                get_class($instance),
                $value,
                sprintf(
                    'Expected %s to return instanceof %s, instead saw %s',
                    $getter,
                    get_class($instance),
                    is_object($value) ? get_class($value) : gettype($value)
                )
            );
            $value = $value->format('Y-m-d H:i:s');
            $this->assertEquals(
                $instance->format('Y-m-d H:i:s'),
                $value,
                sprintf(
                    'Expected return value to be %s, instead saw %s',
                    $instance->format('Y-m-d H:i:s'),
                    $value
                )
            );
            $this->assertTrue(
                in_array($value, $args),
                sprintf(
                    'did not find %s in array of constructor arguments (%s)',
                    $value,
                    json_encode($args)
                )
            );
        }
    }

    /**
     * 
     * @return array
     */
    public function dateTimeDataProvider()
    {
        return array(
            array(
                array(
                    'createdAt' => '2014-01-01 00:00:00',
                    'updatedAt' => '2014-01-01 12:00:00'
                ),
                array(
                    'getCreatedAt' => new DateTime('2014-01-01 00:00:00'),
                    'getUpdatedAt' => new DateTime('2014-01-01 12:00:00')
                )
            ),
            array(
                array(
                    'createdAt' => '2014-01-01 00:00:00'
                ),
                array(
                    'getCreatedAt' => new DateTime('2014-01-01 00:00:00'),
                    'getUpdatedAt' => null
                )
            ),
            array(
                array(
                    'updatedAt' => '2014-01-01 00:00:00'
                ),
                array(
                    'getCreatedAt' => null,
                    'getUpdatedAt' => new DateTime('2014-01-01 00:00:00')
                )
            )
        );
    }
}