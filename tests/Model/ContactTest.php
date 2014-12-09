<?php
use Freshdesk\Model\Contact;

class ContactTest extends PHPUnit_Framework_TestCase
{
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
            )
        );
    }
}