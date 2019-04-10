# 使用说明

## 开发环境 phpstudy  php 5.6.27 + Apache

## 开发框架 PHP CI 3.1.10

## 编辑器 phpstrom

## 数据库连接配置 修改配置文件
 cat application\config\database.php
 $db['default'] = array(
 	'dsn'	=> '',
 	'hostname' => 'localhost',
 	'username' => 'root',
 	'password' => 'root',
 	'database' => 'vueadmin',

##  phpstudy 配置站点域名管理, 同时修改hosts文件（可选）
    www.cirest.com:8889

    接口调用使用示例：
    http://www.cirest.com:8889/api/v2/sys/menu/testapi

    http://www.cirest.com:8889/index.php/api/v2/sys/menu/testapi  
    
    带index.php 若要去掉 修改根目录下 CodeIgniter-3.1.10/.htaccess 文件, 不是application/ 目录下
    
    cat CodeIgniter-3.1.10/.htaccess
    
    <IfModule mod_rewrite.c>
    RewriteEngine on
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ /index.php?/$1 [QSA,PT,L]
    </IfModule>
