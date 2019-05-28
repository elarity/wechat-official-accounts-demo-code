<?php
namespace Application\Controller;
use Application\Model as Model;
class Account{
  /*
   * @desc : param['file_id'] : 文件的唯一id
             在实际业务里，你可以用[文件id+uid]保证唯一
   */
  public function mysql2excel( $param ){
    // 参数验证
    if ( empty( $param['param']['file_id'] ) ) {
      return [];
    }
    $s_file_id = $param['param']['file_id'];
    // 获取服务容器并从中取出redis操作句柄
		$o_di    = \System\Component\Di::getInstance();
    $o_redis = $o_di->get('redis');
    // 根据客户端/网页中传来的file_id参数设置内存标记
    // 我就偷懒了昂，直接用redis来记录文件状态 
    $s_file_export_state = $o_redis->get( $s_file_id );
    // 如果存在这个标记，表示文件正在【处理中】或者【已完成】
    if ( false !== $s_file_export_state ) {
      // 默认给一个空下载链接,如果已经处理完毕，你按照你的具体文件存放路径规律可以直接将下载地址拼接出来
      $s_download_link = 'done' == $s_file_export_state ? 'http://www.baidu.com/1.zip' : '' ;
      return array(
        'state' => $s_file_export_state,
        'data'  => $s_download_link,
      );
    }
    // 如果不存在这个标记，就直接进入到导出处理逻辑中 
    else {

      $s_file_export_state = 'processing';
      // 向redis中写入文件【处理中】标记
      $b_set_ret = $o_redis->set( $s_file_id, $s_file_export_state );
      if ( !$b_set_ret ) {
        return array( 
	        'code'    => -1,
	        'message' => '写入redis文件标记失败',
        );
      } 

      // 从服务容器中获取mysql资源句柄
      // 模拟30秒钟文件处理过程
      // 你可以在下面这里处理你的数据查询逻辑，以及查询完毕后如果生成为csv或者excel文件的逻辑
      $o_mysql    = $o_di->get('mysql');
      $a_user_ret = $o_mysql->query( "select * from bilibili_user_info limit 10" );
      sleep( 30 );

      // 处理完毕后，改写redis中文件标记为【已完成】
      $s_file_export_state = 'done';
      $o_redis->set( $s_file_id, $s_file_export_state );
      return array(
        'code' => 0,
      );

    }
    return [];
  }

}
