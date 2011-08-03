<?php

/**
 * Represents a 'Table' in the salesforce data
 */
class salesforce_Table extends salesforce_Base {

    /**
     * Collection of objects that describe each field in the table
     * @var array
     */
    protected $_field_descriptions = array();
    public function getFieldDescriptions($name = false) {
      if ($name !== false) {
        $name = $this->getProperFieldName($name);
        return $this->_field_descriptions[$name];
      } else {
        return $this->_field_descriptions;
      }
    }

    /**
     * List of fields that are required before create can happen
     * @var array
     */
    protected $_fields_required = array();

    /**
     * Contains a mapping of field names from lower case to the correct casing
     * @var array
     */
    protected $_field_name_mapping = array();

    /**
     * Contains the field name that is the name of the object..  err, the field
     *  used when one needs a one-line display of the object
     * @var string
     */
    protected $_name_field;

    /**
     * Contains all the loaded children for the object (lazy-loaded)
     * @var array
     */
    protected $_parents = array();

    /**
     * Mapping between relation name and child name
     * @var array
     */
    protected $_parent_relation_mapping = array();

    /**
     * Contains the actual values of fields for a tuple. 
     * @var array
     */
    protected $_field_data = array();
    public function getAllFields() { return $this->_field_data; }
    
    /**
     * Contains data about the child relations of the table
     * @var array
     */
    protected $_child_relations = array();
    public function getChildRelations() { return $this->_child_relations; }

    /**
     * Name of the SF table this object represents
     * @var string
     */
    protected $_table_name;
    public function tableName() { return $this->_table_name; }

    // Available CRUD operations on this table
    protected $_createable;
    public function canCreateRecords() { return $this->_createable; }
    protected $_queryable;
    public function canReadRecords() { return $this->_queryable; }
    protected $_updateable;
    public function canUpdateRecords() { return $this->_updateable; }
    protected $_deletable;
    public function canDeleteRecords() { return $this->_deletable; }

    //--------------------------------------------------------------------------
    // Object Methods
    //--------------------------------------------------------------------------

    /**
     * C'Tor.  Loads the meta-data from salesforce
     * @param string $table_name
     */
    public function  __construct($table_name, $meta_data = null) {
        parent::__construct();
        $this->_table_name = $table_name;
        if (!$meta_data) {
            $meta_data = $this->_requestMetadata($table_name);
        }
        $this->_loadMetadata($meta_data, $table_name);
    }

    /**
     * Request table metadata from salesforce
     * @param string $table_name
     * @return array
     */
    protected function _requestMetadata($table_name) {
        if (!isset(self::$_table_descriptions[$table_name])) {
            self::$_table_descriptions[$table_name] = $this->getConn()->describeSObject($table_name);
        }
        return self::$_table_descriptions[$table_name];
    }

    /**
     * Requests data about the table and creates the internal field definitions
     * @param array $data
     * @param string $table_name
     */
    protected function _loadMetadata($data, $table_name) {
        // Store the fields
        foreach ($data->fields as $field) {
            $this->_field_descriptions[$field->name] = $field;
            $this->_field_name_mapping[strtolower($field->name)] = $field->name;
            if ($field->createable && !$field->nillable && !$field->defaultedOnCreate) {
                $this->_fields_required[] = $field->name;
            }

            if ($field->nameField == true) {
                $this->_name_field = $field->name;
            }

            if ($field->type == 'reference') {
              $this->_parent_relation_mapping[$field->relationshipName] = $field->name;
            }
        }
        
        // Store the relations
        foreach ($data->childRelationships as $rel) {
            $this->_child_relations[$rel->childSObject] = $rel;
        }

        $this->_createable = $data->createable;
        $this->_deletable = $data->deletable;
        $this->_queryable = $data->queryable;
        $this->_updateable = $data->updateable;
    }

    /**
     * Returns an array of the field names for this table
     * @return array
     */
    public function getFieldNames($all_lower=false) {
        if ($all_lower) {
            return array_keys($this->_field_name_mapping);
        } else {
            return array_keys($this->_field_descriptions);
        }
    }

    /**
     * Returns the proper (capitalization-wise) name of a field
     * @param string $fieldname
     * @return string
     */
    public function getProperFieldName($fieldname) {
      return $this->_field_name_mapping[strtolower($fieldname)];
    }
    
    /**
     * Returns an array of table names this table is related to
     * @return array
     */
    public function getChildTableNames() {
        return array_keys($this->_child_relations);
    }

    /**
     * Returns the short displayable name for the tuple
     * @return string
     */
    public function getName() {
        return $this->__get($this->_name_field);
    }
    public function __toString() { return $this->getName(); }

    /**
     * Returns the parent tuple for relation.  NULL if not found.
     * @param string $relationName
     * @return salesforce_Table
     */
    public function getParent($relationName) {
        if (!array_key_exists($relationName, $this->_parent_relation_mapping)) {
            throw new InvalidArgumentException("Unable to find Relation '$relationName'");
        }

        if (!isset($this->_parents[$relationName])) {
            $field_name = $this->_parent_relation_mapping[$relationName];
            $reference_tos = is_array($this->getFieldDescriptions($field_name)->referenceTo) ? $this->getFieldDescriptions($field_name)->referenceTo : array($this->getFieldDescriptions($field_name)->referenceTo);
            foreach ($reference_tos as $ref) {
                try {
                    $child = new salesforce_Table($ref);
                    $child->getById($this->_field_data[strtolower($field_name)]);
                    $this->_parents[$relationName] = $child;
                    break;
                } catch (Exception $e) {}
            }
        }
        return $this->_parents[$relationName];
    }

    /**
     *
     * @param string $name
     * @return mixed
     */
    public function getDereference($name) {
        if (!in_array(strtolower($name), $this->getFieldNames(true))) {
            throw new BadMethodCallException("Attempted to get invalid field [$name]");
        }

        $field_desc = $this->getFieldDescriptions($name);
        if ($field_desc->type == 'reference') {
          $child = $this->getParent($field_desc->relationshipName);
          if ($child != null) {
            return $child->getName();
          } else {
            return '';
          }
        }

        return @$this->_field_data[strtolower($name)];
    }

    /**
     * Attempts to get this tuple's field data
     * @param string $name
     * @return mixed
     */
    public function  __get($name) {
        if (!in_array(strtolower($name), $this->getFieldNames(true))) {
            throw new BadMethodCallException("Attempted to get invalid field [$name]");
        }
        return @$this->_field_data[strtolower($name)];
    }

    /**
     * Attempts to set this tuple's field data
     * @param string $name
     * @return mixed - the field that was set
     */
    public function  __set($name, $value) {
        if (in_array(strtolower($name), $this->getFieldNames(true))) {
            return $this->_field_data[strtolower($name)] = $value;
        } else {
            throw new BadMethodCallException("Attempted to set invalid field [$name]");
        }
    }

    /**
     * Sets internal fields from array
     * @param array $values
     */
    public function fieldsFromArray($values) {
        $fields = $this->getFieldNames();
        foreach ($values as $n => $v) {
            if (in_array($n, $fields)) {
                $this->$n = $v;
            }
        }
    }

    /**
     * Validates the tuple's data and returns an array of any errors
     * @return array
     */
    public function validationErrors() {
        $ret = array();

        // Check that all required fields are set
        foreach ($this->_fields_required as $field) {
            $field = strtolower($field);
            if (!isset($this->_field_data[$field]) || empty($this->_field_data[$field])) {
                $ret[$field] = isset($ret[$field]) ?  jlarray_merge($ret[$field], array('Required Field Missing')) : array('Required Field Missing');
            }
        }
        // Done!
        return $ret;
    }

    /**
     * TRUE if the tuple is valid (and hence saveable), else FALSE
     * @see validationErrors()
     * @return boolean
     */
    public function isValid() {
        return (sizeof($this->validationErrors()) == 0);
    }

    /**
     * Attempts to fill the record fields based on Id from SF
     * @param string $id
     */
    public function getById($id) {
        if (!$this->canReadRecords()) {
            throw new InvalidArgumentException("{$this->_table_name} does not support record reads");
        }
        $fields = implode(',', $this->getFieldNames());

        $response = self::getConn()->retrieve($fields, $this->_table_name, array($id));
        if (sizeof($response) > 1) {
            throw new Exception("Too many records returned (expecting 1 - actual".sizeof($response).") for $id");
        }
        if (!is_array($response) || $response[0]->type == NULL) {
            throw new InvalidArgumentException("No records returned for [$id] on {$this->_table_name}");
        }

        $this->_loadFromResponse($response[0]);
    }
    
    /**
     * Loads the objects data from a response from SF
     * @param stdClass $response 
     */
    protected function _loadFromResponse($response) {
        // Load Self!
        $fields = $this->getFieldNames();

        foreach ($fields as $field) {
            if ($field == 'Id' && $response->Id) {
                $this->id = $response->Id; 
                continue;
            }
            $this->$field = $response->fields->$field;
        }
    }

    /**
     * Deletes a record from SF database
     */
    public function delete() {
        if (!$this->Id) {
            throw new InvalidArgumentException("Cannot delete a record with no Id!!");
        }
        if (!$this->canDeleteRecords()) {
            throw new InvalidArgumentException("{$this->_table_name} does not support record deletes");
        }

        $response = self::getConn()->delete(array($this->Id));

        if ($response->success !== true || $response->id != $this->Id) {
            $error_string = "Failed to delete {$this->Id} from {$this->_table_name} :";
            $errors = is_array($response->errors) ? $response->errors : array($response->errors);
            foreach($errors as $err) {
                $error_string .= $err->message . "\n";
            }
            throw new InvalidArgumentException($error_string);
        }

        return true;
    }

    /**
     * Saves a tuple
     * @todo this attempts to save non-saveable and invalid typed fields.  Fix!
     */
    public function save() {
        $obj = new stdclass();
        $obj->fields = array();
        $fields = $this->getFieldDescriptions();
        foreach ($fields as $name => $field) {
            if (!$field->updateable) { continue; }
            if ($field->nillable && $this->$name == '') {
                $obj->fieldsToNull[] = $name;
                continue;
            }
            $obj->fields[$name] = $this->$name;
        }
        $obj->type = $this->_table_name;

        // Are we even valid to save?
        if (!$this->isValid()) {
            throw new InvalidArgumentException("Invalid fields to create an {$this->_table_name}");
        }

        // Existing record?
        if ($this->Id) {
            $obj->Id = $this->Id;
            unset($obj->fields['id']);
            $this->_saveUpdate($obj);
        }

        // No Id, must be a new record
        else {
            $this->_saveCreate($obj);
        }

        return $this;
    }

    /**
     * Executes a save update
     */
    protected function _saveUpdate($obj) {
        // Verify we can create here
        if (!$this->canUpdateRecords()) {
            throw new InvalidArgumentException("{$this->_table_name} does not support record updates");
        }

        // Finally, create our SF representation!
        $r = self::getConn()->update(array($obj));
        if ($r->success !== true) {
            $error_string = 'ERRORS FROM SERVER: ';
            $errors = is_array($r->errors) ? $r->errors : array($r->errors);
            foreach($errors as $err) {
                $error_string .= $err->message . "\n";
            }
            throw new InvalidArgumentException($error_string);
        }
    }

    /**
     * Executes a save creation
     */
    protected function _saveCreate($obj) {
        // Verify we can create here
        if (!$this->canCreateRecords()) {
            throw new InvalidArgumentException("{$this->_table_name} does not support record creates");
        }

        // Finally, create our SF representation!
        $r = self::getConn()->create(array($obj));
        if ($r->success === true) {
            $this->Id = $r->id;
        } else {
            $error_string = 'ERRORS FROM SERVER: ';
            $errors = is_array($r->errors) ? $r->errors : array($r->errors);
            foreach($errors as $err) {
                $error_string .= $err->message . "\n";
            }
            throw new InvalidArgumentException($error_string);
        }
    }

    //--------------------------------------------------------------------------
    // Static Functions
    //--------------------------------------------------------------------------

    /**
     * Contains a lazy loaded list of table descriptions (meta-data)
     * @var array
     */
    protected static $_table_descriptions = array();

    /**
     * Gets the name of all global SF tables
     * @return array
     */
    public static function getAllTableNames() {
        $ret = array();
        $globals = self::getConn()->describeGlobal();
        foreach ($globals->sobjects as $item) {
            $ret[] = $item->name;
        }

        return $ret;
    }

    /**
     * Clears all previously loaded table descriptions;
     */
    public static function clearTableMetadata() {
        self::$_table_descriptions = array();
    }

    /**
     * Creates a new tuple in the table.  Required fields must be passed in $values
     * @param string $table_name
     * @param array $values the initial values to use
     */
    public static function create($table_name, $values) {
        $tuple = new salesforce_Table($table_name);

        $tuple->fieldsFromArray($values);

        $tuple->save();

        return $tuple;
    }
    
    /**
     * Finds all objects based on a SOQL query.  An empty array is returned if none found
     * @param string $table_name
     * @param string $query 
     */
    public static function findBySOQL($table_name, $query) {
        $ret = array();
        try {
            $results = self::getConn()->query($query);
        } catch (Exception $e) {
            if ($e->faultcode == 'sf:MALFORMED_QUERY') {
                // just ignore malformed query errors
                return $ret;
            }
            throw $e;
        }

        foreach ($results->records as $rec) {
            $obj = new salesforce_Table($table_name);
            $obj->_loadFromResponse($rec);
            $ret[] = $obj;
        }
        
        return $ret;
    }
    
    public static function findAll($table_name) {
        $obj = new salesforce_Table($table_name);
        $fields = implode("\n, ",$obj->getFieldNames());
        $query = "SELECT $fields FROM $table_name";
        return self::findBySOQL($table_name, $query);
    }
}
