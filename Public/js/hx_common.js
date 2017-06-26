$(document).ready(function() 
{ 
  $('.return').click(function(event) {
    history.go(-1);
  });
});
var JsonData;
/// ajax 加载数据
function getAjax(types,urls, param)
{
    JsonData ='';
    $.ajax({
      url: urls,
      type: types,
      data: param,
      dataType: 'json',
      async:false,
      beforeSend: function()
      {           
        
      },
      success: function(result)
      {
          JsonData = result;
      },
      faild: function()
      {
           SysMessage('数据加载失败，稍后重试.','');           
           return false;
      },
      complete:function()
      {
                   
      }
    });
}
 //弹出系统消息提示
var fadetime=2000;
function SysMessage(msg, url)
{     
     var alt = '<div class="sys-message">'+msg+'</div>';
      if($('.sys-message').length > 0)
      {         
        $('.sys-message').css('display', 'block');
        $('.sys-message').text(msg);
      }else
      {
         $('body').append(alt); 
      } 
      $('.sys-message').fadeOut(fadetime); 
      if(url !=='')
      {
        location.href = url;
      }      
} 
//采用正则表达式获取地址栏参数
function GetQueryString(name)
{
     var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
     var r = window.location.search.substr(1).match(reg);
     if(r!=null)return  unescape(r[2]); return null;
} 
//字符串时间转时间戳
function getDateTimeStamp(dateStr)
{
 return Date.parse(dateStr.replace(/-/gi,"/"));
}
//本地时间转时间戳
function getLoacalTimeStamp()
{
  return Date.parse(new Date());
}
//时间差计算
function getDiffTime(start, end)
{
  return parseInt(start)-parseInt(end);
}