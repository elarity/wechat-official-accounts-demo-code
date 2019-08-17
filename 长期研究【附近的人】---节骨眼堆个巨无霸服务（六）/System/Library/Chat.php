<?php
namespace System\Library;
class Chat{

  private static $client_id     = 'YXA62J2N8C9kEem3Bf1NrkcPyA';  
  private static $client_secret = 'YXA6eo3pz8MW_eBvRs26j2NL8B8zWyQ';
  private static $host          = 'http://a1.easemob.com/1103190212107405/fenda/';
  private static $curl          = null;
  private static $file          = ROOT.'System'.DS.'Library'.DS.'Chat.json';

  public function __construct() {
    // 获取curl资源
    self::$curl = new System\Library\Curl\Curl();
    if ( !is_file( self::$file ) ) {
      file_put_contents( self::$file, json_encode( array() ) );
    }
  }

  // 获取token
  public static function getToken() {
    self::$curl = new Curl\Curl();
    $sToken = file_get_contents( self::$file );
    $aToken = json_decode( $sToken, true );
    // 如果这个字段为空 | 或者当前时间已经超过超时时间点
    if ( empty( $aToken['expires_at'] ) || ( time() > $aToken['expires_at'] ) ) {
    //if ( true ) {
      self::$curl->setHeader( 'Content-Type', 'application/json' ); 
      $sTokenRet = self::$curl->post( self::$host.'token', array(
        'grant_type'    => 'client_credentials',
        'client_id'     => self::$client_id,
        'client_secret' => self::$client_secret,
      ) );
      $aToken = json_decode( json_encode( $sTokenRet ), true );
      $aToken['expires_at'] = time() + $aToken['expires_in'];
      file_put_contents( self::$file, json_encode( $aToken ) );
    }
    return $aToken;
  }

  // 创建用户
  public static function createUser( $uid ) {
    $aToken = self::getToken(); 
    $sToken = $aToken['access_token']; 
    self::$curl->setHeader( 'Content-Type', 'application/json' ); 
    self::$curl->setHeader( 'Authorization', 'Bearer '.$sToken ); 
    $oInsertRet = self::$curl->post( self::$host.'users', array(
      'username' => $uid, 
      'password' => md5( $uid ), 
    ) );
    return json_decode( json_encode( $oInsertRet ), true );
  }

  /*
   @desc : 发送文本消息
   target_type : users, chatgroups, chatrooms
   target      : [ uid1 ] 
   msg         : 消息内容
   type        : 消息类型；txt:文本消息，img：图片消息，loc：位置消息，audio：语音消息，video：视频消息，file：文件消息 
   from        : 表示消息发送者;无此字段Server会默认设置为“from”:“admin” 
   */
  public static function sendTxtMsg( array $data ) {
    $aToken = self::getToken(); 
    $sToken = $aToken['access_token']; 
    self::$curl->setHeader( 'Content-Type', 'application/json' ); 
    self::$curl->setHeader( 'Authorization', 'Bearer '.$sToken ); 
    $aSendRet = self::$curl->post( self::$host.'messages', array(
      'target_type' => $data['target_type'], 
      'target'      => $data['target'], 
      'msg'         => $data['msg'], 
      'type'        => 'txt',
      'from'        => $data['from'], 
    ) );
    return json_decode( $aSendRet, true );
  } 

  /*
   @desc : 发送图片消息
   target_type : users, chatgroups, chatrooms
   target      : [ uid1 ] 
   msg         : 消息内容
   type        : 消息类型；txt:文本消息，img：图片消息，loc：位置消息，audio：语音消息，video：视频消息，file：文件消息 
   from        : 表示消息发送者;无此字段Server会默认设置为“from”:“admin” 
   */
  public static function sendImgMsg( array $data ) {
    $aToken = self::getToken(); 
    $sToken = $aToken['access_token']; 
    $this->curl->setHeader( 'Content-Type', 'application/json' ); 
    $this->curl->setHeader( 'Authorization', 'Bearer '.$sToken ); 
    $aSendRet = $this->curl->post( $this->host.'messages', array(
      'target_type' => $data['target_type'], 
      'target'      => $data['target'], 
      'msg'         => $data['msg'], 
      'type'        => 'img',
      'from'        => $data['from'], 
    ) );
    return json_decode( $aSendRet, true );
  } 

  /*
   @desc : 发送语音消息
   target_type : users, chatgroups, chatrooms
   target      : [ uid1 ] 
   msg         : 消息内容
   type        : 消息类型；txt:文本消息，img：图片消息，loc：位置消息，audio：语音消息，video：视频消息，file：文件消息 
   from        : 表示消息发送者;无此字段Server会默认设置为“from”:“admin” 
   */
  public static function sendAudioMsg( array $data ) {
    $aToken = self::getToken(); 
    $sToken = $aToken['access_token']; 
    $this->curl->setHeader( 'Content-Type', 'application/json' ); 
    $this->curl->setHeader( 'Authorization', 'Bearer '.$sToken ); 
    $aSendRet = $this->curl->post( $this->host.'messages', array(
      'target_type' => $data['target_type'], 
      'target'      => $data['target'], 
      'msg'         => $data['msg'], 
      'type'        => 'audio',
      'from'        => $data['from'], 
    ) );
    return json_decode( $aSendRet, true );
  } 

}
