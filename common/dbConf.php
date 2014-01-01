<?php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PWD', '');

function connect(){
    $connect = mysql_connect(DB_HOST, DB_USER, DB_PWD);
    if(!$connect){
        die('连接失败:' . mysql_error());
    }
    query('set names utf8');
    return $connect;
}

function selectDB($dbName, $connect){
    mysql_select_db($dbName, $connect) or die("数据库连接错误:" . mysql_error());
}

function query($sql){
    return mysql_query($sql);
}

function free($result){
    mysql_free_result($result);
}

function close($connect){
    mysql_close($connect);
}
