<?php

global $DB;

define('IS_CLI', PHP_SAPI === 'cli');
define('FILE_TERMS', dirname(__FILE__) . '/terms-list.txt');

define('VID_SVC_AREA', 32);
define('VID_KEYWORD', 350);

// Load the class for doing the inserts to the database.
require_once(dirname(__FILE__) . '/jobsdb.class.php');

// Temporarily display runtime errors to the screen.
ini_set('display_errors', TRUE);

// To get the information from term relation: 
//
// select td1.name, td2.name from um_term_relation tr 
//join um_term_data td1 on td1.tid = tr.tid1
// join um_term_data td2 on td2.tid = tr.tid2;


if (IS_CLI) {
 $terms = array();
 $read_handle = fopen(FILE_TERMS, 'r');
 $num_attempts = 0;
 if($read_handle) {
  while(($line = fgets($read_handle)) !== FALSE) {
   $num_inserted = 0;
   // Connect to database so querying will work.
   connect_to_db();
   $terms = explode(',', $line);
   if(is_array($terms) && count($terms) > 0) {
    //var_dump($terms);
    $svc_area = array_shift($terms);
    if($svc_area == '"Sound') {
      $svc_area = 'Sound, Lights, Video Ministry';
    }
    $tid1 = get_term_tid($svc_area, VID_SVC_AREA);
    foreach($terms as $term) {
     $tid2 = get_term_tid($term, VID_KEYWORD);
     if(is_numeric($tid1) && is_numeric($tid2)) {
       $inserted = write_relation($tid1, $tid2);
       $num_inserted++;
     }
     else {
       echo "Failed on: " . $svc_area . " | " . $term . "\n";
       $num_failures++;
     }
     $num_attempts++;
    }
   }
  }
  fclose($read_handle);
 }
}

echo "Attempts: " . $num_attempts . "\n";
echo "Successes: " . $num_inserted . "\n"; // Why is this wrong?
echo "Failures: " . $num_failures . "\n";
exit();

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

function get_term_tid($term_name, $vid) {
 global $DB;
 if(!empty($term_name)) {
  $stmt = $DB->dbh->prepare('SELECT tid FROM um_term_data WHERE name = :name AND vid = :vid');
  $stmt->bindValue(':name', $term_name, PDO::PARAM_STR);
  $stmt->bindValue(':vid', $vid, PDO::PARAM_INT);
  $stmt->execute();
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  if(count($rows) > 0 && $rows[0]['tid']) {
   return $rows[0]['tid'];
  }
 }
}

function write_relation($tid1, $tid2) {
  global $DB;
  $num_inserted = 0;
  if(is_numeric($tid1) && is_numeric($tid2)) {
    $stmt = $DB->dbh->prepare('INSERT INTO um_term_relation(tid1, tid2) VALUES(:tid1, :tid2)');
    $stmt->bindValue(':tid1', $tid1, PDO::PARAM_INT);
    $stmt->bindValue(':tid2', $tid2, PDO::PARAM_INT);
    $stmt->execute();
    $num_inserted = $stmt->rowCount();
  }
  return $num_inserted;
}