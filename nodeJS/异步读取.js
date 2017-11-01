//fs  node 操作文件的模块
var fs = require('fs');
var fname = 'aaa.txt';
console.log('dudududu');


fs.readFile(fname,function(err,data){

	console.log(data.toString());   //结果 buffer  缓存 

})

console.log('duwanle');