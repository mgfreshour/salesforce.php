<?php

abstract class salesforce_TableTestAbstract extends PHPUnit_Framework_TestCase {
    public $table_describes = array();

    public function setUp() {
        salesforce_Base::setSession(array());
        salesforce_Table::clearTableMetadata();
        salesforce_TableLayout::clearAllLayouts();
        $this->conn = $this->getMock('SforcePartnerClient');
    }

    
    function array2object($array) {
        if (is_array($array)) {
            $obj = new StdClass();

            foreach ($array as $key => $val){
                $obj->$key = $val;
            }
        }
        else { $obj = $array; }

        return $obj;
    }

    protected function getDescribeLayoutReturn($table_name) {
        $table_name = strtolower($table_name);
        if (!isset($this->layout_describes[$table_name])) {
            $this->layout_describes[$table_name] = unserialize(file_get_contents(FS_APP_ROOT."/tests/data/salesforce/{$table_name}_table_layout.txt"));
        }
        return $this->layout_describes[$table_name];
    }

    protected function setupLayoutData($table_names) {

        $table_names = is_array($table_names) ? $table_names : array($table_names);

        foreach($table_names as $table_name) {
            $this->conn->expects($this->any())
                       ->method('describeLayout')
                       ->with($this->equalTo($table_name))
                       ->will($this->returnValue($this->getDescribeLayoutReturn($table_name)));
        }

        salesforce_Base::setConn($this->conn);
    }


    protected function getDescribeGlobalReturn() {
        if (!isset($this->describe_global_return)) {
            $this->describe_global_return = unserialize(file_get_contents(FS_APP_ROOT.'/tests/data/salesforce/globals_describe.txt'));
        }

        return $this->describe_global_return;
    }

    protected function getDescribeTableReturn($table_name) {
        $table_name = strtolower($table_name);
        if (!isset($this->table_describes[$table_name])) {
            $this->table_describes[$table_name] = unserialize(file_get_contents(FS_APP_ROOT."/tests/data/salesforce/{$table_name}_table_describe.txt"));
        }
        return $this->table_describes[$table_name];
    }

    protected function setupTableData($table_names) {

        $table_names = is_array($table_names) ? $table_names : array($table_names);

        foreach($table_names as $table_name) {
            $this->conn->expects($this->any())
                       ->method('describeSObject')
                       ->with($this->equalTo($table_name))
                       ->will($this->returnValue($this->getDescribeTableReturn($table_name)));
        }

        salesforce_Base::setConn($this->conn);
    }
}