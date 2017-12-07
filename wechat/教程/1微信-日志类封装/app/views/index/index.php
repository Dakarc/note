<?php
/**
 * Created by PhpStorm.
 * User: Jaleel
 * Date: 2015/1/1
 * Time: 11:43
 */

echo 'index page is here<br />';
if (!empty($data)) {
    echo 'my name is ' . $data['name'] . '<br />';
    echo 'i am ' . $data['age'] . ' years old!<br />';
}