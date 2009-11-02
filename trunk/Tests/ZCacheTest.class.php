#!/usr/bin/php
<?php

    require_once('PHPUnit/Framework.php');
    require_once 'PHPUnit/Framework/TestCase.php';
    require_once('../Core/ZCache.class.php');


    class ZCacheTest extends PHPUnit_Framework_TestCase {


            function testSet() {
                    $m = new ZCache();
                    $this->assertTrue($m->set( 'test',  array( 'test_int'  => 12 , 'test_double'  => 12.234 ,  'test_string'  => 'simple string - data' ,  'test_array'  => array(1,2,3,4)  )  ));
                    $this->assertTrue($m->set( 'test2',  array( 'test_int'  => 12 , 'test_double'  => 12.234 ,  'test_string'  => 'simple string - data' ,  'test_array'  => array(5,6,3,2009)  )  ));
            }

            function testGet() {
                    $m = new ZCache();
                    $this->assertEquals( $m->get( 'test', true  )   ,   array( 'test_int'  => 12 , 'test_double'  => 12.234 ,  'test_string'  => 'simple string - data' ,  'test_array'  => array(1,2,3,4)  )   );
                    $this->assertEquals( $m->get( 'test2', true  )   ,   array( 'test_int'  => 12 , 'test_double'  => 12.234 ,  'test_string'  => 'simple string - data' ,  'test_array'  => array( 5, 6,  3, 2009)  )   );
                    $this->assertFalse($m->get( 'test-2', true , 10  ) , ' Get no exists key ' );
            }


    }
