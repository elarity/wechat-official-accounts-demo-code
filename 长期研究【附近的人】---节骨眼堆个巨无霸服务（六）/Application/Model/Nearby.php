<?php
namespace Application\Model;

class Nearby {

	private $oDi    = null;
  private $oMysql = null;
  private $oMongo = null;

	public function __construct() {
		if ( null == $this->oDi ) {
		  $this->oDi = \System\Component\Di::getInstance();
		}
    $this->oMysql = $this->oDi->get( 'mysql' );
    $this->oRedis = $this->oDi->get( 'redis' );
    $this->oMongo = $this->oDi->get( 'mongo' );
	}

  /*
   * @param : fLat | 用户纬度
   * @param : fLng | 用户经度
   */
  public function search( $fLat, $fLng ) { 
    // 这句相当于使用momo数据库
    $oMomoDb = $this->oMongo->momo;
    // command方法相当于直接执行mongodb原生语句
    // 因为我懒的看这个mongodb-library的库语法了
    $oCursor = $oMomoDb->command( array(
      'geoNear' => 'user',
      'near'    => array(
        'type'        => 'Point', 
        'coordinates' => array(
          // 116.2092590332:经度  40.0444375846:纬度
          floatval( $fLng ),floatval( $fLat )
        ),  
      ),  
      'num' => 20,  
    ) );
    $aUids = array();
    $aRets = $oCursor->toArray()[0];
    foreach( $aRets->results as $r ) { 
      $_aUid = array(
        'uid'      => $r->obj['_id'],
        'distance' => $r->dis,
      );
      $aUids[] = $_aUid;
    }
    return $aUids; 
  }
 
}
