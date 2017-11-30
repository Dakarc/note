<!DOCTYPE html>
<html>
	<head>
		<title>奔驰的套马杆</title>
		<meta charset="utf-8"/>
		<style>
			.dd{
				width:1000px;
				
			}
			.h1{
				width:100%;
				height:60px;
				background:#179408;
			}
			.h2{
				width:100%;
				height:60px;
				background:#7abcde;
			}
			.num1{
				float:right;
				width:100px;
				line-height:60px;
				background:#ce4624;
				font-size:26px;
				text-align:center;
			}
			.num2{
				float:right;
				width:100px;
				line-height:60px;
				background:#b91354;
				font-size:26px;
				text-align:center;
			}
			img{
				position:absolute;
			}
		</style>
	</head>
	<body>
		<div class="dd">
			<div class="h1"><img src="./images/ma.gif" height="60"><span class="num1">1</span></div>
			<div class="h2"><img src="./images/ma.gif" height="60"><span class="num2">2</span></div>
			<div class="h1"><img src="./images/ma.gif" height="60"><span class="num1">3</span></div>
			<div class="h2"><img src="./images/ma.gif" height="60"><span class="num2">4</span></div>
			<div class="h1"><img src="./images/ma.gif" height="60"><span class="num1">5</span></div>
			<div class="h2"><img src="./images/ma.gif" height="60"><span class="num2">6</span></div>
			<div class="h1"><img src="./images/ma.gif" height="60"><span class="num1">7</span></div>
		</div>
		<?php
			//var_dump($_POST['h']);
			$a = $_POST['h'];
			if(!isset($a)){
				echo "<font size=\"7\" color=\"red\">老板您好 没有下注看个JB</font>";
			}else{
				echo "<font size=\"7\" color=\"red\">老板您好下注{$a}号</font>";
			}
		?>
		<input type="hidden" id="c" value="<?php echo $a; ?>" />
	</body>
	<script>
			var arr = [0,0,0,0,0,0,0];
			var timmer = setInterval(function (){
				//1.找对象
				var c = document.getElementById('c').value;
				//alert(c);
				var img = document.getElementsByTagName('img');
				for(var i = 0;i<=arr.length;i++){
					//2.随机数 Math.floor(Math.random()*10)
					arr[i] =arr[i] + Math.floor(Math.random()*10);
					//改属性
					img[i].style.left = arr[i] + "px";
					if(arr[i] > 850){
						clearInterval(timmer);
						if(c == (i+1)){
							alert("恭喜你"+c+"号赢了");
						}else{
							alert("输了裤衩都输给老杨了"+(i+1)+"号赢了");
						}
					}
				}
			},30)
	</script>
</html>