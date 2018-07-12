/**
 * 
 */

var http = require('http');
var https = require('https');
var fs = require('fs');

http.createServer(function(req,res){
  if (req.url=='/') {
    getTitles(res);
  }
}).listen(8000,function(){
  console.log('server start..');
});


async function getTitles(res){  
    try{  
      let [titles, tmpl] = await Promise.all([  
        get_file_content('https://bobo.yimwing.com/show/brandshop/info?brandshop_id=6&uid=10104'),  
        get_file_content('https://bobo.yimwing.com/show/brandshop/record_list?brandshop_id=6&length=20&startid=0&type=1&uid=10104')  
      ]);  
      formatHtml(titles,tmpl, res);  
    } catch(err) {  
      hadError(err, res);          // 调用处理错误的函数。  
    };  
  }  

// 这是通用函数，异步读文件
function get_file_content(file)
{
  return new Promise(function (resolve, reject) {
      
      https.get(file,function(req,res){  
          var html='';  
          req.on('data',function(data){  
              html+=data;  
          });  
          req.on('end',function(){  
              resolve(html );  
          });  
      });  

  });
}


//这是本程序主要逻辑，模板替换后，输出
function formatHtml(titles,html, res) {
 // var html = tmpl.replace('%', ' <li > ' +titles.join('</li > <li >') +' </li > ' );
  res.writeHead(200,{'Content-Type':'text/html;charset=utf-8'});
  res.end(titles+"<br><br><br>" + html);
}

// 通用的错误处理函数
function hadError(err, res) {
  console.log(err);
  res.end('server error.');
}


