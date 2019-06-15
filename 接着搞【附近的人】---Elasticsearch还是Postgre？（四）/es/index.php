<?php
require 'vendor/autoload.php';
use Elasticsearch\ClientBuilder;
$client = ClientBuilder::create()->setHosts( array(
  '192.168.199.225'
) )->build();

$params = [
    'index' => 'momo',
    'type'  => 'user',
    'id'    => 12,
];
$response = $client->get( $params );
print_r( $response );
exit;

/*
// 删除一条数据
$params = [
  'index' => 'momo',
  'type'  => 'user',
  'id'    => 1
];
$response = $client->delete( $params );
print_r( $response );
exit;
*/

// 造1000个假数据
for( $i = 1; $i <= 1000; $i++ ) {
  // 生成一个维度
  $lat = mt_rand( 38, 40 ).'.'.mt_rand( 100000, 999999 );
  // 生成一个经度
  $lon = mt_rand( 115, 116 ).'.'.mt_rand( 100000, 999999 );
  $params = array(
    'index' => 'momo',
    'type'  => 'user',
    'id'    => $i,
    'body'  => array(
      'age'      => mt_rand( 18, 25 ),
      'gender'   => mt_rand( 1, 2 ),
      'location' => array(
        'lat' => $lat,
        'lon' => $lon,
      ),
    ),
  );
  $response = $client->index( $params );
  print_r( $response );
}

