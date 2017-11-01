//nodejs  http的对象 创建服务器
var http = require('http');  //引入 node 中的一些对象 (模块)
//req 请求  res 响应
var se = function(req,res){
	res.writeHead('200',{'content-type':'text/html;charset=utf-8'}); 
	res.write('httlow nihaoma~~~中文'); //返回内容给客户端
	res.end();

}
console.log('is ok!');
http.createServer(se).listen(80);
