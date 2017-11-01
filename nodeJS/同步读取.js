//fs  node 操作文件的模块
var fs = require('fs');
var fname = 'aaa.txt';
console.log('dudududu');


var data = fs.readFileSync(fname)   //同步读取

console.log(data.toString());   //结果 buffer  缓存 

console.log('duwanle');