#!/usr/bin/php
<?php

echo "Test zCache [START]....\n";
require_once('Core/ZCache.class.php');

//echo "Data ...\n .. Results ...\n";
$cache = new ZCache();
$nvgData = $cache->get( /*$u_key*/ "NVG" , /*$static = true*/ true /*, $e_time = 0 */);

// var_dump( $nvgData );
// if (   false === $nvgData ){
//   $nvgData = array('company' => 'NVG' );
//   $cache->set( /*$u_key*/ "NVG" ,  $nvgData  /*, $e_time = 0*/);
// }
// var_dump( $nvgData );




while ( true ){
   $nvgData = array('company' => 'NVG' , 'time' => time() , 'randomBlock' => str_repeat(md5(time() . "12341234123" ), 100 ) );
   $cache->set( /*$u_key*/ "NVG0" ,  $nvgData  /*, $e_time = 0*/);
   $cache->set( /*$u_key*/ "NVG1" ,  $nvgData  /*, $e_time = 0*/);
   $cache->set( /*$u_key*/ "NVG2" ,  $nvgData  /*, $e_time = 0*/);
   //sleep(1);
   usleep(20);
}
echo "Test zCache [END]....\n";

