<?php
/**
 * 前台首页控制器
 * User: Jaleel
 * Date: 2014/12/28
 * Time: 15:57
 */

class Home extends Controller {
    public function index($data = array()) {
        //echo '这是前台Home控制器中的Index方法！<br />';
        $this->show('index/index', $data);
    }
}