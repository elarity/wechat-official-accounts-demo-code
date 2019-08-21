<?php
// 加入composer支持
require_once __DIR__ . "/vendor/autoload.php";
function createString( $type = 'alpha', $length = 8 ) {
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

// 这句相当于使用momo数据库，然后使用ugc表
$collection = ( new MongoDB\Client )->momo->ugc;

// 在loc字段上创建2dsphere索引
// 尽管还没有loc字段，不过这并不重要
$result = $collection->createIndex( array(
  'loc' => '2dsphere',
) );
echo "创建mongodb 2dsphere索引：".PHP_EOL;
print_r( $result );
sleep( 3 );
echo "插入测试用户数据（向MongoDB中插入100条带经纬度的附近动态信息）...".PHP_EOL;

// 然后插入100数据即可
for ( $i = 352; $i <= 451; $i++ ) {
  // 生成一个维度
  $latitude  = mt_rand( 38, 40 ).'.'.mt_rand( 100000, 999999 );
  // 生成一个经度
  $longitude = mt_rand( 115, 116 ).'.'.mt_rand( 100000, 999999 );
  $username  = createString();
  $insert    = $collection->insertOne( array(
    // 你可以粗暴认为：_id就是mongodb的主键，如果你不显式为_id赋值
    // 那么mongodb将会自动会_id生成一坨类似于uuid的值
    //'_id' => $iUid,
    'uid'     => $i,
    'content' => "hello！你好，我是{$username}，认识你很高兴",
    'createTime' => date('Y-m-d H:i:s'),
    // 注意这个loc字段对应上面创建索引的字段名
    'loc' => array(
      // type为point，表示是点，除此之外还有Line和Polygon两种类型
      'type'        => 'Point',
      // 经度、维度
      'coordinates' => array( floatval( $longitude ), floatval( $latitude ) ),
    ),
  ) );
  print_r( $insert );
}
echo "100条数据已经放入到了Mongodb中...".PHP_EOL;
