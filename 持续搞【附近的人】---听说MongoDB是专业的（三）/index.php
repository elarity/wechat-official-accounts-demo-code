<?php

require_once __DIR__ . "/vendor/autoload.php";

// 这句相当于使用momo数据库
$database = ( new MongoDB\Client )->momo;
$cursor = $database->command( array(
  'geoNear' => 'user',
  'near'    => array(
    'type'        => 'Point', 
    'coordinates' => array(
      116.2092590332,40.0444375846
    ),
  ),
  'num' => 5, 
) );
$rets = $cursor->toArray()[0];
foreach( $rets->results as $r ) {
  echo $r->obj['_id'].'号用户距离您 : '.$r->dis.'米'.PHP_EOL;
}
exit;


// 这句相当于使用momo数据库，然后使用user表
$collection = ( new MongoDB\Client )->momo->user;
// 在loc字段上创建2dsphere索引
// 尽管还没有loc字段，不过这并不重要
$result = $collection->createIndex( array(
  'loc' => '2dsphere',
) );
var_dump( $result );
exit;


// 这句相当于使用momo数据库，然后使用user表
$collection = ( new MongoDB\Client )->momo->user;
// 然后插入10000数据即可
for ( $i = 1; $i <= 10000; $i++ ) {
  // 生成一个维度
  $latitude  = mt_rand( 38, 40 ).'.'.mt_rand( 100000, 999999 );
  // 生成一个经度
  $longitude = mt_rand( 115, 116 ).'.'.mt_rand( 100000, 999999 );
  $insert = $collection->insertOne( array(
    // 你可以粗暴认为：_id就是mongodb的主键，如果你不显式为_id赋值
    // 那么mongodb将会自动会_id生成一坨类似于uuid的值
    '_id' => $i,
    // 注意这个loc字段对应上面创建索引的字段名
    'loc' => array(
      // type为point，表示是点，除此之外还有Line和Polygon两种类型
      'type'        => 'Point',
      // 经度、维度
      'coordinates' => array( floatval( $longitude ), floatval( $latitude ) ),
    ),
  ) );
  var_dump( $insert );
}
