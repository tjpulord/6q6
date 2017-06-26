$(document).ready(function() {

 
 //搜索框
 $("#search_input").on({
        focus:function(){
                 var weuiSearchBar = $('#search_bar');
                 weuiSearchBar.addClass('weui_search_focusing');
             },
        blur:function(){
               var weuiSearchBar = $('#search_bar');
               weuiSearchBar.removeClass('weui_search_focusing');
                if ($(this).val()) {
                    $('#search_text').hide();
                } else {
                    $('#search_text').show();
                }
             },
        input:function(){
              var searchShow = $("#search_show");
                if ($(this).val()) {
                   searchShow.show();
                } else {
                   searchShow.hide();
                }
             }     
});
     
  $("#search_cancel").on({
        touchend:function(){
              $("#search_show").hide();
              $('#search_input').val('');
        }
  });

  $("#search_clear").on({
        touchend:function(){
              $("#search_show").hide();
             $('#search_input').val('');
        }
  });  

//预约客服
/*$("#appoint_btn").on({
    click:function(){
         $('#dialog1').show().on('click', '#primary1', function () {
                    $('#dialog1').off('click').hide();
                    $('#dialog2').show().on('click', '#primary2', function () {
                    $('#dialog2').off('click').hide();
                });
                    

                }).on('click','#default1',function(){
                    $('#dialog1').off('click').hide();

                });

    }

});*/

//预约客服·邸广照2016/6/28修改
//预约客服
$("#appoint_btn").on({
    click:function(){
         $('#dialog1').show().on('click', '#primary1', function () {

                    $('#dialog1').hide();
                    $('#dialog2').show().on('click', '#primary2', function () {
                      if ($('.weui_dialog_title').html() == '提交成功') {
                        $('#dialog2').hide();
                      } else {
                        $('#dialog1').show();
                        $('#dialog2').hide();
                      }
                });
                    

                }).on('click','#default1',function(){
                   // alert('取消');
                    $('#dialog1').hide();

                });

    }

});

//关于汇鑫底部菜单
$('.weui_tabbar_item').on({

    click:function(){

         $(this).addClass('weui_bar_item_on').siblings('.weui_bar_item_on').removeClass('weui_bar_item_on');
    }
});

//分享图片按钮
$("#share_btn").on({

    click:function(){
            $("#share_img").show();         
    }

});

//取消遮罩层
$(".ok_btn").on({
    click:function(){
         $("#share_img").hide();
    }
})



});

