<?php
/**
 * 项目后台入口文件 单一入口
 * User: Jaleel
 * Date: 2014/12/28
 * Time: 15:25
 */

define('APP', 'web');

require_once '../core/App.class.php';

//注册一个用户自定义的自动加载类方法
spl_autoload_register(array('App', 'myAutoLoader'));
try {
    App::run();
} catch (MyException $e) {
    $e->showError($e->getMessage());
}