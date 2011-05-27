<?php

class testable_salesforce_Base extends salesforce_Base {}

class salesforce_BaseTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        $this->username = 'username@example.com';
        $this->password = 'password123!';
        $this->security_token = 'TOKENTOKENTOKEN';
        $this->wsdl = FS_APP_ROOT.'/lib/third_party/salesforce/soapclient/partner.wsdl.xml';

        $this->location = 'LocationReturn';
        $this->session = 'SessionReturn';
        $this->sessionData = array();
    }

    public function test__CorrectCredentialsPassed_ObjectConstructed() {
        $conn = $this->getMock('SforcePartnerClient');
        $conn->expects($this->once())
             ->method('createConnection')
             ->with($this->equalTo($this->wsdl));
        $conn->expects($this->once())
             ->method('login')
             ->with($this->equalTo($this->username), $this->equalTo($this->password.$this->security_token));
			 
		salesforce_Base::setCredentials($this->username, $this->password, $this->security_token);
        salesforce_Base::setConn($conn);

        $testee = new testable_salesforce_Base(array('session'=>$this->sessionData));
    }

    public function test__SessionVariablesSet_SuccessfulLogin() {
        $conn = $this->getMock('SforcePartnerClient');
        $conn->expects($this->once())
             ->method('getLocation')
             ->will($this->returnValue($this->location));
        $conn->expects($this->once())
             ->method('getSessionId')
             ->will($this->returnValue($this->session));
        salesforce_Base::setConn($conn);


        $testee = new testable_salesforce_Base(array('session'=>$this->sessionData));

        $this->assertEquals($this->location, $testee->getSessionData('salesforce_location'));
        $this->assertEquals($this->session, $testee->getSessionData('salesforce_sessionId'));
        $this->assertEquals($this->wsdl, $testee->getSessionData('salesforce_wsdl'));
    }

    public function test__StoredLoginInfoUsed() {
        $this->sessionData['salesforce_location'] = $this->location;
        $this->sessionData['salesforce_sessionId'] = $this->session;
        $this->sessionData['salesforce_wsdl'] = $this->wsdl;

        $conn = $this->getMock('SforcePartnerClient');
        $conn->expects($this->once())
             ->method('createConnection')
             ->will($this->returnValue($this->wsdl));
        $conn->expects($this->once())
             ->method('setEndpoint')
             ->will($this->returnValue($this->location));
        $conn->expects($this->once())
             ->method('setSessionHeader')
             ->will($this->returnValue($this->session));
        $conn->expects($this->never())
             ->method('login');
        salesforce_Base::setConn($conn);

        $testee = new testable_salesforce_Base(array('session'=>$this->sessionData));
    }

    public function test__LoginIsTried_StoredLoginInfoThrowsInvalidSessionException() {
        $this->sessionData['salesforce_location'] = $this->location;
        $this->sessionData['salesforce_sessionId'] = $this->session;
        $this->sessionData['salesforce_wsdl'] = $this->wsdl;


        $conn = $this->getMock('SforcePartnerClient');
        $e = new Exception('Blah Blah Blah');
        $e->faultcode = 'sf:INVALID_SESSION_ID';
        $conn->expects($this->exactly(2))
             ->method('createConnection')
             ->will($this->returnValue($this->wsdl));
        $conn->expects($this->once())
             ->method('setEndpoint')
             ->will($this->returnValue($this->location));
        $conn->expects($this->once())
             ->method('setSessionHeader')
             ->will($this->throwException($e));
        $conn->expects($this->once())
             ->method('login');
        salesforce_Base::setConn($conn);

        $testee = new testable_salesforce_Base(array('session'=>$this->sessionData));
    }
}
