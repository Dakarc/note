<?php
/**
 * URL重写类
 * User: Jaleel
 * Date: 2014/12/28
 * Time: 16:44
 */

class App {
    protected static $controller = 'Home';
    protected static $method     = 'index';
    protected static $pams       = array();

    /**
     * url重写路由的URL地址解析方法
     */
    protected static function parseUrl() {

        //判断是否传了URL
        if (isset($_GET['url'])) {
            $url = trim($_GET['url'], '/');
            //print_r($url);

            $url = explode('/', $url);

            //得到控制器名称
            if (isset($url[0])) {
                self::$controller = $url[0];
                unset($url[0]);
            }

            //得到方法名
            if (isset($url[1])) {
                self::$method = $url[1];
                unset($url[1]);
            }

            //判断是否还其他的参数
            if (isset($url)) {
                self::$pams = array_values($url);
            }
            //print_r($url);
        }
    }

    /**
     * 项目的入口方法
     * @throws Exception
     */
    public static function run() {
        self::parseUrl();

        if (APP == 'app') {

            //得到控制器的路径
            $con_dir = APP . '/controllers/' . self::$controller . ".class.php";
        }

        if (APP == 'web') {

            //得到控制器的路径
            $con_dir = 'controllers/' . self::$controller . ".class.php";
        }

        //判断控制器文件是否存在
        if (file_exists($con_dir)) {
            $c = new self::$controller;
        } else {
            throw new MyException('控制器不存在！');
        }

        //执行方法
        if (method_exists($c, self::$method)) {
            $m = self::$method;
            $pam_num = count(self::$pams);
            $pam = array();
            if ($pam_num > 0) {
                if ($pam_num == 1) {
                    if (is_numeric(self::$pams[0])) {
                        $pam['id'] = self::$pams[0];
                    } else {
                        throw new MyException('非法参数');
                    }
                } else if ($pam_num > 1 && $pam_num % 2 == 0) {
                    for ($i = 0; $i < $pam_num; $i += 2) {
                        $pam[self::$pams[$i]] = self::$pams[$i+1];
                    }
                } else {
                    throw new MyException('非法参数');
                }

                $c->$m($pam);
            } else {
                $c->$m();
            }

        } else {
            throw new MyException('方法不存在！');
        }
    }

    /**
     * 自动加载类方法
     * @param $className
     * @throws Exception
     */
    public static function myAutoLoader($className) {

        if (APP == 'app') {

            //控制器所在的目录
            $controller = APP . '/controllers/' . $className . '.class.php';

            //模型所在的目录
            $model = APP . '/models/' . $className . '.class.php';

            //项目的核心目录
            $core = 'core/' . $className . '.class.php';
        }

        if (APP == 'web') {
            //控制器所在的目录
            $controller = 'controllers/' . $className . '.class.php';

            //模型所在的目录
            $model = 'models/' . $className . '.class.php';

            //项目的核心目录
            $core = '../core/' . $className . '.class.php';
        }

        //判断类文件在哪个目录中
        if (file_exists($controller)) {
            require_once $controller;
        } else if (file_exists($model)) {
            require_once $model;
        } else if (file_exists($core)) {
            require_once $core;
        } else {
            throw new Exception('类文件不存在！');
        }
    }
}