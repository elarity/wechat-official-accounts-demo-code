<?php
require 'vendor/autoload.php';
use Elasticsearch\ClientBuilder;
$client = ClientBuilder::create()->setHosts( array(
  '192.168.199.225'
) )->build();
$params = array(
  'index' => 'momo',
  'type'  => 'user',
  'body'  => array(
    'query' => array(
      // bool
      "bool" => array(
        "must" => array(
          "match" => array( 
            'gender' => 2,
          ),
        ),
        "filter" => array(
          "range" => array(
            "age" => array(
              "gte" => 18,
              "lte" => 18,
            ),
          ),
        ),
      ),  // bool END.
    ), // query END.
    'sort' => array(
      '_geo_distance' => array(
        "location" => array(
          'lat' => 39.972023, 
          'lon' => 116.324356, 
        ),
        'order' => 'asc',
        'unit'  => 'km',
        'distance_type' => 'plane',
      ), 
    ),  // sort END.
  ),  // query END
);
$response = $client->search( $params );
foreach( $response['hits']['hits'] as $key => $item ) {
  echo "用户ID：{$item['_id']}，性别：{$item['_source']['gender']}，年龄：{$item['_source']['age']}，距您：{$item['sort'][0]}KM".PHP_EOL;  
}
