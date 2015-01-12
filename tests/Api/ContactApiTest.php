<?php
use Freshdesk\Model\Contact as ContactM;
use Freshdesk\Config\Connection;
use Freshdesk\Contact;
use Freshdesk\Rest;


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
            ->disableOriginalConstructor()
            ->setMethods(
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

    /**
     * @dataProvider configlessProvider
     * @expectedException RuntimeException
     * @expectedExceptionMessageRegExp /No connection config available/
     * @param string $section
     */
    public function testConfiglessConstructor($section)
    {
        Rest::GetSection($section);
    }

    /**
     * @return array
     */
    public function configlessProvider()
    {
        return array(
            array(Rest::SECTION_REST),
            array(Rest::SECTION_TICKET),
            array(Rest::SECTION_CONTACT)
        );
    }

    /**
     * @dataProvider badSectionProvider
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp /Unkown section "[^"]*", use SECTION_\* constants/
     * @param string $section
     */
    public function testBadSections($section)
    {
        Rest::GetSection($section);
    }

    /**
     * @return array
     */
    public function badSectionProvider()
    {
        return array(
            array('fpo'),
            array('bar'),
            array('')
        );
    }

    /**
     * @depends testConfiglessConstructor
     */
    public function testConstructor()
    {
        $connection = new Connection('https://valid:pass@test.freshdesk.com');
        $contact = new Contact($connection);
        $sectionGetters = $this->configlessProvider();
        $sectionGetters = array_pop($sectionGetters);
        foreach ($sectionGetters as $section)
        {
            $section = Rest::GetSection($section);
            if ($section instanceof Contact)
                $this->assertEquals($contact, $section);
            $configs = $this->getConnection(
                $section,
                $contact
            );
            $this->assertEquals(
                $configs['expected'],
                $configs['instance']
            );
            $this->assertEquals(
                $configs['instance']->getBaseUrl(),
                $connection->getBaseUrl()
            );
            $this->assertEquals(
                $configs['expected']->getBaseUrl(),
                $connection->getBaseUrl()
            );
            $this->assertEquals(
                $configs['expected']->getDomain(),
                $connection->getDomain()
            );
            $this->assertEquals(
                $configs['instance']->getDomain(),
                $connection->getDomain()
            );
            $this->assertEquals(
                $configs['instance']->getScheme(),
                $connection->getScheme()
            );
            $this->assertEquals(
                $configs['expected']->getScheme(),
                $connection->getScheme()
            );
            $this->assertEquals(
                $configs['instance']->getUserName(),
                $connection->getUserName()
            );
            $this->assertEquals(
                $configs['expected']->getUserName(),
                $connection->getUserName()
            );
            $this->assertEquals(
                $configs['expected']->getPassword(),
                $connection->getPassword()
            );
            $this->assertEquals(
                $configs['instance']->getPassword(),
                $connection->getPassword()
            );
        }
    }

    /**
     * @param Rest $section
     * @param Rest $expected
     * @return array
     */
    protected function getConnection(Rest $section, Rest $expected)
    {
        $reflection = new \ReflectionClass('\Freshdesk\Rest');
        $reflectionConfig = $reflection->getProperty('config');
        $reflectionConfig->setAccessible(true);
        return array(
            'instance'  => $reflectionConfig->getValue($section),
            'expected'  => $reflectionConfig->getValue($expected)
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
