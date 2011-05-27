<?php

/**
 * Represents the layout of a table in the salesforce data
 */
class salesforce_LayoutParser extends salesforce_Base {

    /**
     * The table this layout will represent
     * @var salesforce_Table $_table
     */
    protected $_table;

    /**
     *
     * @param salesforce_Table $table
     */
    public function  __construct($table) {
        parent::__construct();
        $this->_table = $table;

    }

    /**
     * Requests all layouts for a table and stores them in self::$_table_layouts
     */
    public function loadTableLayouts() {
        $table_name = $this->_table->tableName();
        $layout_definitions = $this->getConn()->describeLayout($table_name);

        $layouts = array();
        
        if(!is_object($layout_definitions)) {
            throw new Exception("Wow, something went horribly wrong getting [$table_name] layout.. ".var_export($layout_definitions,1));
        }
        
        foreach ($layout_definitions->layouts as $name => $layout) {
            $layouts[$name] = $this->parseLayout($layout);
        }
        
        return $layouts;
    }

    public function parseLayout($layout) {
        $ret = array();
        $layout = is_array($layout) ? $layout : array($layout);
        foreach($layout as $name => $section) {
            if (is_object($section)) {
                $name = isset($section->heading) ? $section->heading : 'untitled section - '.$name;
                $ret[$name] = $this->parseLayoutSection($section);
            } else {
                // This only seems to happen with some type of ID
                //   just ignore it.
            }
        }
        return $ret;
    }

    public function parseLayoutSection($section) {
        $ret = array();

        if (isset($section->layoutRows)) {
            $layoutRows = is_array($section->layoutRows) ? $section->layoutRows : array($section->layoutRows);
            foreach ($layoutRows as $row) {
                if (!isset($ret[$section->heading])) { $ret[$section->heading] = array(); }
                $ret[$section->heading]['row '.sizeof($ret[$section->heading])] = $this->parseLayoutRow($row);
            }
        }
        elseif (isset($section->columns)) {
            $layoutColumns = is_array($section->columns) ? $section->columns : array($section->columns);
            $cols = array();
            foreach ($layoutColumns as $col) {
                $cols['col# '.sizeof($cols)] = $this->parseLayoutCol($col);
            }
            $ret[$section->label] = $cols;
        }
        else {
            throw new Exception("Section without Rows Or Columns!! ".var_export($section,1));
        }

        return $ret;
    }

    public function parseLayoutCol($col) {
        return array($col->label => $col->field);
    }

    public function parseLayoutRow($row) {
        $ret = array();

        if (is_array($row)) {
            $layoutArray = $row;
            foreach ($layoutArray as $layoutItem) {
                $ret['sub row '.sizeof($ret)] = $this->parseLayoutRow($layoutItem);
            }
        }
        elseif (isset($row->layoutItems)) {
            $layoutArray = is_array($row->layoutItems) ? $row->layoutItems : array($row->layoutItems);
            foreach ($layoutArray as $layoutItem) {
                $ret['col '.sizeof($ret)] = $this->parseLayoutRow($layoutItem);
            }
        }
        else {
            if (isset($row->layoutComponents)) {
                $cmp = $this->parseLayoutComponents($row->layoutComponents);
                $cmp['editable'] = $row->editable;
                if (isset($row->label)) {
                    $ret[$row->label] = $cmp;
                } else {
                    $ret[] = $cmp;
                }
            }
        }

        return $ret;
    }

    public function parseLayoutComponents($layoutComponents) {
        $ret = array();

        $field_descriptions = $this->_table->getFieldDescriptions();

        $layoutComponents = is_array($layoutComponents) ? $layoutComponents : array($layoutComponents);
        foreach ($layoutComponents as $component) {
            switch($component->type) {
                case 'EmptySpace': //—A blank space on the page layout.
                    break;
                
                case 'Field': //—Field name. A mapping to the name field on the describeSObjectResult.
                    $cmp = array('label'=>$field_descriptions[$component->value]->label
                                ,'type'=>$field_descriptions[$component->value]->type
                                ,'field'=>$component->value);
                    if ($field_descriptions[$component->value]->picklistValues) {
                        $cmp['picklist'] = array();
                        foreach ($field_descriptions[$component->value]->picklistValues as $item) {
                            if ($item->active) {
                                $cmp['picklist'][$item->label] = $item->value;
                            }
                        }
                    }
                    $ret[] = $cmp;
                    break;
                
                case 'Separator': //—Separator character, such as a semicolon (:) or slash (/).
                    $cmp = array('type'=>'seperator'
                                ,'value'=>$component->value);
                    $ret[] = $cmp;
                    break;
                
                case 'SControl': //—Reserved for future use.
                default:
                    throw new Exception('Invalid component type found! - '.$component->type);
            }
        }

        return $ret;
    }

} // End Class