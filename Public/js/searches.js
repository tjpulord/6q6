  //封装改变样式边框
  function animteDown()
  {
    $(".hint-block").slideDown('fast').css({'border':'1px solid #96C8DA','border-top' : '0px', 'box-shadow' : '0 2px 3px 0 rgba(34,36,38,.15)'});
    // $hintSearchContainer.css({'border':'1px solid #96C8DA','border-bottom' : '0px', 'box-shadow' : '0 2px 3px 0 rgba(34,36,38,.15)'});
    $(".hint-ul").empty();
    $(".hint-ul").append("<li>请输入名字进行查找</li>");
  }

  //向上滑动动画
  function animateUp()
  {
    $(".hint-block").slideUp('fast',function(){
      // $hintSearchContainer.css({'border':'1px solid #ccc'});
    });
  }