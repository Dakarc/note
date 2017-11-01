var http = require('http');
var fs = require('fs');

//创建服务器 端口号 80
var server = http.createServer(function(request,response){
	//指定服务器的返回结果200  编码方式 utf-8
	response.writeHead(200,{'content-type':'text/html;charset=utf-8'});
	//异步的方式 读取 index.html的内容
	fs.readFile('index.html',function(err,data){
		//内容返回给客户端
		response.write(data,'utf-8');
		//终断请求
		response.end();
	})

}).listen(80);  //80端口

console.log('okok');
//引入了socket.io模块  监听server
var io = require('socket.io').listen(server);

//自带的事件  如果有客户访问 服务器 自动出发 connection 事件 
io.sockets.on('connection', function(socket){   //必须叫connection
	console.log('jinlaile');
	socket.on('login',function(obj,fn){
		console.log(obj.name);
		fn(obj.name);  //返回给前台(谁请求的就返回给谁) data 的内容是什么  
		//广播 发送给所有连接我的客户端
		socket.broadcast.emit('login1',{name:obj['name']});  //处理自己以外广播所有人
	})

	socket.on('message',function(obj,fn){
		console.log(obj.message);
		fn(obj);
		socket.broadcast.emit('message',obj);
	})



});

