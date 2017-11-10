<?php
//单态设计模式  
//需求 : 希望一个类 再一个页面中 只能被实例化一次  
//1. 不能让人在外面随意的new    ---------    封装构造方法 
//2. 只能类的内部 new 得到新对象  ------  写了一个方法 用于得到新的对象 
//3. 外面连对象都没有 怎么访问这个问法 ----- 用静态方法
//4. 又回到原点  ------  定义了个静态属性 判断是否第一次进入

class A{
	static public $obj = null;
	static public $tab;
	private function __construct(){
		//连接数据库
		echo "数据连接成功";
	}
	static public function getObj($tab){
		//初始化表名
		A::$tab = $tab;
		//判断
		if(is_null(A::$obj)){
	    	A::$obj = new A;  //new A  = 对象 
	    }
	    return A::$obj;
	}

	public function select(){
		$table = A::$tab;
	    $sql = "select * from {$table}";
	    echo $sql;
	}
}

$a = A::getObj('stu');  //对象 $a 
$a->select();
$b = A::getObj('grade');  //对象 $a    //  $b = $a;
$b->select();

if($a === $b){
	echo "Y";
}else{
	echo "N";
}

/*$a = new A();
$a = new A();
$a = new A();
$a = new A();
$a = new A();
$a = new A();
*/