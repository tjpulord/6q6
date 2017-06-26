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
//ajax文件上传
function fileupload(method,urls, param)
{
    $.ajaxFileUpload
    ({
        url: urls,
        type: method,
        secureuri: false, //一般设置为false
        fileElementId: 'policyFile', // 上传文件的id、name属性名
        dataType: 'json', //返回值类型，一般设置为json、application/json
        data: param,
        async:false,
        // elementIds: elementIds, //传递参数到服务器
        success: function(data, status)
        {
          return data;
        },
        error: function(data, status, e)
        {
            return e;
        }
    });
}


//一键生成所有模型控制类
function onekey(label, url) {
    getAjax('GET', 'admin.php?s=/home/ModelField/createAllControllers', '');
    alert(JsonData.data, url);
}
