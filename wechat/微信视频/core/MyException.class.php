<?php
/**
 * Created by PhpStorm.
 * User: Jaleel
 * Date: 2015/1/1
 * Time: 11:20
 */

class MyException extends Exception {
    public function showError($msg) {
        require_once APP . '/views/error/error.php';
    }
}