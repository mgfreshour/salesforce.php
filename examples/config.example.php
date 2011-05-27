<?php
define('FS_APP_ROOT', realpath(dirname(__FILE__) . '/..'));
require_once(FS_APP_ROOT.'/lib/salesforce/Base.php');
require_once(FS_APP_ROOT.'/lib/salesforce/Table.php');
require_once(FS_APP_ROOT.'/lib/salesforce/TableLayout.php');
require_once(FS_APP_ROOT.'/lib/salesforce/LayoutParser.php');

$config = array(
  'salesforce_username'=>'username@example.com'
, 'salesforce_password'=>'password123!'
, 'salesforce_token'=>'TOKENTOKENTOKEN'
);
