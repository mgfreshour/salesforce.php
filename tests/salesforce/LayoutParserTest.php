<?php

class salesforce_LayoutParserTest extends salesforce_TableTestAbstract {
    var $layout_describes = array();

    public function setUp() {
        salesforce_Base::setSession(array());
        $this->conn = $this->getMock('SforcePartnerClient');
        salesforce_Base::setConn($this->conn);
    }
    
    protected function getMockTable() {
        $field_descriptions = array();
        $field_descriptions['TestFieldOne'] = $this->array2object(array('label'=>'Label 1', 'type'=>'string'));
        $field_descriptions['TestFieldTwo'] = $this->array2object(array('label'=>'Label 2', 'type'=>'datetime'));
        
        $picklistValues = array();
        $picklistValues[] = $this->array2object(array('active' => true,'defaultValue' => false,'label' => 'Option One','value' => 'Value One',));
        $picklistValues[] = $this->array2object(array('active' => true,'defaultValue' => false,'label' => 'Option Two','value' => 'Value Two',));
        $picklistValues[] = $this->array2object(array('active' => true,'defaultValue' => false,'label' => 'Option Three','value' => 'Value Three',));
        $picklistValues[] = $this->array2object(array('active' => false,'defaultValue' => false,'label' => 'Option Off','value' => 'Value Off',));
        $field_descriptions['TestFieldThree'] = $this->array2object(array('label'=>'Label 3', 'type'=>'picklist', 'picklistValues'=>$picklistValues));

        $table = $this->getMock('salesforce_Table', array(), array(), '', false);
        $table->expects($this->any())
               ->method('getFieldDescriptions')
               ->will($this->returnValue($field_descriptions));
        
        return $table;
    }


    //--------------------------------------------------------------------------
    // Tests
    //--------------------------------------------------------------------------
//    public function test_Everything() {
//        $table_name = 'Event';
//        $this->setupTableData($table_name);
//        $this->setupLayoutData($table_name);
//        
//        $table = new salesforce_Table($table_name);
//        
//        $testee = new salesforce_LayoutParser($table);
//        $ret = $testee->loadTableLayouts();
//    }
    
    public function test_ParseComponent_Picklist() {
        // Given
        $table = $this->getMockTable();
        
        $components = array ();
        $components[] = $this->array2object(array('type'=>'Field', 'value'=>'TestFieldThree'));

        // When
        $testee = new salesforce_LayoutParser($table);
        $ret = $testee->parseLayoutComponents($components);
        
        // Then
        $expected = array (array (
                                    'label' => 'Label 3',
                                    'type' => 'picklist',
                                    'field' => 'TestFieldThree',
                                    'picklist' => array
                                    (
                                        'Option One' => 'Value One',
                                        'Option Two' => 'Value Two',
                                        'Option Three' => 'Value Three'
                                    )
                                  )
                            );

        $this->assertEquals($expected, $ret);
    }
    
    public function test_ParseComponent_SunnyDay() {
        // Given
        $table = $this->getMockTable();
        
        $components = array ();
        $components[] = $this->array2object(array('type'=>'Field', 'value'=>'TestFieldOne'));
        $components[] = $this->array2object(array('type'=>'Separator', 'value'=>','));
        $components[] = $this->array2object(array('type'=>'Field', 'value'=>'TestFieldTwo'));

        // When
        $testee = new salesforce_LayoutParser($table);
        $ret = $testee->parseLayoutComponents($components);
        
        // Then
        $expected = array (0 =>
                                  array (
                                    'label' => 'Label 1',
                                    'type' => 'string',
                                    'field' => 'TestFieldOne'
                                  ),
                            1 => array (
                                    'type' => 'seperator',
                                    'value' => ','
                                ),
                            2 =>
                                  array (
                                    'label' => 'Label 2',
                                    'type' => 'datetime',
                                    'field' => 'TestFieldTwo'
                                  )
                            );

        $this->assertEquals($expected, $ret);
    }
    
    public function test_parseLayoutRow_SingleRowMultiColumns() {
        // Given
        // mock out the parseLayoutComponents function but leave everything else as normal
        $testee = $this->getMock('salesforce_LayoutParser', array('parseLayoutComponents'), array(null));
        
        $testee->expects($this->exactly(3))
               ->method('parseLayoutComponents')
               ->will($this->onConsecutiveCalls(array('Cmp 1'), array('Cmp 2'), array('Cmp 3')));
        
        // When
        $row = new stdClass();
        $row->layoutItems = array();
        $row->layoutItems[] = $this->array2object(array('editable' => true, 'label' => 'Label 1', 'layoutComponents' => '' ));
        $row->layoutItems[] = $this->array2object(array('editable' => true, 'label' => 'Label 2', 'layoutComponents' => '' ));
        $row->layoutItems[] = $this->array2object(array('editable' => true, 'label' => 'Label 3', 'layoutComponents' => '' ));
        
        $ret = $testee->parseLayoutRow($row);
        
        
        // Then
        $expected = array ('col 0' => array ( 'Label 1' => array(0=>'Cmp 1', 'editable' => true) )
                          ,'col 1' => array ( 'Label 2' => array(0=>'Cmp 2', 'editable' => true) )
                          ,'col 2' => array ( 'Label 3' => array(0=>'Cmp 3', 'editable' => true) )
                          );
        $this->assertEquals($expected, $ret);
    }
    
    public function test_parseLayoutRow_SubRows() {
        // Given
        // mock out the parseLayoutComponents function but leave everything else as normal
        $testee = $this->getMock('salesforce_LayoutParser', array('parseLayoutComponents'), array(null));
        
        $testee->expects($this->exactly(3))
               ->method('parseLayoutComponents')
               ->will($this->onConsecutiveCalls(array('Cmp 1'), array('Cmp 2'), array('Cmp 3')));
        
        // When
        $row = array();
        $layoutItems = array();
        $layoutItems[] = $this->array2object(array('editable' => true, 'label' => 'Row 1 Label 1', 'layoutComponents' => '' ));
        $layoutItems[] = $this->array2object(array('editable' => true, 'label' => 'Row 1 Label 2', 'layoutComponents' => '' ));
        $row[] = $this->array2object(array('layoutItems'=>$layoutItems));
        $layoutItems = array();
        $layoutItems[] = $this->array2object(array('editable' => true, 'label' => 'Row 2 Label 1', 'layoutComponents' => '' ));
        $row[] = $this->array2object(array('layoutItems'=>$layoutItems));
        
        $ret = $testee->parseLayoutRow($row);
        
        
        // Then
        $expected = array(  'sub row 0' => array( 'col 0' => array( 'Row 1 Label 1' => array( 0 => 'Cmp 1', 'editable' => true ) )
                                                 ,'col 1' => array( 'Row 1 Label 2' => array( 0 => 'Cmp 2', 'editable' => true ) )
                         )
                           ,'sub row 1' => array( 'col 0' => array( 'Row 2 Label 1' => array( 0 => 'Cmp 3', 'editable' => true ) )
                         ));       

        $this->assertEquals($expected, $ret);
    }
}