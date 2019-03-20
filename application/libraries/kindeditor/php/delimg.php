<?php
/**
 * KindEditor PHP
 *
 * 本PHP程序是演示程序，建议不要直接在实际项目中使用。
 * 如果您确定直接使用本程序，使用之前请仔细确认相关安全设置。
 *
 */

require_once 'JSON.php';

$php_path = dirname(__FILE__) . '/';//dirname($_SERVER['DOCUMENT_ROOT']);  dirname(__FILE__)


//文件保存目录路径
$save_path = $php_path . '../../../../';


$save_path = realpath($save_path) . '/';

$DelFileName = $_POST['filename'];
$DelFileType = $_POST['isdir'];

$FilePath = $save_path . $DelFileName;

$FilePath = realpath($FilePath);

//var_dump($FilePath);
//
//var_dump($DelFileType);
//return;
if($DelFileType == 'F') {
    if (!unlink($FilePath))
    {
        echo "Error deleting " . $FilePath;
    } else {
        echo "succeed";
    }
    exit;
}

if($DelFileType == 'D'){
    if(!@rmdir($FilePath)){
        echo  "文件夹 " . $FilePath . " 不为空，不能删除！";
    } else {
        echo "succeed";
    }
    exit;
}


