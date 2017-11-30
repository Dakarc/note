<?php
/**
 * Created by PhpStorm.
 * User: Jaleel
 * Date: 2014/12/28
 * Time: 15:59
 */

class Test extends Controller {
    public function index($data = array()) {
        //echo '这是test控制器中的Index方法<br />';
        $this->show('test/index', $data);
    }
}