<?php

function createString( $type = 'alpha', $length = 6 ) {
    switch ( $type ) {
      case 'alpha':
        // total length is 62 
        $cover = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $str   = '';
        for ( $i = 1; $i <= $length; $i++ ) {
          $index = mt_rand( 0, 61 );
          $str   .= $cover[$index];
        }   
        break;
      case 'number':
        $cover = '1234567890';
        $str   = '';
        for ( $i = 1; $i <= $length; $i++ ) {
          $index = mt_rand( 0, 9 );
          $str   .= $cover[$index];
        }   
        break;
    }   
    return $str;
}

// 获取一周的第一天和最后一天
function getFEWeekDay() {
  //本周的第一天和最后一天
  $date = new DateTime();
  $date->modify('this week');
  $f_day_of_week = $date->format('Ymd');
  $date->modify('this week +6 days');
  $e_day_of_week = $date->format('Ymd');
  return array(
    $f_day_of_week,
    $e_day_of_week,
  );
}

// 生成一个id
function generateID() {
  // total length is 62 
  $cover = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
  $str   = '';
  for ( $i = 1; $i <= 32; $i++ ) {
    $index = mt_rand( 0, 61 );
    $str   .= $cover[$index];
  }   
  return $str; 
}

// 错误返回
function returnError( $code = -1, $message = '系统内部错误' ) {
  return array(
    'code'    => $code,
    'message' => $message,
  );
}

// 成功返回
function returnSuccess( $data = array() ) {
  return array(
    'code' => 0,
    'data' => $data,
  );
}

