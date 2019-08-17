<?php
namespace Application\Model;

class User {

	private $oDi    = null;
  private $oMysql = null;

	public function __construct() {
		if ( null == $this->oDi ) {
		  $this->oDi = \System\Component\Di::getInstance();
		}
    $this->oMysql = $this->oDi->get( 'mysql' );
    $this->oRedis = $this->oDi->get( 'redis' );
    $this->oMongo = $this->oDi->get( 'mongo' );
	}

  /*
   * @param : iUid | 用户uid
   * @param : fLat | 用户纬度
   * @param : fLng | 用户经度
   */
  public function track( $iUid, $fLat, $fLng ) {
    $oCollection = $this->oMongo->momo->user; 
    print_r( $oCollection );
  }

  /*
   * @param : iUid | 用户uid
   */
  public function getUserByUid( $iUid ) { 
    $sSql  = "select * from ti_user where id=:id";
    $aUser = $this->oMysql->row( $sSql, array(
      'id' => $iUid,
    ) );
    unset( $aUser['password'] );
    return $aUser;
  }

  /*
   * @desc  : 用户登陆.
   * @param : sUsername
   * @param : sPassword
   */
  public function login( $sUsername, $sPassword ) {
    $sSql  = "select * from ti_user where username=:username";
    $aUser = $this->oMysql->row( $sSql, array(
      'username' => $sUsername,
    ) );
    if ( false == $aUser ) {
      return false;
    }
    if ( password_verify( $sPassword, $aUser['password'] ) ) { 
      return $aUser;
    } 
    else {
      return false;
    }
  } 
  
  /*
   * @desc  : 用户注册.
   * @param : sUsername
   * @param : sPassword
   */
  public function register( $sUsername, $sPassword ) {
    $sSql      = "insert into ti_user(`username`,`password`) values(:username,:password)"; 
    $sPassword = password_hash( $sPassword, PASSWORD_DEFAULT ); 
    $bRet = $this->oMysql->query( $sSql, array( 
      'username' => $sUsername,
      'password' => $sPassword,
    ) );
    return array(
      'id'       => $this->oMysql->lastInsertId(),
      'username' => $sUsername,
    );
  }

  /*
   * @desc  : 检测用户是否存在.
   * @param : sUsername
   */
  public function checkUser( $sUsername ) {
    $sSql  = "select * from ti_user where username=:username";
    $aUser = $this->oMysql->row( $sSql, array(
      'username' => $sUsername,
    ) );
    return $aUser;
  }
  
}
