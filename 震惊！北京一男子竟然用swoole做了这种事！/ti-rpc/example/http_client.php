<?php
/*
 @desc : 利用curl封装的简单对http的客户端演示案例
 */

// 封装curl方法
function curl_init_param( $curl, $json_data ) {
  curl_setopt_array( $curl, array(
    CURLOPT_PORT      => 6666,
    CURLOPT_URL       => "http://127.0.0.1:6666/",
    CURLOPT_ENCODING  => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT   => 30,
    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST  => "POST",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS     => $json_data,
    CURLOPT_HTTPHEADER     => array(
      "cache-control: no-cache",
      "postman-token: efe52804-aa89-8c4d-01ae-5e9a27012312"
    ),
  ) );
}
// 文件名称
$s_file_id = '123456789123456';

// 模拟网页点击【导出】按钮后，发出【导出】命令请求...
// 构造请求参数.
$sn = array(
  'type' => 'SN',
  'requestId' => time(),
  'param' => array(
    'model' => 'Account',
    'method' => 'mysql2excel',
    'param' => array(
      'file_id'  => $s_file_id,
    ),
  ),
);
$json_data = json_encode( $sn );
$curl      = curl_init();
curl_init_param( $curl, $json_data );
$response  = curl_exec( $curl );
$err       = curl_error( $curl );
if ( $err ) {
  echo "cURL Error #:" . $err.PHP_EOL;
  exit(); 
}
print_r( json_decode( $response, true ) );

// 点击完毕【导出】按钮后，开始模拟ajax轮训状态，一秒钟一次...
while ( 1 ) {
  sleep( 1 );
  // 构造请求参数.
  $sw = array(
    'type' => 'SW',
    'requestId' => time(),
    'param' => array(
      'model' => 'Account',
      'method' => 'mysql2excel',
      'param' => array(
        'file_id'  => $s_file_id,
      ),
    ),
  );
  $json_data = json_encode( $sw );
  curl_init_param( $curl, $json_data );
  $response  = curl_exec( $curl );
  $err       = curl_error( $curl );
  if ( $err ) {
    echo "cURL Error #:" . $err.PHP_EOL;
  } else {
    print_r( json_decode( $response, true ) );
  }
}
