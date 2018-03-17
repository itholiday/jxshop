<?php
return array(
	//'配置项'=>'配置值'
    'URL_MODEL' => 2,//设置为rewrite模式url
    'DEFAULT_MODULE' => 'Home',//默认访问模块
    'MODULE_ALLOW_LIST' => array('Home','Admin'),//允许访问的模块
    'TMPL_PARSE_STRING' => array(
        '__PUBLIC_ADMIN__' => '/Public/Admin',
        '__PUBLIC_HOME__' => '/Public/Home'
    ),
    /* 数据库设置 */
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  '127.0.0.1', // 服务器地址
    'DB_NAME'               =>  'jxshop',          // 数据库名
    'DB_USER'               =>  'root',      // 用户名
    'DB_PWD'                =>  '12345',          // 密码
    'DB_PORT'               =>  '3306',        // 端口
    'DB_PREFIX'             =>  'jx_',    // 数据库表前缀
    /*上传目录*/
    'UPLOADS' => '/Uploads',
    'SHOW_PAGE_TRACE' => true,
    'key' => ''
);