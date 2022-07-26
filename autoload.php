<?php

/**
 * Loads all files found in a given folder.
 * Calls itself recursively for all sub folders.
 *
 * @param string $dir
 */
if( ! function_exists('require_files_of_folder')){
    function require_files_of_folder($dir){
        foreach (new DirectoryIterator($dir) as $fileInfo) {
            if (!$fileInfo->isDot()) {
                if ($fileInfo->isDir()) {
                    requireFilesOfFolder($fileInfo->getPathname());
                } else {
                    require_once $fileInfo->getPathname();
                }
            }
        }
    }
}


/**
 * for debuging purposes  
 */

if( ! function_exists('write_log')){
    function write_log($log){
        if(is_array($log) || is_object($log)){
            error_log(" WP_Plugin --> ");
            error_log(print_r($log,true));
        }else{
            error_log(" WP_Plugin --> " . $log);
        }
    }
}

// $libs = array(
//     __DIR__ . '/assets/'
// );

// foreach ($libs as $lib) {
//     require_files_of_folder($lib);
// }

require_once 'assets/custom_attributes.php';
require_once 'assets/display_fields.php'; 
require_once 'assets/model.php'; 
require_once 'assets/notices.php'; 

