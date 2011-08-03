<?php
require('./config.php');

try {
// Configure salesforce connections
salesforce_Base::setCredentials($config['salesforce_username'], $config['salesforce_password'], $config['salesforce_token']);

// Create the controller
$ctl = new salesforceExplorerController();
$ctl->run($_REQUEST);
} catch (Exception $e) {
    echo '<h1>', $e->getMessage(), '</h1>', '<a href="?">Back to Home</a>';
}
echo '<br /><br /><br />';
var_dump($_REQUEST);

class salesforceExplorerController {
    function run($params) {
        $action = $params['action'];
        switch ($action) {
            case 'describe':
                $this->describeAction($params);
                break;
            case 'layout':
                $this->layoutAction($params);
                break;
            case 'browse':
                $this->browseAction($params);
                break;
            case 'view':
                $this->viewAction($params);
                break;
            case 'edit':
                $this->editAction($params);
                break;
            case 'save':
                $this->saveAction($params);
                break;
            default:
                $this->indexAction($params);
                break;
        }
    }
    
    /**
     *
     * @param string $url
     */
    protected function _redirect($url) {
        if ($url[0] != '/') {
            $url = $_SERVER['PHP_SELF'].$url;
            header("Location: $url");
        } else {
            header("Location:$url");
        }
    }
    
    function tpl($name) {
        include("salesforce_explorer/$name.tpl");
    }
    
    function saveAction($params) {
        $table = new salesforce_Table($params['table']);
        $table->getById($params['id']);
        $table->fieldsFromArray($params);
        try {
            $table->save();
            $this->_redirect('?action=view&id='.$params['id'].'&table='.$params['table']);
        } catch (Exception $e) {
            echo '<h2>ERROR : '.$e->getMessage().'</h2><a href="?">Back to Home</a>';
        }
    }
    
    function editAction($params) {
        $table = new salesforce_Table($params['table']);
        $table->getById($params['id']);
        $layout = new salesforce_TableLayout($table);
        $this->tplData['edit_layout'] = $layout->getLayoutDisplay('editLayoutSections');
        $this->tplData['id'] = $params['id'];
        $this->tplData['table'] = $params['table'];
        $this->tpl('edit');
    }
    
    function viewAction($params) {
        $table = new salesforce_Table($params['table']);
        $table->getById($params['id']);
        $layout = new salesforce_TableLayout($table);
        $this->tplData['detail_layout'] = $layout->getLayoutDisplay('detailLayoutSections');
        $this->tplData['table'] = $params['table'];
        $this->tpl('view');
    }
    
    function browseAction($params) {
        $table = new salesforce_Table($params['table']);
        $this->tplData['table'] = $params['table'];
        $this->tplData['field_headers'] = $table->getFieldNames();
        $this->tplData['tuples'] = salesforce_Table::findAll($params['table']);
        $this->tpl('browse');
    }
    
    function layoutAction($params) {
        try {
            $table = new salesforce_Table($params['table']);
            $layout = new salesforce_TableLayout($table);
            $this->tplData['detail_layout'] = $layout->getLayoutDisplay("detailLayoutSections");
            $this->tplData['edit_layout'] = $layout->getLayoutDisplay("editLayoutSections");
        $this->tplData['table'] = $params['table'];
            $this->tpl('layout');
        } catch (Exception $e) {
            echo '<h2>ERROR : '.$e->getMessage().'</h2><a href="?">Back to Home</a>';
        }
        
    }
    
    function describeAction($params) {
        $table = new salesforce_Table($params['table']);
        $this->tplData['fields'] = $table->getFieldDescriptions();
        $this->tplData['relations'] = $table->getChildRelations();
        $this->tplData['table'] = $params['table'];
        $this->tpl('describe');
    }
    
    function indexAction($params) {
        $this->tplData['tables'] = salesforce_Table::getAllTableNames();
        $this->tpl('index');
    }
}