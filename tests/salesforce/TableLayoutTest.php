<?php

class testable_salesforce_TableLayout extends salesforce_TableLayout { 
    protected function getParser() {
        return isset($this->parser) ? $this->parser : parent::getParser();
    }
    
    public function call($function, $arg1=null, $arg2=null, $arg3=null, $arg4=null, $arg5=null) {
        return $this->$function($arg1, $arg2, $arg3, $arg4, $arg5);
    }
    
    public static function setLayout($table_name, $layout_name, $value) { 
        self::$_table_layouts[$table_name][$layout_name] = $value;
    }
}

class salesforce_TableLayoutTest extends salesforce_TableTestAbstract {
    var $layout_describes = array();

    public function setUp() {
        salesforce_Base::setSession(array());
        $this->conn = $this->getMock('SforcePartnerClient');
        salesforce_TableLayout::clearAllLayouts();
        salesforce_Table::clearTableMetadata();
        
        $this->table_name = 'Event';
        $this->setupTableData($this->table_name);
        $this->setupLayoutData($this->table_name);

        $this->table = new salesforce_Table($this->table_name);
        $this->testee = new testable_salesforce_TableLayout($this->table);
    }
    
    protected function _testComponentDisplay($cmp, $editable, $value, $expected) {
        // Given
        $function = "_get{$cmp['type']}ComponentDisplay";
        
        // When
        $ret = $this->testee->call($function, $cmp, $value, $editable);
        
        // Then
        $this->assertEquals($expected, $ret);
    }


    //--------------------------------------------------------------------------
    // Tests
    //--------------------------------------------------------------------------
	/*
    public function test_something() {
		$outfile = '../temp.html';
        $this->table->description = 'description value';
        $this->table->subject = 'Email';
        $this->table->startdatetime = '2011-11-05 05:52:33';
        $this->testee->setTable($this->table);
        
        file_put_contents($outfile, date('Y-m-d h:i:s').' '.print_r($this->testee->getTableLayoutNames(),1));
        $style = "<style>
                    table { border: solid 1px black; width: 700px; }
                    label { text-align: right; width:150px; padding-right: 10px; display:inline-block; float: left; }
                    input { float:left; }
                    textarea { width: 500px; height: 80px; }
                  </style>";
        error_log($style, 3, $outfile);
        
        $ret = $this->testee->getLayoutDisplay("detailLayoutSections");
        error_log("<h1>detailLayoutSections</h1><hr />\n", 3, $outfile);
        error_log("$ret\n", 3, $outfile);
        
        error_log("<hr />\n", 3, $outfile);
        
        error_log("<h1>editLayoutSections</h1><hr />\n", 3, $outfile);
        $ret = $this->testee->getLayoutDisplay("editLayoutSections");
        error_log("$ret\n", 3, $outfile);
        
        //$this->assertEquals('Hello world', $ret);
    }
	*/
    
    public function test_LayoutDisplay_SunnyDay() {
        $testee = $this->getMock('testable_salesforce_TableLayout', array('getLayoutRowDisplay'), array($this->table));
        
        $testee->expects($this->exactly(3))
               ->method('getLayoutRowDisplay')
               ->will($this->onConsecutiveCalls('<tr><td>One</td></tr>', '<tr><td>Two</td></tr>', '<tr><td>Three</td></tr>'));
        
        $layout = array( 'section 1' => array('row 1'=>array(1), 'row 2'=>array(2), 'row 3'=>array(3)));
        testable_salesforce_TableLayout::setLayout('Event', 'test_layout', $layout);
        
        $ret = $testee->getLayoutDisplay('test_layout');
        
        $expected = '<table><tr><th colspan="88">section 1</th></tr><tr><td>One</td></tr><tr><td>Two</td></tr><tr><td>Three</td></tr></table>';
        
        $this->assertEquals($expected, $ret);
    }

    public function test_LayoutRowDisplay_SingleColumn() {
        $testee = $this->getMock('testable_salesforce_TableLayout', array('getLayoutColumnDisplay'), array($this->table));
        
        $testee->expects($this->exactly(3))
               ->method('getLayoutColumnDisplay')
               ->will($this->onConsecutiveCalls('<td>Col 1</td>', '<td>Col 2</td>', '<td>Col 3</td>'));
        
        $row =array ('col 0' => array ( 'Label 1' => array(0=>'Cmp 1', 'editable' => true) )
                    ,'col 1' => array ( 'Label 2' => array(0=>'Cmp 2', 'editable' => true) )
                    ,'col 2' => array ( 'Label 3' => array(0=>'Cmp 3', 'editable' => true) )
                    );
        $ret = $testee->getLayoutRowDisplay($row);
        
        $expected = '<tr><td>Col 1</td><td>Col 2</td><td>Col 3</td></tr>';
        
        $this->assertEquals($expected, $ret);
    }
    
    public function test_LayoutColumnDisplay_SingleComponent() {
        $col = array ('columnLabel' => array ( array ( 'label' => 'fieldLabel', 'type' => 'textarea', 'field'=>'Description' ),
                                               'editable' => true
                     ));
        
        $this->table->description = 'description value';
        $this->testee->setTable($this->table);
        $ret = $this->testee->getLayoutColumnDisplay($col);
        
        $expected = '<td><label for="Description">fieldLabel</label><textarea name="Description">description value</textarea></td>';
        
        $this->assertEquals($expected, $ret);
    }
    
    public function test_LayoutColumnDisplay_ThreeComponent() {
        $col = array ('columnLabel' => array ( array ( 'label' => 'fieldOneLabel', 'type' => 'string', 'field'=>'Description' ),
                                               array ( 'type' => 'seperator', 'value'=>' SEP ' ),
                                               array ( 'label' => 'fieldTwoLabel', 'type' => 'datetime', 'field'=>'Location' ),
                                               'editable' => true
                     ));
        
        $this->table->description = 'description value';
        $this->table->location = 'location value';
        $this->testee->setTable($this->table);
        $ret = $this->testee->getLayoutColumnDisplay($col);
        
        $expected = '<td>'
                   .'<label for="Description">fieldOneLabel</label><input type="textbox" name="Description" value="description value" />'
                   .' SEP '
                   .'<label for="Location">fieldTwoLabel</label><input type="textbox" name="Location" value="location value" />'
                   .'</td>';
        $this->assertEquals($expected, $ret);
    }
    
    public function test_SeperatorDisplay() {
        $cmp = array ( 'type' => 'seperator', 'value'=>' SEP ' );        
        $expected = ' SEP ';
        $value = 'Test Value';
        $this->_testComponentDisplay($cmp, true, $value, $expected);
        $this->_testComponentDisplay($cmp, false, $value, $expected);
    }

    public function test_StringDisplay() {
        $cmp = array ( 'label' => 'fieldOneLabel', 'type' => 'string', 'field'=>'TestFieldName' );
        $value = 'Test Value';
        
        $expected = '<label for="TestFieldName">fieldOneLabel</label><input type="textbox" name="TestFieldName" value="Test Value" />';
        $this->_testComponentDisplay($cmp, true, $value, $expected);
        
        $expected = 'fieldOneLabel : Test Value';
        $this->_testComponentDisplay($cmp, false, $value, $expected);
    }

    public function test_BooleanDisplay() {
        $cmp = array ( 'label' => 'fieldOneLabel', 'type' => 'boolean', 'field'=>'TestFieldName' );
        $value = 'true';
        
        $expected = '<label for="TestFieldName">fieldOneLabel</label><input type="checkbox" value="true" name="TestFieldName" checked="checked" />';
        $this->_testComponentDisplay($cmp, true, 'true', $expected);
        
        $expected = '<label for="TestFieldName">fieldOneLabel</label><input type="checkbox" value="true" name="TestFieldName" />';
        $this->_testComponentDisplay($cmp, true, 'false', $expected);
        
        $expected = '<label for="TestFieldName">fieldOneLabel</label><input type="checkbox" value="true" name="TestFieldName" checked="checked" readonly="true" disabled="disabled" />';
        $this->_testComponentDisplay($cmp, false, 'true', $expected);
        
        $expected = '<label for="TestFieldName">fieldOneLabel</label><input type="checkbox" value="true" name="TestFieldName" readonly="true" disabled="disabled" />';
        $this->_testComponentDisplay($cmp, false, 'false', $expected);
    }


    public function test_IntDisplay() {
        $cmp = array ( 'label' => 'fieldOneLabel', 'type' => 'int', 'field'=>'TestFieldName' );
        $value = '12';
        
        $expected = '<label for="TestFieldName">fieldOneLabel</label><input type="textbox" name="TestFieldName" value="'.$value.'" />';
        $this->_testComponentDisplay($cmp, true, $value, $expected);
        
        $expected = 'fieldOneLabel : '.$value;
        $this->_testComponentDisplay($cmp, false, $value, $expected);
    }

    public function test_DoubleDisplay() {
        $cmp = array ( 'label' => 'fieldOneLabel', 'type' => 'double', 'field'=>'TestFieldName' );
        $value = '12.01';
        
        $expected = '<label for="TestFieldName">fieldOneLabel</label><input type="textbox" name="TestFieldName" value="'.$value.'" />';
        $this->_testComponentDisplay($cmp, true, $value, $expected);
        
        $expected = 'fieldOneLabel : '.$value;
        $this->_testComponentDisplay($cmp, false, $value, $expected);
    }

    public function test_DateDisplay() {
        $cmp = array ( 'label' => 'fieldOneLabel', 'type' => 'date', 'field'=>'TestFieldName' );
        $value = '2005-11-05';
        
        $expected = '<label for="TestFieldName">fieldOneLabel</label><input type="textbox" name="TestFieldName" value="'.$value.'" />';
        $this->_testComponentDisplay($cmp, true, $value, $expected);
        
        $expected = 'fieldOneLabel : '.$value;
        $this->_testComponentDisplay($cmp, false, $value, $expected);
    }

    public function test_DateTimeDisplay() {
        $cmp = array ( 'label' => 'fieldOneLabel', 'type' => 'datetime', 'field'=>'TestFieldName' );
        $value = '2005-11-05 12:57:08';
        
        $expected = '<label for="TestFieldName">fieldOneLabel</label><input type="textbox" name="TestFieldName" value="'.$value.'" />';
        $this->_testComponentDisplay($cmp, true, $value, $expected);
        
        $expected = 'fieldOneLabel : '.$value;
        $this->_testComponentDisplay($cmp, false, $value, $expected);
    }

    public function test_CurrencyDisplay() {
        $cmp = array ( 'label' => 'fieldOneLabel', 'type' => 'currency', 'field'=>'TestFieldName' );
        $value = '$57.02';
        
        $expected = '<label for="TestFieldName">fieldOneLabel</label><input type="textbox" name="TestFieldName" value="'.$value.'" />';
        $this->_testComponentDisplay($cmp, true, $value, $expected);
        
        $expected = 'fieldOneLabel : '.$value;
        $this->_testComponentDisplay($cmp, false, $value, $expected);
    }
    
    public function test_TextAreaDisplay() {
        $cmp = array ( 'label' => 'fieldOneLabel', 'type' => 'textarea', 'field'=>'TestFieldName' );
        $value = '$57.02';
        
        $expected = '<label for="TestFieldName">fieldOneLabel</label><textarea name="TestFieldName">'.$value.'</textarea>';
        $this->_testComponentDisplay($cmp, true, $value, $expected);
        
        $expected = 'fieldOneLabel : '.$value;
        $this->_testComponentDisplay($cmp, false, $value, $expected);
    }

    public function test_PercentDisplay() {
        $cmp = array ( 'label' => 'fieldOneLabel', 'type' => 'percent', 'field'=>'TestFieldName' );
        $value = '57';
        
        $expected = '<label for="TestFieldName">fieldOneLabel</label><input type="textbox" name="TestFieldName" value="'.$value.'" />';
        $this->_testComponentDisplay($cmp, true, $value, $expected);
        
        $expected = 'fieldOneLabel : '.$value;
        $this->_testComponentDisplay($cmp, false, $value, $expected);
    }

    public function test_PhoneDisplay() {
        $cmp = array ( 'label' => 'fieldOneLabel', 'type' => 'phone', 'field'=>'TestFieldName' );
        $value = '(555) 867-5309';
        
        $expected = '<label for="TestFieldName">fieldOneLabel</label><input type="textbox" name="TestFieldName" value="'.$value.'" />';
        $this->_testComponentDisplay($cmp, true, $value, $expected);
        
        $expected = 'fieldOneLabel : '.$value;
        $this->_testComponentDisplay($cmp, false, $value, $expected);
    }

    public function test_UrlDisplay() {
        $cmp = array ( 'label' => 'fieldOneLabel', 'type' => 'url', 'field'=>'TestFieldName' );
        $value = 'http://www.google.com';
        
        $expected = '<label for="TestFieldName">fieldOneLabel</label><input type="textbox" name="TestFieldName" value="'.$value.'" />';
        $this->_testComponentDisplay($cmp, true, $value, $expected);
        
        $expected = 'fieldOneLabel : '.$value;
        $this->_testComponentDisplay($cmp, false, $value, $expected);
    }

    public function test_EmailDisplay() {
        $cmp = array ( 'label' => 'fieldOneLabel', 'type' => 'email', 'field'=>'TestFieldName' );
        $value = 'bobo@clowns.cc';
        
        $expected = '<label for="TestFieldName">fieldOneLabel</label><input type="textbox" name="TestFieldName" value="'.$value.'" />';
        $this->_testComponentDisplay($cmp, true, $value, $expected);
        
        $expected = 'fieldOneLabel : '.$value;
        $this->_testComponentDisplay($cmp, false, $value, $expected);
    }
    
    public function test_ComboboxDisplay() {
        $cmp = array ( 'label' => 'fieldOneLabel'
                      ,'type' => 'combobox'
                      ,'field'=> 'TestFieldName'
                      ,'picklist' => array (
                                        'Option One' => 'Value One',
                                        'Option Two' => 'Value Two',
                                        'Option Three' => 'Value Three' ) );
        $value = 'Value Two';
        
        $expected = '<label for="TestFieldName">fieldOneLabel</label>'
                   .'<select name="TestFieldName">'
                     .'<option value="Value One">Option One</option>'
                     .'<option value="Value Two" selected="selected">Option Two</option>'
                     .'<option value="Value Three">Option Three</option>'
                   .'</select>';
        
        $this->_testComponentDisplay($cmp, true, $value, $expected);
        
        $expected = 'fieldOneLabel : Option Two';
        $this->_testComponentDisplay($cmp, false, $value, $expected);
    }
    
    public function test_PicklistDisplay() {
        $cmp = array ( 'label' => 'fieldOneLabel'
                      ,'type' => 'picklist'
                      ,'field'=> 'TestFieldName'
                      ,'picklist' => array (
                                        'Option One' => 'Value One',
                                        'Option Two' => 'Value Two',
                                        'Option Three' => 'Value Three' ) );
        $value = 'Value Two';
        
        $expected = '<label for="TestFieldName">fieldOneLabel</label>'
                   .'<select name="TestFieldName">'
                     .'<option value="Value One">Option One</option>'
                     .'<option value="Value Two" selected="selected">Option Two</option>'
                     .'<option value="Value Three">Option Three</option>'
                   .'</select>';
        
        $this->_testComponentDisplay($cmp, true, $value, $expected);
        
        $expected = 'fieldOneLabel : Option Two';
        $this->_testComponentDisplay($cmp, false, $value, $expected);
    }
    
    public function test_MultiPicklistDisplay() {
        $cmp = array ( 'label' => 'fieldOneLabel'
                      ,'type' => 'multipicklist'
                      ,'field'=> 'TestFieldName'
                      ,'picklist' => array (
                                        'Option One' => 'Value One',
                                        'Option Two' => 'Value Two',
                                        'Option Three' => 'Value Three' ) );
        $value = 'Value Two';
        
        $expected = '<label for="TestFieldName">fieldOneLabel</label>'
                   .'<select name="TestFieldName" multiple="multiple">'
                     .'<option value="Value One">Option One</option>'
                     .'<option value="Value Two" selected="selected">Option Two</option>'
                     .'<option value="Value Three">Option Three</option>'
                   .'</select>';
        
        $this->_testComponentDisplay($cmp, true, $value, $expected);
        
        $expected = 'fieldOneLabel : Option Two';
        $this->_testComponentDisplay($cmp, false, $value, $expected);
    }
    
    public function test_AnyTypeDisplay() {
        $cmp = array ( 'label' => 'fieldOneLabel', 'type' => 'anytype', 'field'=>'TestFieldName' );
        $value = 'fewq fsaf lkjdsaf dsa';
        
        $expected = '<label for="TestFieldName">fieldOneLabel</label><input type="textbox" name="TestFieldName" value="'.$value.'" />';
        $this->_testComponentDisplay($cmp, true, $value, $expected);
        
        $expected = 'fieldOneLabel : '.$value;
        $this->_testComponentDisplay($cmp, false, $value, $expected);
    }
    
    public function test_base64Display() {
        $cmp = array ( 'label' => 'fieldOneLabel', 'type' => 'base64', 'field'=>'TestFieldName' );
        $value = 'fewq fsaf lkjdsaf dsa';
        
        try {
            $this->_testComponentDisplay($cmp, true, $value, '');
            $this->fail('Expected an Exception!');
        } catch (InvalidArgumentException $e) {
            $this->assertContains('Base64 Components dont allow Edit!', $e->getMessage());
        }
        
        $expected = 'fieldOneLabel : '.$value;
        $this->_testComponentDisplay($cmp, false, $value, $expected);
    }
    
    public function test_IDDisplay() {
        $cmp = array ( 'label' => 'fieldOneLabel', 'type' => 'id', 'field'=>'TestFieldName' );
        $value = 'fewq fsaf lkjdsaf dsa';
        
        try {
            $this->_testComponentDisplay($cmp, true, $value, '');
            $this->fail('Expected an Exception!');
        } catch (InvalidArgumentException $e) {
            $this->assertContains('ID Components dont allow Edit!', $e->getMessage());
        }
        
        $expected = 'fieldOneLabel : '.$value;
        $this->_testComponentDisplay($cmp, false, $value, $expected);
    }
    
    public function test_ReferenceDisplay() {
        $cmp = array ( 'label' => 'fieldOneLabel', 'type' => 'reference', 'field'=>'TestFieldName' );
        $value = 'fewq fsaf lkjdsaf dsa';
        
//        try {
//            $this->_testComponentDisplay($cmp, true, $value, '');
//            $this->fail('Expected an Exception!');
//        } catch (InvalidArgumentException $e) {
//            $this->assertContains('Reference Components dont allow Edit!', $e->getMessage());
//        }
        
        $expected = 'fieldOneLabel : '.$value;
        $this->_testComponentDisplay($cmp, false, $value, $expected);
    }
}
