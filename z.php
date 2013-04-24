#!/usr/bin/php
<?php

require_once('Core/ZCache.class.php');
$cache = new ZCache( dirname(__FILE__) . '/tmp/' );


echo "1) Test zCache [START]....";
$nvgData = $cache->get( "NVG" ,  true );

$i = 0;
while ( $i < 10000 ){
   $nvgData = array('company' => 'NVG' , 'time' => time() , 'randomBlock' => str_repeat(md5(time() . "12341234123" ), 100 ) );
   $cache->set( "NVG0" ,  $nvgData);
   $cache->set( "NVG1" ,  $nvgData);
   $cache->set( "NVG2" ,  $nvgData);
   $i++;
}
echo "[DONE]\n";

echo "2) Test zCache [START]....";
        $testString = 'time  :' . time();

        $dataArray = array( $testString );
        $cache->set(  'time_now' ,  $dataArray);
        sleep(1);
        if ( $dataArray = $cache->get('time_now' , false , 15) ){
          if ( $testString === $dataArray[0] ){
                sleep(20);
                if ( false === $cache->get('time_now' , false , 15) ){
                        echo "[DONE]\n";
                } else {
                        echo "[FAIL 3]\n";
                }
          }else{
                echo "[FAIL 1]\n";
          }
        }else{
                echo "[FAIL 2]\n";
        }


