<?php
namespace Application\V1\Controller;
use Application\Model as Model;

class Nearby {

	private $oUserModel   = null;

	private $oNearbyModel = null;

  public function __construct() {
    $this->oUserModel   = new Model\User();
    $this->oNearbyModel = new Model\Nearby();
	}	

  /*
   * @desc  : app会通过这个接口定时上报经纬度上来
   * @param : array aParam
              | lng
              | lat
   */
  public function index( $aParam ) {
    if ( empty( $aParam['lng'] ) || empty( $aParam['lat'] ) ) {
      return returnError( -1, "缺少经纬度参数" );
    }
    $fLat   = $aParam['lat'];
    $fLng   = $aParam['lng'];
    $aUids  = $this->oNearbyModel->search( $fLat, $fLng );
    // 获取用户详细信息. 
    $aUsers = array();
    foreach( $aUids as $aUid ) {
      $aUser = $this->oUserModel->getUserByUid( $aUid['uid'] );
      if ( false !== $aUser ) {
        $aUser['distance'] = $aUid['distance'];
        $aUsers[] = $aUser; 
      }
    }
    return returnSuccess( $aUsers );
  }
  
}
