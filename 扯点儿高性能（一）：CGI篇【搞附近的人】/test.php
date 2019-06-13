#! /usr/bin/php-cgi
<?php
echo 'http版本:'.$_SERVER['HTTP_VERSION'].PHP_EOL;
echo 'http方法:'.$_SERVER['HTTP_METHOD'].PHP_EOL;
echo 'query-string:'.$_SERVER['QUERY_STRING'].PHP_EOL;
echo "hello，xiaodushe~".PHP_EOL;
