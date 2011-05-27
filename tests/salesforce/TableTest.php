<?php

class salesforce_TableTest extends salesforce_TableTestAbstract {
    protected function getEventExampleFields() {
        return array('WhoId' => '', 'WhatId' => '', 'Subject' => 'adsf', 'Location' => '', 'IsAllDayEvent' => 'false', 'ActivityDateTime' => '2011-04-05T19:00:00.000Z',
         'ActivityDate' => '2011-04-05', 'DurationInMinutes' => '60', 'StartDateTime' => '2011-04-05T19:00:00.000Z', 'EndDateTime' => '2011-04-05T20:00:00.000Z',
         'Description' => 'qwersadf', 'AccountId' => '', 'OwnerId' => '005E0000000QGFaIAO', 'IsPrivate' => 'false', 'ShowAs' => 'Busy', 'IsDeleted' => 'false',
         'IsChild' => 'false', 'IsGroupEvent' => 'false', 'GroupEventType' => '0', 'CreatedDate' => '2011-04-05T18:37:04.000Z', 'CreatedById' => '005E0000000QGFaIAO',
         'LastModifiedDate' => '2011-04-05T18:37:04.000Z', 'LastModifiedById' => '005E0000000QGFaIAO', 'SystemModstamp' => '2011-04-05T18:37:04.000Z', 'IsArchived' => 'false',
         'RecurrenceActivityId' => '', 'IsRecurrence' => 'false', 'RecurrenceStartDateTime' => '', 'RecurrenceEndDateOnly' => '', 'RecurrenceTimeZoneSidKey' => '',
         'RecurrenceType' => '', 'RecurrenceInterval' => '', 'RecurrenceDayOfWeekMask' => '', 'RecurrenceDayOfMonth' => '', 'RecurrenceInstance' => '',
         'RecurrenceMonthOfYear' => '', 'ReminderDateTime' => '2011-04-05T18:45:00.000Z', 'IsReminderSet' => 'true');
    }
    //--------------------------------------------------------------------------
    // Tests
    //--------------------------------------------------------------------------
    public function test__ReturnsCorrectNames_StaticGetNamesIsCalled() {
        $this->conn = $this->getMock('SforcePartnerClient');
        $this->conn->expects($this->once())
                   ->method('describeGlobal')
                   ->will($this->returnValue($this->getDescribeGlobalReturn()));
        salesforce_Base::setConn($this->conn);

        $table_names = salesforce_Table::getAllTableNames();

        $expected_return = array('Account'
                                ,'ApexTrigger'
                                ,'CampaignMember'
                                ,'CaseTeamTemplate'
                                ,'ContactShare'
                                ,'DashboardFeed'
                                ,'Event'
                                ,'FeedTrackedChange'
                                ,'LeadStatus'
                                ,'OpportunityLineItem'
                                ,'ProcessInstance'
                                ,'Site'
                                ,'UserFeed');
        $this->assertEquals($expected_return, $table_names);
    }

    public function test__TableDescriptionIsRequestedFromSalesForce_OnConstruction() {
        $table_name = 'Event';

        $this->conn->expects($this->once())
                   ->method('describeSObject')
                   ->with($this->equalTo($table_name))
                   ->will($this->returnValue($this->getDescribeTableReturn($table_name)));
        salesforce_Base::setConn($this->conn);

        $testee = new salesforce_Table($table_name);
    }
    
    public function test__RelationMetaDataPopulated_OnConstruction() {
        $table_name = 'Event';

        $this->conn->expects($this->once())
                   ->method('describeSObject')
                   ->will($this->returnValue($this->getDescribeTableReturn($table_name)));
        salesforce_Base::setConn($this->conn);

        $testee = new salesforce_Table($table_name);
        
        $rels = $testee->getChildTableNames();
        $expected = array( 'Attachment', 'ContentVersion', 'EntitySubscription', 'Event', 'EventAttendee', 'EventFeed', 'FeedComment'
                          ,'FeedPost', 'NewsFeed', 'UserProfileFeed');
        
        $this->assertEquals($expected, $rels);
    }

    public function test__FieldMetaDataPopulated_OnConstruction() {
        $table_name = 'Event';

        $this->conn->expects($this->once())
                   ->method('describeSObject')
                   ->will($this->returnValue($this->getDescribeTableReturn($table_name)));
        salesforce_Base::setConn($this->conn);

        $testee = new salesforce_Table($table_name);

        $fields = $testee->getFieldNames();
        $expected_return = array(
             'Id','WhoId','WhatId','Subject','Location','IsAllDayEvent','ActivityDateTime','ActivityDate','DurationInMinutes','StartDateTime'
            ,'EndDateTime','Description','AccountId','OwnerId','IsPrivate','ShowAs','IsDeleted','IsChild'
            ,'IsGroupEvent','GroupEventType','CreatedDate','CreatedById','LastModifiedDate','LastModifiedById','SystemModstamp','IsArchived'
            ,'RecurrenceActivityId','IsRecurrence','RecurrenceStartDateTime','RecurrenceEndDateOnly','RecurrenceTimeZoneSidKey','RecurrenceType'
            ,'RecurrenceInterval','RecurrenceDayOfWeekMask','RecurrenceDayOfMonth','RecurrenceInstance','RecurrenceMonthOfYear','ReminderDateTime'
            ,'IsReminderSet');

        $this->assertEquals($expected_return, $fields);
        $this->assertEquals($table_name, $testee->tableName());
        $this->assertSame(true, $testee->canCreateRecords());
        $this->assertSame(true, $testee->canReadRecords());
        $this->assertSame(true, $testee->canUpdateRecords());
        $this->assertSame(true, $testee->canDeleteRecords());
    }

    public function test__MetaDataRequestedOnlyOnce_MultipleObjectsCreated() {
        $table_name = 'Event';

        $this->conn->expects($this->once())
                   ->method('describeSObject')
                   ->with($this->equalTo($table_name))
                   ->will($this->returnValue($this->getDescribeTableReturn($table_name)));
        salesforce_Base::setConn($this->conn);

        $testee  = new salesforce_Table($table_name);
        $testee2 = new salesforce_Table($table_name);
        $testee3 = new salesforce_Table($table_name);
    }

    public function test__ExceptionThrown_InvalidFieldAccess() {
        $table_name = 'Event';
        $this->setupTableData($table_name);

        $testee  = new salesforce_Table($table_name);

        try {
            $testee->bobo_the_clown = 'Is Awesome';
            $this->fail('Expected Exception!!');
        } catch (BadMethodCallException $e) { }

        try {
            $who_is_awesome = $testee->bobo_the_clown;
            $this->fail('Expected Exception!!');
        } catch (BadMethodCallException $e) { }
    }

    public function test__MagicGetAndSetWork_ExactFieldNames() {
        $table_name = 'Event';
        $this->setupTableData($table_name);

        $testee  = new salesforce_Table($table_name);

        $testee->Subject = 'Testing';
        $return = $testee->Subject;
        $this->assertEquals('Testing', $return);
    }

    public function test__MagicGetAndSetWork_WronglyCapitalizedNames() {
        $table_name = 'Event';
        $this->setupTableData($table_name);

        $testee  = new salesforce_Table($table_name);

        $testee->SubJecT = 'Testing';
        $return = $testee->subject;
        $this->assertEquals('Testing', $return);
    }

    public function test__TupleIsMarkedInvalid_Empty() {
        $table_name = 'Account';
        $this->setupTableData($table_name);

        $testee  = new salesforce_Table($table_name);

        $this->assertFalse($testee->isValid());
    }

    public function test__TupleIsMarkedInvalid_OneRequiredFieldIsMissing() {
        $table_name = 'Lead';
        $this->setupTableData($table_name);

        $testee  = new salesforce_Table($table_name);
        $testee->LastName = 'Bobo';
        //$testee->Company = 'Clown Inc.';

        $this->assertFalse($testee->isValid());
    }

    public function test_TupleIsValid_AllRequiredFieldsAreSet() {
        $table_name = 'Lead';
        $this->setupTableData($table_name);

        $testee  = new salesforce_Table($table_name);
        $testee->LastName = 'Bobo';
        $testee->Company = 'Clown Inc.';

        $this->assertTrue($testee->isValid());
    }

    public function test_ObjectPropertiesSet_WhenCreating() {
        $table_name = 'Lead';
        $this->setupTableData($table_name);

        $return = new stdclass();
        $return->success = true;
        $return->id = '00QE00000012CYjMAM';
        $this->conn->expects($this->once())
                   ->method('create')
                   ->will($this->returnValue($return));
        salesforce_Base::setConn($this->conn);

        $fields = array('LastName'=>'Bobo', 'Company'=>'Clown Inc.');
        $testee = salesforce_Table::create($table_name, $fields);

        $this->assertEquals('Bobo', $testee->lastname);
        $this->assertEquals('Clown Inc.', $testee->company);
        $this->assertEquals('00QE00000012CYjMAM', $testee->id);
    }

    public function test_ExceptionThrown_CreateCalledWithMissingRequiredFields() {
        $table_name = 'Lead';
        $this->setupTableData($table_name);

        try {
            $fields = array('LastName'=>'Bobo');
            $testee = salesforce_Table::create($table_name, $fields);
            $this->fail('Exception not thrown!');
        } catch (Exception $e) {
            $this->assertEquals('Invalid fields to create an Lead', $e->getMessage());
        }
    }

    public function test_ExceptionThrown_CreateCalledAndServerErrors() {
        $table_name = 'Lead';
        $this->setupTableData($table_name);

        $return = new stdclass();
        $return->success = false;
        $return->errors = new stdclass();
        $return->errors->fields = 'Company';
        $return->errors->message = 'Required fields are missing: [Company]';
        $return->errors->statusCode = 'REQUIRED_FIELD_MISSING';
        $this->conn->expects($this->once())
                   ->method('create')
                   ->will($this->returnValue($return));
        salesforce_Base::setConn($this->conn);

        try {
            $fields = array('LastName'=>'Bobo', 'Company'=>'Clown Inc.');
            $testee = salesforce_Table::create($table_name, $fields);
            $this->fail('Exception not thrown!');
        } catch (Exception $e) {
            $this->assertContains('Required fields are missing: [Company]', $e->getMessage());
            $this->assertContains('ERRORS FROM SERVER:', $e->getMessage());
        }
    }


    public function test_CallsCreateAndIdIsSet_WhenSavingNewRecord() {
        $table_name = 'Lead';
        $this->setupTableData($table_name);

        $return = new stdclass();
        $return->success = true;
        $return->id = '00QE00000012CYjMAM';
        $this->conn->expects($this->once())
                   ->method('create')
                   ->will($this->returnValue($return));
        salesforce_Base::setConn($this->conn);


        $testee  = new salesforce_Table($table_name);
        $testee->LastName = 'Bobo';
        $testee->Company = 'Clown Inc.';
        $testee->save();

        $this->assertEquals('Bobo', $testee->lastname);
        $this->assertEquals('Clown Inc.', $testee->company);
        $this->assertEquals('00QE00000012CYjMAM', $testee->id);

    }

    public function test_CallsUpdate_WhenSavingExistingRecord() {
        $table_name = 'Lead';
        $this->setupTableData($table_name);
        
        $return = new stdclass();
        $return->success = true;
        $this->conn->expects($this->once())
                   ->method('update')
                   ->will($this->returnValue($return));
        salesforce_Base::setConn($this->conn);


        $testee  = new salesforce_Table($table_name);
        $testee->LastName = 'Bobo';
        $testee->Company = 'Clown Inc.';
        $testee->Id = '00QE00000012CYjMAM';
        $testee->save();

        $this->assertEquals('Bobo', $testee->lastname);
        $this->assertEquals('Clown Inc.', $testee->company);
        $this->assertEquals('00QE00000012CYjMAM', $testee->id);
    }

    public function test_CorrectObjectCreated_WhenGetByIdCalled() {
        $table_name = 'Lead';
        $this->setupTableData($table_name);
        
        $id = '00QE00000012CYjMAM';
        $last_name = 'BoboTheClown';
        $company = 'Clown School';

        $testee = new salesforce_Table($table_name);

        $return = new stdclass();
        $return->type = $table_name;
        $return->Id = $id;
        $return->fields = new stdclass();
        $field_names = $testee->getFieldNames();
        foreach($field_names as $field) {
            $return->fields->$field = '';
        }
        $return->fields->LastName = $last_name;
        $return->fields->Company = $company;
        $return = array($return);


        $this->conn->expects($this->once())
                   ->method('retrieve')
                   ->with(  $this->equalTo(implode(',', $testee->getFieldNames()))
                          , $this->equalTo($table_name)
                          , $this->equalTo(array ($id))
                          )
                   ->will($this->returnValue($return));
        salesforce_Base::setConn($this->conn);

        $testee->getById($id);

        $this->assertEquals($last_name, $testee->lastname);
        $this->assertEquals($company, $testee->company);
        $this->assertEquals($id, $testee->id);
    }

    public function test_ExceptionThrown_GetByIdFindsNothing() {
        $table_name = 'Lead';
        $this->setupTableData($table_name);

        $id = '00QE00000012CYjMAM';

        $testee = new salesforce_Table($table_name);

        $return = new stdclass();
        $return->type = NULL;
        $return->fields = NULL;
        $return = array($return);


        $this->conn->expects($this->once())
                   ->method('retrieve')
                   ->with(  $this->equalTo(implode(',', $testee->getFieldNames()))
                          , $this->equalTo($table_name)
                          , $this->equalTo(array ($id))
                          )
                   ->will($this->returnValue($return));
        salesforce_Base::setConn($this->conn);

        try {
            $testee->getById($id);
            $this->fail('Exception not thrown!');
        } catch (Exception $e) {
            $this->assertContains('No records returned', $e->getMessage());
        }
    }

    public function test_ExceptionThrown_WhenDeleteCalledOnRecordWithoutId() {
        $table_name = 'Lead';
        $this->setupTableData($table_name);

        $testee = new salesforce_Table($table_name);

        try {
            $testee->delete();
            $this->fail('Exception not thrown!');
        } catch (Exception $e) {
            $this->assertContains('Cannot delete a record with no Id', $e->getMessage());
        }
    }

    public function test_ExceptionThrown_WhenDeleteFailsAtSF() {
        $table_name = 'Lead';
        $this->setupTableData($table_name);

        $id = '00QE00000013CYjMAM';

        $return = new stdclass();
        $return->success = false;
        $return->id = NULL;
        $return->errors = new stdclass();
        $return->errors->message = 'invalid cross reference id';
        $return->errors->statusCode = 'INVALID_CROSS_REFERENCE_KEY';
        $this->conn->expects($this->once())
                   ->method('delete')
                   ->with($this->equalTo(array ($id)))
                   ->will($this->returnValue($return));
        salesforce_Base::setConn($this->conn);

        $testee = new salesforce_Table($table_name);
        $testee->Id = $id;

        try {
            $testee->delete();
            $this->fail('Exception not thrown!');
        } catch (Exception $e) {
            $this->assertContains("Failed to delete $id", $e->getMessage());
        }
    }

    public function test_Success_WhenDeleteSucceeds() {
        $table_name = 'Lead';
        $this->setupTableData($table_name);

        $id = '00QE00000012CYjMAM';

        $return = new stdclass();
        $return->success = true;
        $return->id = $id;

        $this->conn->expects($this->once())
                   ->method('delete')
                   ->with($this->equalTo(array ($id)))
                   ->will($this->returnValue($return));
        salesforce_Base::setConn($this->conn);


        $testee = new salesforce_Table($table_name);
        $testee->Id = $id;

        $ret = $testee->delete();
        $this->assertTrue($ret);
    }
    
    public function test__FindBySOQL_SingleReturn() {
        // Given
        $table_name = 'Event';
        $this->setupTableData($table_name);
        
        $query =  "TEST QUERY LIKE A SELECT ... FROM EVENT WHERE ID='00UE0000000zNoCMAU'";
        
        $fields = $this->array2object($this->getEventExampleFields());
        $record = $this->array2object(array('type' => 'Event', 'fields' =>$fields, 'Id' => '00UE0000000zNoCMAU'));
        $return = $this->array2object(array('records' => array($record)));
        $this->conn->expects($this->once())
                   ->method('query')
                   ->with($this->equalTo($query))
                   ->will($this->returnValue($return));
        salesforce_Base::setConn($this->conn);

        // When
        $ret = salesforce_Table::findBySOQL($table_name, $query);
        
        // Then
        $expected = array();
        $fields_array = $this->getEventExampleFields();
        foreach ($fields_array as $name => $val) {
            $expected[strtolower($name)] = $val;
        }
        $expected['id'] = '00UE0000000zNoCMAU';
        $this->assertEquals(1, sizeof($ret));
        $this->assertEquals($expected, $ret[0]->getAllFields());
    }
    
    public function test__FindBySOQL_MultiReturn() {
        // Given
        $table_name = 'Event';
        $this->setupTableData($table_name);
        
        $query =  "TEST QUERY LIKE A SELECT ... FROM EVENT WHERE Something=SomethingElse";
        
        $fields1 = $this->getEventExampleFields();
        $fields1['Subject'] = 'Record 1 Subject';
        $record1 = $this->array2object(array('type' => 'Event', 'fields' =>$this->array2object($fields1), 'Id' => '10UE0000000zNoCMAU'));
        $fields2 = $this->getEventExampleFields();
        $fields2['Subject'] = 'Record 2 Subject';
        $record2 = $this->array2object(array('type' => 'Event', 'fields' =>$this->array2object($fields1), 'Id' => '20UE0000000zNoCMAU'));
        $fields3 = $this->getEventExampleFields();
        $fields3['Subject'] = 'Record 3 Subject';
        $record3 = $this->array2object(array('type' => 'Event', 'fields' =>$this->array2object($fields1), 'Id' => '30UE0000000zNoCMAU'));
        $return = $this->array2object(array('records' => array($record1, $record2, $record3)));
        $this->conn->expects($this->once())
                   ->method('query')
                   ->with($this->equalTo($query))
                   ->will($this->returnValue($return));
        salesforce_Base::setConn($this->conn);

        // When
        $ret = salesforce_Table::findBySOQL($table_name, $query);
        
        // Then
        $this->assertEquals(3, sizeof($ret));
        
        $expected = array();
        foreach ($fields1 as $name => $val) {
            $expected[strtolower($name)] = $val;
        }
        $expected['id'] = '10UE0000000zNoCMAU';
        $this->assertEquals($expected, $ret[0]->getAllFields());
        
        $expected = array();
        foreach ($fields1 as $name => $val) {
            $expected[strtolower($name)] = $val;
        }
        $expected['id'] = '20UE0000000zNoCMAU';
        $this->assertEquals($expected, $ret[1]->getAllFields());
        
        $expected = array();
        foreach ($fields1 as $name => $val) {
            $expected[strtolower($name)] = $val;
        }
        $expected['id'] = '30UE0000000zNoCMAU';
        $this->assertEquals($expected, $ret[2]->getAllFields());
    }
    
    public function test__FindBySOQL_NoReturns() {
        // Given
        $table_name = 'Event';
        //$this->setupTableData($table_name); // since no objs are created, no setup is needed
        
        $query =  "TEST QUERY LIKE A SELECT ... FROM EVENT WHERE Something=SomethingElse";

        $return = $this->array2object(array('records' => array()));
        $this->conn->expects($this->once())
                   ->method('query')
                   ->with($this->equalTo($query))
                   ->will($this->returnValue($return));
        salesforce_Base::setConn($this->conn);

        // When
        $ret = salesforce_Table::findBySOQL($table_name, $query);
        
        // Then
        $this->assertEquals(0, sizeof($ret));
    }
    
    public function test__FindBySOQL_HandlesMalformedQueries() {
        // Given
        $table_name = 'Event';
        //$this->setupTableData($table_name); // since no objs are created, no setup is needed
        
        $query =  "TEST QUERY LIKE A SELECT ... FROM EVENT WHERE Something=SomethingElse";

        
        $e = new Exception('Blah Blah Blah');
        $e->faultcode = 'sf:MALFORMED_QUERY';
        $this->conn->expects($this->once())
                   ->method('query')
                   ->with($this->equalTo($query))
                   ->will($this->throwException($e));
        salesforce_Base::setConn($this->conn);

        // When
        $ret = salesforce_Table::findBySOQL($table_name, $query);
        
        // Then
        $this->assertEquals(0, sizeof($ret));
    }
}
