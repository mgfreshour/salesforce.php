<?php

// Include the library classes
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

define('FS_APP_ROOT', realpath(dirname(__FILE__) . '/..'));
 
set_include_path(implode(PATH_SEPARATOR, array(
   '/usr/share' ,
   FS_APP_ROOT.'/lib',
   FS_APP_ROOT.'/mobile/library',
   FS_APP_ROOT.'/tests/test-library',
   get_include_path()
)));
 
 
require_once 'Zend/Loader/Autoloader.php';
require_once 'TestsAutoloader.php';
 
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->pushAutoloader(new TestsAutoloader());


// Try to reduce some of the cruft in the logs
error_reporting(E_ALL ^ E_NOTICE);