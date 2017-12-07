<?php
/**
 * Created by PhpStorm.
 * User: Jaleel
 * Date: 2015/1/1
 * Time: 11:40
 */

class Controller {
    public function show($page, $data = array()) {
        $dir = 'app/views/' . $page . '.php';
        if (file_exists($dir)) {
            require_once $dir;
        }
    }
}