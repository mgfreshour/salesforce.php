<?php

/**
 * Represents the layout of a table in the salesforce data
 */
class salesforce_TableLayout extends salesforce_Base {

    /**
     * The table this layout will represent
     * @var salesforce_Table $_table
     */
    protected $_table;
    public function setTable(salesforce_Table $table) { $this->_table = $table; }
    
    /**
     *
     * @var array
     */
    protected $_layouts;

    /**
     * 
     * @param salesforce_Table $table
     */
    public function  __construct(salesforce_Table $table, $layouts=false) {
        parent::__construct();
        $this->_table = $table;
        
        if (!$layouts) {
            $layouts = $this->loadTableLayouts();
        }
        $this->_layouts = $layouts;
        
    }
    
    /**
     * Lazy loads (one-time) the layouts for a table from SF
     * @return array
     */
    public function loadTableLayouts() {
        $table_name = $this->_table->tableName();

        if (!isset(self::$_table_layouts[$table_name])) {
            $parser = $this->getParser();
            self::$_table_layouts[$table_name] = $parser->loadTableLayouts();
        }
        return self::$_table_layouts[$table_name];
    }
    
    /**
     * Gets the names of the layouts contained in this object.. sort of..
     * @return array
     */
    public function getTableLayoutNames() {
        $table_name = $this->_table->tableName();
        
        return array_keys(self::$_table_layouts[$table_name]);
    }
    
    /**
     * Just an overridable function for the parser dependency
     * @return salesforce_LayoutParser 
     */
    protected function getParser() {
        return new salesforce_LayoutParser($this->_table);
    }
    
    /**
     * Creates the display of a component
     * @param array $component
     * @param mixed $value 
     */
    public function getComponentDisplay($component, $value, $display_value, $editable) {
        $method_name = "_get{$component['type']}ComponentDisplay";
        if (method_exists($this, $method_name)) {
            return $this->$method_name($component, $value, $display_value, $editable);
        } else {
            //throw new InvalidArgumentException("Unknown Component Type [{$component['type']}]".var_export($component,1));
        }
    }
    
    /**
     * 
     * @param array $col
     */
    public function getLayoutColumnDisplay($col) {
        $ret = '<td>';
        
        foreach($col as $label => $data) {
            //$ret .= $label;
            $editable = $data['editable'];
            foreach ($data as $cmp) {
                if (is_array($cmp)) {
                    $value = $display_value = null;
                    if (isset($cmp['field'])) {
                      $field_desc = $this->_table->getFieldDescriptions($cmp['field']);
                      $value = $display_value = $this->_table->{$cmp['field']};
                      if ($field_desc->type == 'reference') {
                          $display_value = $this->_table->getDereference($cmp['field']);
                      }
                    }
                    $ret .= $this->getComponentDisplay($cmp, $value, $display_value, $editable);
                }
            }
        }
        
        $ret .= '</td>';
        
        return $ret;
    }
    
    /**
     *
     * @param array $row 
     */
    public function getLayoutRowDisplay($row) {
        $ret = '<tr>';
        foreach($row as $col) {
            $ret .= $this->getLayoutColumnDisplay($col);
        }
        $ret .= '</tr>';
        return $ret;
    }

    /**
     *
     * @param string $section 
     */
    public function getLayoutDisplay($layout_name) {
        $ret = '';
        $sections = $this->getLayout($this->_table->tableName(), $layout_name);
        foreach ($sections as $name => $section) {
            $ret .= '<table><tr><th colspan="88">'.$name.'</th></tr>';
            foreach ($section as $blah => $rows) {
                foreach ($rows as $row) {
                    $ret .= $this->getLayoutRowDisplay($row);
                }
            }
            $ret .= '</table>';
        }
        
        return $ret;
    }
    
    /**
     * String values.
     */
    protected function _getSeperatorComponentDisplay($component, $value, $display_value, $editable) {
        return $component['value'];
    }
    /**
     * String values.
     */
    protected function _getStringComponentDisplay($component, $value, $display_value, $editable) {
        if ($editable) {
            $ret = '<label for="'.$component['field'].'">'.$component['label'].'</label><input type="textbox" name="'.$component['field'].'" value="'.$display_value.'" />';
        } else {
            $ret = $component['label'].' : '.$display_value;
        }
        return $ret;
    }
    /**
     * Boolean (true / false) values.
     */
    protected function _getBooleanComponentDisplay($component, $value, $display_value, $editable) {

        $ret = '<label for="'.$component['field'].'">'.$component['label'].'</label>'
              .'<input type="checkbox" value="true" name="'.$component['field'].'" '.($value == 'true' ? 'checked="checked" ' : '').(!$editable ? 'readonly="true" disabled="disabled" ' : '').'/>';

        return $ret;
    }
    /**
     * Integer values.
     */
    protected function _getIntComponentDisplay($component, $value, $display_value, $editable) {
        return $this->_getStringComponentDisplay($component, $value, $display_value, $editable);
    }
    /**
     * Double values.
     */
    protected function _getDoubleComponentDisplay($component, $value, $display_value, $editable) {
        return $this->_getStringComponentDisplay($component, $value, $display_value, $editable);

    }
    /**
     * Date values.
     */
    protected function _getDateComponentDisplay($component, $value, $display_value, $editable) {
        return $this->_getStringComponentDisplay($component, $value, $display_value, $editable);
    }
    /**
     * Date and time values.
     */
    protected function _getDatetimeComponentDisplay($component, $value, $display_value, $editable) {
        return $this->_getStringComponentDisplay($component, $value, $display_value, $editable);
    }
    /**
     * Base64-encoded arbitrary binary data (of type base64Binary). Used for Attachment, Document, and Scontrol objects.
     */
    protected function _getBase64ComponentDisplay($component, $value, $display_value, $editable) {
        if ($editable) {
            throw new InvalidArgumentException('Base64 Components dont allow Edit!');
        }
        return $this->_getStringComponentDisplay($component, $value, $display_value, $editable);
    }
    /**
     * Primary key field for the object. For information on IDs, see ID Field Type.
     */
    protected function _getIDComponentDisplay($component, $value, $display_value, $editable) {
        if ($editable) {
            throw new InvalidArgumentException('ID Components dont allow Edit!');
        }
        return $this->_getStringComponentDisplay($component, $value, $display_value, $editable);
    }
    /**
     * Cross-references to a different object. Analogous to a foreign key field in SQL.
     */
    protected function _getReferenceComponentDisplay($component, $value, $display_value, $editable) {
        if ($editable) {
            //throw new InvalidArgumentException('Reference Components dont allow Edit!');
        }
        return $this->_getStringComponentDisplay($component, $value, $display_value, false /*$editable*/);
    }
    /**
     * Currency values.
     */
    protected function _getCurrencyComponentDisplay($component, $value, $display_value, $editable) {
        return $this->_getStringComponentDisplay($component, $value, $display_value, $editable);
    }
    /**
     * String that is displayed as a multiline text field.
     */
    protected function _getTextAreaComponentDisplay($component, $value, $display_value, $editable) {
        if ($editable) {
            $ret = '<label for="'.$component['field'].'">'.$component['label'].'</label><textarea name="'.$component['field'].'">'.$value.'</textarea>';
        } else {
            $ret = $component['label'].' : '.$value;
        }
        return $ret;
    }
    /**
     * Percentage values.
     */
    protected function _getPercentComponentDisplay($component, $value, $display_value, $editable) {
        return $this->_getStringComponentDisplay($component, $value, $display_value, $editable);
    }
    /**
     * Phone numbers. Values can include alphabetic characters. Client applications are responsible for phone number formatting.
     */
    protected function _getPhoneComponentDisplay($component, $value, $display_value, $editable) {
        return $this->_getStringComponentDisplay($component, $value, $display_value, $editable);
    }
    /**
     * URL values. Client applications should commonly display these as hyperlinks.
     */
    protected function _getUrlComponentDisplay($component, $value, $display_value, $editable) {
        return $this->_getStringComponentDisplay($component, $value, $display_value, $editable);
    }
    /**
     * Email addresses.
     */
    protected function _getEmailComponentDisplay($component, $value, $display_value, $editable) {
        return $this->_getStringComponentDisplay($component, $value, $display_value, $editable);
    }
    /**
     * Comboboxes, which provide a set of enumerated values and allow the user to specify a value not in the list.
     */
    protected function _getComboboxComponentDisplay($component, $value, $display_value, $editable, $multiple=false) {
        if ($editable) {
            $ret = '<label for="'.$component['field'].'">'.$component['label'].'</label>'
                    .'<select name="'.$component['field'].'"'.($multiple ? ' multiple="multiple"' : '').'>';
            foreach ($component['picklist'] as $label => $option_value) {
                $ret .= '<option value="'.$option_value.'"'.($value==$option_value ? ' selected="selected"' : '').'>'.$label.'</option>';
            } 
            $ret .= '</select>';
        } else {
            $ret = $component['label'].' : ';
            foreach ($component['picklist'] as $label => $option_value) {
                $ret .= ($value==$option_value ? $label : '');
            } ;
        }
        return $ret;
    }
    /**
     * Single-select picklists, which provide a set of enumerated values from which only one value can be selected.
     */
    protected function _getPicklistComponentDisplay($component, $value, $display_value, $editable) {
        return $this->_getComboboxComponentDisplay($component, $value, $display_value, $editable);
    }
    /**
     * multi-select picklists, which provide a set of enumerated values from which multiple values can be selected.
     */
    protected function _getMultiPicklistComponentDisplay($component, $display_value, $value, $editable) {
        return $this->_getComboboxComponentDisplay($component, $value, $display_value, $editable, true);
    }
    /**
     * Values can be any of these types: string, picklist, boolean, int, double, percent, ID, date, dateTime, url, or email.
     */
    protected function _getAnyTypeComponentDisplay($component, $value, $display_value, $editable) {
        return $this->_getStringComponentDisplay($component, $value, $display_value, $editable);
    }


    //--------------------------------------------------------------------------
    // Static Stuff
    //--------------------------------------------------------------------------
    /**
     * Contains all previously loaded table layouts (to be referenced when needed
     *  rather than reloaded at each need)
     * @var array
     */
    protected static $_table_layouts = array();
    public static function getAllLayouts() { return self::$_table_layouts; }
    public static function getLayout($table_name, $layout_name) { 
        if (isset(self::$_table_layouts[$table_name][$layout_name])) {
            return self::$_table_layouts[$table_name][$layout_name];
        } else {
            throw new InvalidArgumentException("Unknown Layout [$table_name -> $layout_name]".var_export(self::$_table_layouts,1));
        }
    }
    public static function clearAllLayouts() { self::$_table_layouts = array(); }

} // End Class