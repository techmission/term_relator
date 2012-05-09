<?php

define('IS_CLI', PHP_SAPI === 'cli');
define('FILE_TERMS', dirname(__FILE__) . '/terms-list.txt');
define('FILE_SAVE_TERMS', dirname(__FILE__) . '/terms-list-new.txt');

if (IS_CLI) {
 $terms = array();
 $read_handle = fopen(FILE_TERMS, 'r');
 $write_handle = fopen(FILE_SAVE_TERMS, 'a+');
 var_dump($read_handle);
 var_dump($write_handle);
 if($read_handle && $write_handle) {
   while(($line = fgets($read_handle)) !== FALSE) {
     echo($line);
     $terms = explode(',', $line);
     if(is_array($terms) && count($terms) > 0) {
       foreach($terms as $term) {
         $new_line = $term . "\n";
         echo $new_line;
         fwrite($write_handle, $new_line);
       }
     }
   }
   fclose($read_handle);
   fclose($write_handle);
  }
}
  