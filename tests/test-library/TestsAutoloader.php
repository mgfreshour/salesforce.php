<?php

require_once 'Zend/Loader/Autoloader/Interface.php';
class TestsAutoloader implements Zend_Loader_Autoloader_Interface
{
    public function autoload ($class_name)
    {
        $possible_base_paths = array( FS_APP_ROOT . '/lib/'
                                    , FS_APP_ROOT . '/htdocs/'
                                    , FS_APP_ROOT . '/tests/test-library/');

        foreach ($possible_base_paths as $base_path) {
            $path_parts = explode('_', $class_name);

            $classFileName = array_pop($path_parts);
            if (sizeof($path_parts) > 0) {
                $classPath = $base_path . implode(DIRECTORY_SEPARATOR, $path_parts) . DIRECTORY_SEPARATOR . $classFileName . '.php';
            } else {
                $classPath = $base_path . $classFileName . '.php';
            }

            if (file_exists($classPath)) {
                include_once $classPath;
                break;
            }
        }
    }
}