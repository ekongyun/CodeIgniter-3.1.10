<?php
/**
 * KindEditor PHP
 *
 * 本PHP程序是演示程序，建议不要直接在实际项目中使用。
 * 如果您确定直接使用本程序，使用之前请仔细确认相关安全设置。
 *
 */
require_once 'JSON.php';

header('content-type:application:json;charset=utf8');
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST');
header('Access-Control-Allow-Headers:x-requested-with,content-type,x-auth-token');   // 设置php CI 处理 CORS 自定义头部
//var_dump($_FILES);
//var_dump($_SERVER);return;
if ($_SERVER["REQUEST_METHOD"] == 'POST') {   // 只处理post请求，否则options请求 500错误

//var_dump($_POST["userid"]);
////var_dump($_GET["userid"]);
//return;
    $php_path = dirname(__FILE__) . '/';//dirname($_SERVER['DOCUMENT_ROOT']);  dirname(__FILE__)
    $php_url = "";//dirname($_SERVER['HTTP_HOST']) . '/';//PHP_SELF

//文件保存目录路径
    $save_path = $php_path . '../../../../uploads/';
//文件保存目录URL
    $save_url = $php_url . '/uploads/';
// nginx服务器端修改绝对路径
//$save_url = "http://172.30.3.11/static/home/kindeditor/attached/";

//定义允许上传的文件扩展名
    $ext_arr = array(
        'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
        'flash' => array('swf', 'flv'),
        'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
        'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
    );
//最大文件大小  10M 默认是1M
    $max_size = 10000000;

    $save_path = realpath($save_path) . '/';

//PHP上传失败
    if (!empty($_FILES['file']['error'])) {
        switch ($_FILES['file']['error']) {
            case '1':
                $error = '超过php.ini允许的大小。';
                break;
            case '2':
                $error = '超过表单允许的大小。';
                break;
            case '3':
                $error = '图片只有部分被上传。';
                break;
            case '4':
                $error = '请选择图片。';
                break;
            case '6':
                $error = '找不到临时目录。';
                break;
            case '7':
                $error = '写文件到硬盘出错。';
                break;
            case '8':
                $error = 'File upload stopped by extension。';
                break;
            case '999':
            default:
                $error = '未知错误。';
        }
        alert($error);
    }

//有上传文件时
    if (empty($_FILES) === false) {
        //原文件名
        $file_name = $_FILES['file']['name'];
        //服务器上临时文件名
        $tmp_name = $_FILES['file']['tmp_name'];
        //文件大小
        $file_size = $_FILES['file']['size'];
        //检查文件名
        if (!$file_name) {
            alert("请选择文件。");
        }
        //检查目录
        if (@is_dir($save_path) === false) {
            alert("上传目录不存在。");
        }
        //检查目录写权限
        if (@is_writable($save_path) === false) {
            alert("上传目录没有写权限。");
        }
        //检查是否已上传
        if (@is_uploaded_file($tmp_name) === false) {
            alert("上传失败。");
        }
        //检查文件大小
        if ($file_size > $max_size) {
            alert("上传文件大小超过限制(<10M)。");
        }
        //检查目录名
        $dir_name = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
        if (empty($ext_arr[$dir_name])) {
            alert("目录名不正确。");
        }
        //获得文件扩展名
        $temp_arr = explode(".", $file_name);
        $file_ext = array_pop($temp_arr);
        $file_ext = trim($file_ext);
        $file_ext = strtolower($file_ext);
        //检查扩展名
        if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
            alert("上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。");
        }
//        //创建文件夹
//        if ($dir_name !== '') {
//            $save_path .= $dir_name . "/";
//            $save_url .= $dir_name . "/";
//            if (!file_exists($save_path)) {
//                mkdir($save_path);
//            }
//        }

        // 以userid为根目录
        $userid = empty($_POST['userid']) ? '' : trim($_POST['userid']);
        //var_dump($userid);
        if ($userid == '') {
            echo "Invalid session userid.";
            exit;
        }

        //创建文件夹
        if ($dir_name !== '') {
//        $save_path .= $dir_name . "/";
//        $save_url .= $dir_name . "/";
            $save_path .= $dir_name . "/" . $userid . "/";
            $save_url .= $dir_name . "/" . $userid . "/";
            if (!file_exists($save_path)) {
                mkdir($save_path);
            }
        }
        $ymd = date("Ym");
        $save_path .= $ymd . "/";
        $save_url .= $ymd . "/";
        if (!file_exists($save_path)) {
            mkdir($save_path);
        }
        //新文件名
        $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
        //移动文件
        $file_path = $save_path . $new_file_name;
        if (move_uploaded_file($tmp_name, $file_path) === false) {
            alert("上传文件失败。");
        }
        @chmod($file_path, 0644);
        $file_url = $save_url . $new_file_name;

        header('Content-type: text/html; charset=UTF-8');
        $json = new Services_JSON();
//        echo $json->encode(array('error' => 0, 'url' => $file_url));
        echo $json->encode(array('error' => 0, 'link' => "http://222.40.42.139" . $file_url));
//        {"link":"http://222.40.42.139/uploads/image/201807/20180725175100_69184.png"}
//        echo '{"link":"http://222.40.42.139/uploads/image/201807/20180725175100_69184.png"}';
        exit;
    }
}


function alert($msg)
{
    header('Content-type: text/html; charset=UTF-8');
    $json = new Services_JSON();
    echo $json->encode(array('error' => 1, 'message' => $msg));

    exit;
}
