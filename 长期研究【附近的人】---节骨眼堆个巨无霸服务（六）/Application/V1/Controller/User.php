<?php
namespace Application\V1\Controller;
use Application\Model as Model;

class User {
	private $oUserModel = null;
  public function __construct() {
    $this->oUserModel = new Model\User();
	}	

  /*
   * @desc  : app会通过这个接口定时上报经纬度上来
   * @param : array aParam
              | lng
              | lat
   */
  public function track( $aParam ) {
    if ( empty( $aParam['lat'] ) || empty( $aParam['lng'] ) ) {
      return returnSuccess();
    }
    $iUid = $_SESSION['uid']; 
    $fLat = $aParam['lat'];
    $fLng = $aParam['lng'];
    $this->oUserModel->track( $iUid, $fLat, $fLng );
    return []; 
  }
  
  /*
   * @desc  : 用户登陆
   * @param : array aParam
              | username 用户名
              | password 密码
   */
  public function login( $aParam ) {
    $aChkRet = $this->_checkParam( $aParam );
    if ( 0 != $aChkRet['code'] ) {
      return $aChkRet;
    }
    $sUsername = $aParam['username'];
    $sPassword = $aParam['password'];
    $aLoginRet = $this->oUserModel->login( $sUsername, $sPassword );
    if ( false == $aLoginRet ) {
      return returnError( -1, "用户名或密码错误" );
    }
    $aSessionRet = $this->_setSession( $aLoginRet );
    return returnSuccess( $aSessionRet );
  }

  /*
   * @desc  : 用户注册.
   * @param : array aParam
              | username 用户名
              | password 密码
   */
  public function register( $aParam ) {
    $aChkRet = $this->_checkParam( $aParam );
    if ( 0 != $aChkRet['code'] ) {
      return $aChkRet;
    }
    $sUsername = $aParam['username'];
    $sPassword = $aParam['password'];
    // 检测用户是否注册过.
    $aUser = $this->oUserModel->checkUser( $sUsername );
    if ( false == $aUser ) {
      $aUser = $this->oUserModel->register( $sUsername, $sPassword );
    }
    $aSessionRet = $this->_setSession( $aUser );
    return returnSuccess( $aSessionRet );
  }

  private function _checkParam( $aParam ) {
    if ( empty( $aParam['username'] ) || empty( $aParam['password'] ) ) {
      return returnError( -1, "username或者password为空" );
    }
  }

  private function _setSession( $aUser ) {
		$oDi    = \System\Component\Di::getInstance();
    $oRedis = $oDi->get( 'redis' );
    $iUid   = $aUser['id'];
    $sToken = md5( $iUid.time() );
    $sUsername   = $aUser['username'];
    $sSessionKey = TOKEN_PREFIX.$sToken;
    $oRedis->hmset( $sSessionKey, array(
      'uid'      => $iUid, 
      'username' => $sUsername, 
    ) ); 
    $oRedis->expire( $sSessionKey, 30 * 24 * 3600 );
    unset( $aUser['password'] );
    return array(
      'token' => $sToken,
      'user'  => $aUser,
    );
  }

}
