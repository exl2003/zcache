<?php
/**
 * Class caching
 * Created on 09.09.2009
 * Reedit  on 29.10.2009
 *
 * @package     ZCache
 * @subpackage  Class
 * @version     SVN: $Id$
 * @revision    SVN: $Revision$
 * @access      private
 * @changedby   $Author$
 * @link       $HeadURL$
 * @date       $Date$
 * @author      Zarubin Alexey <exl2003@gmail.com>
 * @license     BSD
 */

if (!defined('DIR_ZCACHE')) {
    define('DIR_ZCACHE', '');
}

class ZCache
{
  private $CacheDir = '';
  private $recache  = false;
  private $DirMask  = 0777;

    public function __construct  ( $cache_dir = DIR_ZCACHE ){
      $this->CacheDir = $cache_dir . '_zcache/';
      /*
      $this->CacheDir = $cache_dir;
      */
    }

    public function SetRecache( $recache = false ){
       $this->recache = $recache;
    }

    public function Set( $u_key , $data , $e_time = 0 ){
      return  $this->SaveData( $u_key , $data , $e_time );
    }

    public function Get( $u_key , $static = true , $e_time = 0 ){
      if ( $this->recache ){
       return false;
      }
      return $this->LoadData( $u_key , $static , $e_time);
    }

    private function GenPath( $str_md5 ){
      $md5_path = array( substr($str_md5, 0, 2)  , substr($str_md5, 2, 2) );
      $ret =  $this->CacheDir . implode('/' , $md5_path ) . '/';
      if ( empty($ret) ){
          return false;
      }
      return $ret;
    }

    private function GenFileName( $str_md5 ){

      if ($dir_name = $this->GenPath( $str_md5 )){
      }else{
       return false;
      }

      $name = substr( $str_md5 , 4, 2);
      if ( empty($name) ){
          return false;
      }
      $ret =  $dir_name . $name;
      if ( empty($ret) ){
          return false;
      }
      return $ret;
    }

    private function GenIndexFileName( $str_md5 ){

      if ($dir_name = $this->GenPath( $str_md5 )){
      }else{
       return false;
      }
      $ret =  $dir_name . 'index';
      if ( empty($ret) ){
          return false;
      }
      return $ret;
    }


    private function AppendToIndex( $file_name , $first_data = '' ){
          $fwrite = false;
          $fp = fopen( $file_name , 'a');
          if ( !empty($first_data) ){
            $fwrite = fwrite($fp, $first_data . "\n");
          }
          fclose($fp);
          return $fwrite;
    }

    private function CreateIndex( $file_name , $first_data = '' ){
          $fwrite = false;
          $fp = fopen( $file_name , 'w');
          if ( !empty($first_data) ){
            $fwrite = fwrite($fp, $first_data . "\n");
          }
          fclose($fp);
          return $fwrite;
    }

    private function ReIndex( $file_name , $data = array() ){
      $text = ''; $ret = false;
      if ( empty($data) ){
        return false;
      }
      foreach( $data as $id_file => $values){
        foreach( $values as $id_key => $info){
          $string = $id_file . ':' . $id_key . ':' . $info['start'] . ':' . $info['end'] . ':' . $info['t_in'] . ':' . $info['t_out'] . "\n";
          $text .=  $string;
        }
      }

      if ( empty($text) ){
        unlink( $file_name );
        return true;
      }

      if ($fp = fopen( $file_name  . '.bak' , 'w')){
        if ( fwrite( $fp , $text ) ){

        }else {

        }
        fclose($fp);
        unlink( $file_name );
        rename( $file_name  . '.bak' , $file_name );
        return true;
      }
      return $ret;
    }

    private function ReadIndex( $file_name , $reread = false ){
          $array = array();
          $handle = @fopen( $file_name , "r");
          if ($handle) {
              while (!feof($handle)) {
                  $buffer = trim(fgets($handle));
                  if (!empty($buffer)){
                    $array[] = explode( ':' , $buffer);
                  }
              }
              fclose($handle);
          }
          if ( empty($array) ){
            return false;
          }else{
            $new_arr = array();
            foreach ( $array as $index => $data ){
              $new_arr[ $data[0] ][ $data[1] ] = array(
                                            'file'   => $data[0],
                                            'key'    => $data[1],
                                            'start'  => $data[2],
                                            'end'    => $data[3],
                                            't_in'   => $data[4],
                                            't_out'  => $data[5],
                                            'size'  => $data[3] - $data[2]
                                    );
            }
            return $new_arr;
          }
    }


    private  function AppendToFile( $file_name , $first_data = '' ){
          $fp = fopen( $file_name , 'r+');
          fseek($fp, 0 , SEEK_END);
          if ( !empty($first_data) ){
              fwrite($fp, $first_data);
          }else{
            //empty data
          }
          if ($pos = ftell($fp)){

          } else{
            return false;
          }
          fclose($fp);
          return $pos;
    }

    private  function CreateFile( $file_name , $first_data = '' ){
          $fwrite = false;
          $fp = fopen( $file_name , 'w');
          if ( !empty($first_data) ){
            $fwrite = fwrite($fp, $first_data);
          }else{
            //empty data
          }
          fclose($fp);
          return $fwrite;
    }


    private  function CreateDirs( $str_md5 ){
      $dir_name = $this->GenPath( $str_md5 );
      $ret = false;
      if ( !is_dir( $dir_name ) ){
        if ( $ret = @mkdir( $dir_name  , $this->DirMask , true) ){
        } else {
          /* USER_ERROR - cannot create dir */
        }
      }else{
        if ( $handle = opendir( $dir_name ) ) {
         $ret = true;
         closedir($handle);
        }else{
          /* USER_ERROR - cannot open dir */
        }
      }
      return $ret;
    }

    private  function GetFileData( $file_name , $header = array() ){
            $fp     = fopen( $file_name        , 'r' );
                      fseek( $fp  , isset($header['start']) ? $header['start'] : 0 );
            $info  =  fread( $fp  , isset($header['size'])  ? $header['size']  : 0 );
            fclose($fp);
            if ( $info = gzuncompress($info) ){
               if ( $info = unserialize($info) ){
                  return $info;
               }
            }
            return false;
    }

    private  function Exist_Dir( $str_md5 ){
      $dir_name = $this->GenPath( $str_md5 );
      $ret = false;
      if ( is_dir( $dir_name ) ){
        if ( $handle = opendir( $dir_name ) ) {
         $ret = true;
         closedir($handle);
        }
      }
      return $ret;
    }


  private  function LoadData( $u_key , $static = true , $e_time = 0 ){
    if ( empty($u_key) ){
        return false;
    }
    $key_md5 = md5( $u_key );
    $key_small_md5  = substr( $key_md5 , 6);
    $key_fsmall_md5 = substr( $key_md5 , 4);
    $key_file_md5   = substr( $key_md5 , 4 , 2);
    $file_name = $this->GenFileName( $key_md5 );
    $index_file_name = $this->GenIndexFileName( $key_md5 );
    if ( $this->Exist_Dir( $key_md5 )  && file_exists( $index_file_name ) && file_exists( $file_name )){
      $index_data = $this->ReadIndex( $index_file_name );
      if ( isset( $index_data[ $key_file_md5 ] ) ){
        if ( isset( $index_data[ $key_file_md5 ][ $key_small_md5 ] ) ){
          $header = $index_data[ $key_file_md5 ][ $key_small_md5 ];
          if ( $static ){
            return $this->GetFileData( $file_name , $header );
          } else {
            if ( empty($e_time) ){
                if ( $header['t_out'] > time() || empty($header['t_out']) ){
                  return $this->GetFileData( $file_name , $header );
                }
            } else {
              if ( $header['t_in'] + $e_time > time() ){
                return $this->GetFileData( $file_name , $header );
              }
            }
          }
        }
      }
    }
    return false;
  }

  private  function SaveData( $key = 'test' , $data=array() , $e_time = 0 ){
      if ( empty($key) /*||  empty($data) */){
          return false;
      } else {
          $key_md5 = md5( $key );
          $key_small_md5  = substr( $key_md5 , 6);
          $key_fsmall_md5 = substr( $key_md5 , 4);
          $key_file_md5   = substr( $key_md5 , 4 , 2);
          if ( !$this->CreateDirs( $key_md5 ) ){
            return false;
          }
          $file_name = $this->GenFileName( $key_md5 );
          $index_file_name = $this->GenIndexFileName( $key_md5 );
          if ( file_exists( $index_file_name ) ) {
            $index_data = $this->ReadIndex( $index_file_name );
            if ( isset( $index_data[ $key_file_md5 ] ) ){
              if ( isset( $index_data[ $key_file_md5 ][ $key_small_md5 ] ) ){
                //ReIndex
                unset( $index_data[ $key_file_md5 ][ $key_small_md5 ] );
                if ( $this->ReIndex( $index_file_name , $index_data ) ){
                  if ( $data = gzcompress( serialize( $data ) ) ){
                      if ( filesize( $file_name  ) - strlen($data) > 1024 ){
                        unlink( $file_name  );
                        $string = $key_file_md5 . ':' . $key_small_md5 . ':0:' . strlen($data) . ':' . time() . ':' . $e_time ;
                        if ( $this->CreateFile( $file_name        , $data ) ){
                            if ( $this->AppendToIndex( $index_file_name , $string ) ){
                                    return true;
                            }// else error save index data
                        }// else error save file data
                      } else {
                          if ( $pos = $this->AppendToFile( $file_name    , $data ) ){
                              $string = $key_file_md5 . ':' . $key_small_md5 . ':' . ( $pos - strlen($data) ) . ':' . $pos . ':' . time() . ':' . $e_time ;
                              if ( $this->AppendToIndex( $index_file_name , $string ) ){
                                      return true;
                              }else{ // else error save index data
                                      return false;
                              }
                          }else{// else error save file data
                            return false;
                          }
                      }
                  }else{// else error compress
                    return false;
                  }
                } else {
                 return false;
                }
              } else{
                  //AppendFile
                  if ( file_exists( $file_name ) ) {
                      if ( $data = gzcompress( serialize( $data ) ) ){
                          if ( $pos = $this->AppendToFile( $file_name    , $data ) ){
                              $string = $key_file_md5 . ':' . $key_small_md5 . ':' . ( $pos - strlen($data) ) . ':' . $pos . ':' . time() . ':' . $e_time ;
                              if ( $this->AppendToIndex( $index_file_name , $string ) ){
                                      return true;
                              }// else error save index data
                          }// else error save file data
                      }// else error compress
                  }else{
                      if ( $data = gzcompress( serialize( $data ) ) ){
                          $string = $key_file_md5 . ':' . $key_small_md5 . ':0:' . strlen($data) . ':' . time() . ':' . $e_time ;
                          if ( $this->CreateFile( $file_name        , $data ) ){
                              if ( $this->AppendToIndex( $index_file_name , $string ) ){
                                      return true;
                              }// else error save index data
                          }// else error save file data
                      }// else error compress
                  }
              }
          }else {
                if ( $data = gzcompress( serialize( $data ) ) ){
                    $string = $key_file_md5 . ':' . $key_small_md5 . ':0:' . strlen($data) . ':' . time() . ':' . $e_time ;
                    if ( $this->CreateFile( $file_name        , $data ) ){
                        if ( $this->AppendToIndex( $index_file_name , $string ) ){
                                return true;
                        }// else error save index data
                    }// else error save file data
                }// else error compress
             }
          } else {
            if ( $data = gzcompress( serialize( $data ) ) ){
                $string = $key_file_md5 . ':' . $key_small_md5 . ':0:' . strlen($data) . ':' . time() . ':' . $e_time ;
                if ( $this->CreateFile( $file_name        , $data ) ){
                  if ( $this->CreateIndex( $index_file_name , $string ) ){
                          return true;
                  }// else error save index data
                }// else error save file data
            }// else error compress
          }
      }
  }

}