<?php

global $DB;

define('IS_CLI', PHP_SAPI === 'cli');
define('FILE_TERMS', dirname(__FILE__) . '/terms-list.txt');

// Load the class for doing the inserts to the database.
require_once(dirname(__FILE__) . '/jobsdb.class.php');

if (IS_CLI) {
 $terms = array();
 $read_handle = fopen(FILE_TERMS, 'r');
 if($read_handle) {
  while(($line = fgets($read_handle)) !== FALSE) {
   $num_inserted = 0;
   // Connect to database so querying will work.
   connect_to_db();
   $terms = explode(',', $line);
   if(is_array($terms) && count($terms) > 0) {
    var_dump($terms);
    $svc_area = array_shift($terms);
    if($svc_area == '"Sound') {
      $svc_area = 'Sound, Lights, Video Ministry';
    }
    $tid1 = get_term_tid($svc_area);
    foreach($terms as $term) {
     $tid2 = get_term_tid($term);
     if(is_numeric($tid1) && is_numeric($tid2)) {
       //$inserted = write_relation($tid1, $tid2);
     }
     //$num_inserted = $num_inserted + $inserted;
    }
   }
  }
  fclose($read_handle);
 }
}

function connect_to_db() {
 global $DB;
 // Initialize the database handler.
 try {
  $DB = new JobsDB();
 }
 catch(Exception $e) {
  echo "Exception: " . $e->getMessage() . "\n";
 }
 
 // Set to not log at database layer, since logging does not work in command line mode.
 $DB->isLogging = FALSE;
 
 // Connect to the database;
 $DB->connect();
 return;
}

function get_term_tid($term_name) {
 if(!empty($term_name)) {
  $stmt = $DB->prepare('SELECT tid FROM um_term_data WHERE name = :name');
  $stmt->bindValue(':name', $term, PDO::PARAM_STR);
  $stmt->execute();
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  if(count($rows) > 0 && $rows[0]['tid']) {
   return $rows[0]['tid'];
  }
  else {
   var_dump($rows);
  }
 }
}

function write_relation($tid1, $tid2) {
  global $DB;
  $num_inserted = 0;
  if(is_numeric($tid1) && is_numeric($tid2)) {
    $stmt = $DB->prepare('INSERT INTO bk_term_relation(tid1, tid2) VALUES(:tid1, :tid2)');
    $stmt->bindValue(':tid1', $tid1, PDO::PARAM_INT);
    $stmt->bindValue(':tid2', $tid2, PDO::PARAM_INT);
    $stmt->execute();
    $num_inserted = $stmt->rowCount();
  }
  return $num_inserted;
}