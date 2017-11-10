//事件驱动  nodejs 中 自定义 
//  events
var events = require('events');
var shijian = new events.EventEmitter();

function onlaomeile(){
	console.log('ylsbl');
}

shijian.on('sbl',onlaomeile);   //shijian.on(编程的事件叫啥名,把什么函数变成事件);





shijian.emit('sbl');
