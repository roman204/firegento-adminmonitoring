<?php
class Firegento_AdminLogger_Test_Model_Observer extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @var Firegento_AdminLogger_Model_Observer
     */
    protected $_object;

    protected function setUp()
    {
        parent::setUp();
        $this->_object = Mage::getModel('firegento_adminlogger/observer');

        // set admin/session
        $this->_setAdminSession();
    }

    /**
     * set admin session with session
     */
    protected function _setAdminSession()
    {
        $sessionMock = $this->getModelMockBuilder('admin/session')
            ->disableOriginalConstructor() // This one removes session_start and other methods usage
            ->setMethods(null) // Enables original methods usage, because by default it overrides all methods
            ->getMock();
        $this->replaceByMock('singleton', 'admin/session', $sessionMock);
    }

    /**
     * Create event to pass into observer method
     *
     * @param array $data
     */
    protected function _createObserver(array $data)
    {
        $event = new Varien_Event($data);
        $data['event'] = $event;
        $observer = new Varien_Event_Observer();
        return $observer->setData($data);
    }

    /**
     * if Firegento_AdminLogger_Model_History is saved, don't do anything inside the observer
     */
    public function testNoHistorySaves()
    {
        $object = new Firegento_AdminLogger_Model_History();
        $data = array('object' => $object);
        $observer = $this->_createObserver($data);

        $mock = $this->getModelMock('firegento_adminlogger/history');
        $mock->expects($this->never())->method('save');
        $this->replaceByMock('model', 'firegento_adminlogger/history', $mock);

        Mage::getModel('firegento_adminlogger/observer')->modelSaveAfter($observer);
    }

    public function testHistorySavesWithCustomer()
    {
        $customer = new Mage_Customer_Model_Customer();
        $customer->setEmail('test@example.com');
        $customer->setFirstname('Johnny');
        $customer->setLastname('Doels');
        $customer->setPassword('1234567890');

        $data = array('object' => $customer);
        $observer = $this->_createObserver($data);

        $mock = $this->getModelMock('firegento_adminlogger/history');
        $mock->expects($this->once())->method('save');
        $this->replaceByMock('model', 'firegento_adminlogger/history', $mock);

        Mage::getModel('firegento_adminlogger/observer')->modelSaveAfter($observer);
    }
}