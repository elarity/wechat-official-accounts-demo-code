<?php

if( 'cli' !== php_sapi_name() ){	
	exit( '服务只能运行在cli sapi模式下'.PHP_EOL );
}

if( !extension_loaded('swoole') ){
	exit( '请安装swoole扩展'.PHP_EOL );
}

// 定义系统常量
define( 'DS', DIRECTORY_SEPARATOR );
define( 'ROOT', __DIR__.DS );
// 定义mysql配置
define( 'MYSQL_HOST', '127.0.0.1' );
define( 'MYSQL_USER', 'root' );
define( 'MYSQL_PASS', '123456' );
define( 'MYSQL_DB',   'momo' );
// 定义redis配置
define( 'REDIS_HOST', '127.0.0.1' );
define( 'REDIS_PORT', 6379 );
// redis前缀
define( 'TOKEN_PREFIX', 'token:' );
$aApiAuthConfig = array(
  'User' => array(
    'login' => array(
      'anonymous' => true, 
    ),
    'register' => array(
      'anonymous' => true, 
    ),
    'track' => array(
      'anonymous' => false, 
    ),
  ),
  'Nearby' => array(
    'index' => array(
      'anonymous' => false, 
    ),
  ),
);

// 载入系统函数库
require_once ROOT."System".DS."Library".DS."Function.php";

if( is_file( ROOT.'vendor'.DS.'autoload.php' ) ){
  require_once ROOT.'vendor'.DS.'autoload.php';
}
else {
  // 自定义autoload方法
  function autoload( $class ){
    $includePath = str_replace( '\\', DS, $class );
    $targetFile = ROOT.$includePath.'.php';
    require_once( $targetFile );
  }
  spl_autoload_register( 'autoload' );
}


// 继承Core父类
class Gmu extends System\Core{
  // 拉起worker进程前需要做的初始化工作
  public function initWorker(){}
  // 拉起tasker进程前需要做的初始化工作
  // 比如初始化数据库类库
  // 比如初始化其他类库
  public function initTasker( \swoole_server $server, $workerId ){
		$oDi = \System\Component\Di::getInstance();
    // mysql to di
    $oMysql = new \System\Library\Mysql();  
    $oDi->set( 'mysql', $oMysql ); 
    // redis to di
    $oRedis = new \Redis();
    $oRedis->connect( REDIS_HOST, REDIS_PORT );
    $oDi->set( 'redis', $oRedis );
    // mongodb to di
    $oMongodb = new MongoDB\Client();
    $oDi->set( 'mongo', $oMongodb );
    // validator to di
    $oValidator = new \System\Library\Valitron\Validator();  
    $oDi->set( 'validator', $oValidator );
  }
  // 具体业务逻辑
  public function process( $aServer, $aParam ){
    global $aApiAuthConfig;
    $sPathInfo = $aParam['swoole']['server']['path_info'];
    $aPathInfo = explode( '/', $sPathInfo );
    if ( 4 != count( $aPathInfo ) ) {
      return array(
        'code'    => -1,
        'message' => '非法请求,错误的API URI',
      );
    }
    $sVersion    = strtoupper( $aPathInfo[ 1 ] );
    $sClassName  = ucfirst( $aPathInfo[ 2 ] );
    $sMethodName = $aPathInfo[ 3 ];
		$aParam['param'] = isset( $aParam['param'] ) ? $aParam['param'] : array() ;
    // get DI instance 
		$oDi = \System\Component\Di::getInstance();
    $oRedis = $oDi->get( 'redis' );
    $aAuthConfig = $aApiAuthConfig[ $sClassName ][ $sMethodName ];
    // 如果API允许匿名访问.
    if ( true == $aAuthConfig['anonymous'] ) {
      $aSession = array();
    }
    else {
      if ( empty( $aParam['swoole']['header']['token'] ) ) {
        return returnError( -1, '缺少TOKEN' );
      } 
      $sToken   = $aParam['swoole']['header']['token'];
      $aSession = $oRedis->hgetall( TOKEN_PREFIX.$sToken );
      if ( 0 == count( $aSession ) ) {
        return returnError( 9999, 'token已经失效!' );
      } 
    }
    $_SERVER  = $aParam['swoole'];
    $_SESSION = $aSession;
    // 将param抛给model中的method，并获得到处理完后的数据
		$sTargetModel  = '\Application\\'.$sVersion.'\\Controller\\'.$sClassName;
    $oTargetModel  = new $sTargetModel; 
		$aTargetConfig = $aParam['param'];
    $aSendData = call_user_func_array( array( $oTargetModel, $sMethodName ), array( $aTargetConfig ) );
		return $aSendData;
  }

}

$gmu = new Gmu;

// 开启一些配置项
$gmu->initSetting( array(
	'http' => array(
		'host' => '0.0.0.0',
    'port' => 8000,
	),
	'tcp' => array(
    'port' => 6667,
  ),
	'custom' => array(
		'tcpPack' => 'length',    // 1.eof，eof拆包 2.length，length拆包
	),
  // 服务注册
  /*
  'serviceRegisterSetting' => array(
    'host' => '127.0.0.1',
    'port' => 6379,
    'serviceName' => 'account-service',
  ),  
  */
) );
$gmu->run();
