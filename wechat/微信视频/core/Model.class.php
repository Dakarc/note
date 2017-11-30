<?php
/**
 * Created by PhpStorm.
 * User: Jaleel
 * Date: 2015/1/1
 * Time: 11:51
 */

class Model {
    protected static $_instance;
    protected static $_link;
    protected $whereStr;

    //私有方法实现单例模式
    private function __construct() {}

    /**
     * 返回MODEL实例
     * @return Model
     */
    public static function getSingleton() {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }

        self::connect('localhost', 'root', '', 'test');
        return self::$_instance;
    }

    /**
     * 数据库连接
     * @param $host 服务器
     * @param $uname 连接数据库用户名
     * @param $upass 连接数据库密码
     * @param $dbname 数据库名称
     */
    protected static function connect($host, $uname, $upass, $dbname) {
        try {
            @self::$_link = new PDO("mysql:host={$host};dbname={$dbname}", $uname, $upass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
            self::$_link->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $my = new MyException();
            $my->showError($e->getMessage());
        }
    }


    /**
     * 直接查询数据
     * @param $sql sql语句字符串
     * @param array $data 条件数组
     * @return array 返回值
     */
    public function queryString($sql, $data = array()) {
        $stmt = self::$_link->prepare($sql);
        if (empty($data)) {
            $stmt->execute();
        } else {
            $stmt->execute($data);
        }
        return $stmt->fetchAll();
    }

    /**
     * where条件方法
     * @param $whereStr 条件字符串
     * @return $this 返回当前对象
     */
    public function where($whereStr) {
        $this->whereStr = $whereStr;
        return $this;
    }

    /**
     * 查询方法
     * @param $table 查询的数据表
     * @param array $data 条件数组
     * @return array 返回结果集
     */
    public function select($table, $data = array()) {
        $sql = "select * from {$table} ";
        if (isset($this->whereStr)) {
            $sql .= $this->whereStr;
        }
        $stmt = self::$_link->prepare($sql);
        if (empty($data)) {
            $stmt->execute();
        } else {
            $stmt->execute($data);
        }

        return $stmt->fetchAll();
    }

    /**
     * 插入数据
     * @param $table 表名
     * @param array $data 插入数据数组 要求数组下标前带有“:”
     * @return int 成功返回1 失败返回0
     */
    public function insert($table, array $data) {
        $feilds = '';
        $values = '';
        $bind = array();
        foreach ($data as $k=>$v) {
            $feilds .= ltrim($k, ':') . ',';
            $bind[$k] = $v;
            $values .= $k . ",";
        }

        $sql = "insert ignore into {$table}(" . rtrim($feilds, ',') . ") values(" . rtrim($values, ',') . ")";
        try {
            self::$_link->beginTransaction();
            $stmt = self::$_link->prepare($sql);
            $stmt->execute($bind);
            if ($stmt->rowCount() > 0) {
                self::$_link->commit();
                return 1;
            } else {
                $err = $stmt->errorInfo();
                throw new PDOException($err[2]);
            }
        } catch (PDOException $e) {
            self::$_link->rollback();
            $my = new MyException();
            $my->showError($e->getMessage());
        }
    }

    /**
     * 删除方法
     * @param $table 表名称
     * @param array $data 条件数组
     * @return int 返回值
     */
    public function delete($table, array $data) {
        $sql = "delete from {$table} " . $this->whereStr;
        try {
            self::$_link->beginTransaction();
            $stmt = self::$_link->prepare($sql);
            $stmt->execute($data);
            if ($stmt->rowCount() > 0) {
                self::$_link->commit();
                return 1;
            } else {
                $err = $stmt->errorInfo();
                throw new PDOException($err[2]);
            }
        } catch (PDOException $e) {
            self::$_link->rollback();
            $my = new MyException();
            $my->showError($e->getMessage());
        }
    }

    /**
     * 更新方法
     * @param $table
     * @param array $data
     * @return int
     */
    public function update($table, array $data, array $where) {
        $fields = '';
        foreach ($data as $k=>$v) {
            $fields .= ltrim($k, ':') . '=' . $k . ',';
        }

        $sql = "update {$table} set " . rtrim($fields, ',') . ' ' . $this->whereStr;
        try {
            self::$_link->beginTransaction();
            $stmt = self::$_link->prepare($sql);
            $arr = array_merge($data, $where);
            $re = $stmt->execute($arr);
            if ($stmt->rowCount() > 0) {
                self::$_link->commit();
                return 1;
            } else {
                $err = $stmt->errorInfo();
                throw new PDOException($err[2]);
            }
        } catch (PDOException $e) {
            self::$_link->rollback();
            $my = new MyException();
            $my->showError($e->getMessage());
        }
    }

    public function __destruct() {
        self::$_link= null;
    }
}