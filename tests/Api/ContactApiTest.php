<?php
use Freshdesk\Model\Contact as ContactM;
use Freshdesk\Config\Connection;


class ContactApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $restMock = null;

    /**
     * @var \stdClass
     */
    protected $data = null;

    protected function setUp()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject $this ->restMock */
        $this->restMock = $this->getMockBuilder('\Freshdesk\Contact')
            ->setConstructorArgs(
                array(
                    new Connection('https://token:x@test.freshdesk.com')
                )
            )->setMethods(
                array(
                    'restCall'
                )
            )->getMock();
        $this->data = json_decode(
            trim(
                file_get_contents('./tests/_data/user.json')
            )
        );
    }

    public function testGetContactById()
    {
        $this->restMock->method('restCall')
            ->willReturn(
                json_encode(
                    $this->data
                )
            );
        $contact = new ContactM(
            $this->data
        );
        $return = $this->restMock->getContactById(
            $contact->getId()
        );
        $this->assertEquals($contact, $return);
        $blank = new ContactM();
        $blank->setId(
            $contact->getId()
        );
        $this->restMock->getContactById(
            $blank
        );
        $this->assertEquals(
            $contact,
            $blank
        );
    }

    public function testGetContact()
    {
        $this->restMock->method('restCall')
            ->willReturn(
                json_encode(
                    $this->data
                )
            );
        $contact = new ContactM(
            $this->data
        );
        $dates = array(
            'getCreatedAt'  => $contact->getCreatedAt(),
            'getUpdatedAt'  => $contact->getUpdatedAt()
        );
        /** @var ContactM $model */
        $model = $this->restMock->getContactById(
            $contact->getId()
        );
        $this->assertInstanceOf(
            get_class($contact),
            $model
        );
        $this->assertEquals($contact, $model);
        foreach ($dates as $getter => $value)
        {
            if ($value)
            {
                $this->assertInstanceOf(
                    get_class($value),
                    $model->{$getter}()
                );
                $this->assertEquals(
                    $value,
                    $model->{$getter}()
                );
                $this->assertEquals(
                    $value->format('Y-m-d H:i:s'),
                    $model->{$getter}()->format('Y-m-d H:i:s')
                );
            }
            else
            {
                $this->assertEmpty(
                    $model->{$getter}()
                );
            }
        }
   }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessageRegExp /Error: test error message/
     */
    public function testErrorResponse()
    {
        $this->restMock->method('restCall')
            ->willReturn(
                '{"errors":{"error":"test error message"}}'
            );
        $target = new ContactM(
            array(
                'id' => 1
            )
        );
        $this->restMock->getContactById($target);
    }
}