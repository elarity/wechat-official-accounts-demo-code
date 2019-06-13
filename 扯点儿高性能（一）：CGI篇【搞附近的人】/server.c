#include <stdio.h>
#include <stdlib.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <string.h>
#include <arpa/inet.h>
#include <unistd.h>
#define BUFFER_SIZE 4096
extern char ** environ;
int main( int argc, char * argv[] ) {
  //for ( int index = 0; environ[ index ] != NULL; index++ ) {
    //printf( "%s\n", environ[ index ] );
  //}
  if ( argc < 3 ) {
    printf( "usage : ./server 0.0.0.0 6666\n" );
    exit( -1 );
  }
  const char * ip_string_p = argv[ 1 ];
  int port_int    = atoi( argv[ 2 ] );
  int backlog_int = 10;
  int common_ret_int;
  int listen_socket_fd;
  int client_socket_fd;
  struct sockaddr_in socket_base_struct;
  struct sockaddr_in client_base_struct;
  int client_struct_length_int;
  int socket_opt_address_reuse_int = 1;
  listen_socket_fd = socket( PF_INET, SOCK_STREAM, 0 );
  if ( listen_socket_fd < 0 ) {
    exit( -1 );
  }
  setsockopt( listen_socket_fd, SOL_SOCKET, SO_REUSEADDR, &socket_opt_address_reuse_int, sizeof( socket_opt_address_reuse_int ) );
  // 创建socket struct结构体并清空其中内存的数据
  bzero( &socket_base_struct, sizeof( socket_base_struct ) );
  socket_base_struct.sin_family = PF_INET;
  // 将PORT转换成big-endian的PORT
  socket_base_struct.sin_port   = htons( port_int );
  // 将IP地址转换为big-endian的IP地址
  inet_pton( PF_INET, ip_string_p, &socket_base_struct.sin_addr );
  // 将分配好的address struct绑定好创建的listen socket上去
  common_ret_int = bind( listen_socket_fd, ( struct sockaddr * )&socket_base_struct, sizeof( socket_base_struct ) );
  if ( common_ret_int < 0 ) {
    exit( -1 );
  }
  // 开始监听listen socket
  common_ret_int = listen( listen_socket_fd, backlog_int );
  if ( common_ret_int < 0 ) {
    exit( -1 );
  }
  client_struct_length_int = sizeof( client_base_struct );
  // 让服务器陷入无限循环中
  while ( 1 ) {
    client_socket_fd = accept( listen_socket_fd, ( struct sockaddr * )&client_base_struct, &client_struct_length_int );
    if ( client_socket_fd < 0 ) {
      exit( -1 );
    }  
    // fork一下，子进程去调用处理 php-cgi 程序
    pid_t pid;
    pid = fork();
    if ( 0 == pid ) {
      // 别废话那么多，先能用再说
      char buf[ BUFFER_SIZE ];
      char content[ BUFFER_SIZE ];
      char * http_state_line_string_p;
      char * http_method_string_p;
      char * http_query_string_p;
      char * http_version_string_p;
      FILE * file_fd;
      recv( client_socket_fd, content, BUFFER_SIZE - 1, 0 );
      /*
       此处顺带为了让泥腿子们了解HTTP协议，我直接把http协议传输过来的数据
       全部打印出来，你们感受一下传说中HTTP协议load的数据是长什么样子的.
       一般说来，http服务器要做的就是解析这段http数据,解析成标准格式供我们
       使用。
       */
      printf( "这就是传说中的HTTP协议的具体数据内容：\n" );
      printf( "%s\n", content );
      printf( "传说中HTTP协议数据内容已经OVER\n" );
      /*
       下面四行代码，是将HTTP数据中第一行：状态请求行 截取出来后开始解析
       - GET则是PHP中常见的$_SERVER['http_method']
       - /?username=xiaodushe则为QUERY_STRING
       - HTTP/1.1则为http协议版本
       这三项内容在php中都保存在了$_SERVER中..如果我没记错的话
       strtok()是C语言函数中的一个奇葩......
       */
      http_state_line_string_p = strtok( content, "\r\n" );
      http_method_string_p     = strtok( http_state_line_string_p, " " );
      http_query_string_p      = strtok( NULL, " " );
      http_version_string_p    = strtok( NULL, " " );
      // 就先码死这个php-cgi程序吧，理论上cgi程序应该根据请求路径不同加载不同的cgi程序...
      // 这个。。。先将就一下，码死成一个固定的cgi，能用就行..
      // 我们将get参数通过设置环境变量传递给php-cgi程序
      setenv( "QUERY_STRING", http_query_string_p, 1 );
      setenv( "HTTP_METHOD", http_method_string_p, 1 );
      setenv( "HTTP_VERSION", http_version_string_p, 1 );
      // 从php-cgi拿回来数据...
      FILE * fp = popen( "./test.php", "r" );
      // 下面是按照http协议标准手工构造http数据返回给客户端
      // 如果你不按照下面标准进行构造，客户端一般会返回一些提示，比如
      // curl会返回:curl: (52) Empty reply from server
      char html_entity[ BUFFER_SIZE ];
      char html_body_content[ BUFFER_SIZE ];
      fread( html_body_content, sizeof( char ), sizeof( html_body_content ), fp );
      char html_response_template[] = "HTTP/1.1 200 OK\r\nContent-Type:text/plain\r\nContent-Length: %d\r\nHttp-Server:ti-server\r\n\r\n%s";
      sprintf( html_entity, html_response_template, strlen( html_body_content ), html_body_content );
      send( client_socket_fd, html_entity, sizeof( html_entity ), 0 );
      // 关闭与客户端的连接.
      close( client_socket_fd );
      exit( -1 );
    }
  }
  // 关闭socket
  close( listen_socket_fd );
  return 0;
} 
