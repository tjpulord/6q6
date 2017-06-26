$(function(){
    function classpage(){
		$(".mould-2conmid").each(function(){
			var T_H = $(this).height();
				T_Hcircle = $(this).find(".circle").height();
			$(this).find(".circle").css("margin-top",(T_H-T_Hcircle)/2);
		});
	    $('.circle').each(function(index, el) {
	        var num = $(this).find('.NUM').text() * 3.6;
	        if (num<=180) {
	            $(this).find('.right').css('transform', "rotate(" + num + "deg)");
	        } else {
	            $(this).find('.right').css('transform', "rotate(180deg)");
	            $(this).find('.left').css('transform', "rotate(" + (num - 180) + "deg)");
	        };
	    });
    };
	var page=0;//当前页
	var pages=1;//总页数
	var ajax=!1;//是否加载中
	var ul_obj = $('#mould-2list1');
	$(window).scroll(function(){
	    if(($(window).scrollTop() + $(window).height() > $(document).height()-40) && !ajax && pages > page){
	        page++;//当前页增加1
	        ajax=!0;//注明开始ajax加载中
	        ul_obj.append('<div class="loading" style="text-align:center"><img src="images/loading.gif" alt="" /></div> ');//出现加载图片
	        $.ajax({
	        	type: 'GET',
	        	url: './json.php',
	        	dataType: 'json',
	        	success: function(data){
	        		var list = data.list;
	        		var li_clone =  "";
	        		for(var i= 0,l = list.length;i<l;i++){
	        			//alert(data['list'][0]['zhi1']);
	        			//处理数据并插入
	        			var zhi1 = list[i].zhi1;
	        				zhi2 = list[i].zhi2;
	        				zhi3 = list[i].zhi3;
	        				zhi4 = list[i].zhi4;
	        				zhi5 = list[i].zhi5;
	        				zhi6 = list[i].zhi6;
	        				zhi7 = list[i].zhi7;
	        				zhi8 = list[i].zhi8;
	        				zhi9 = list[i].zhi9;
	        				zhi10 = list[i].zhi10;
	        				zhi11 = list[i].zhi11;
	        				li_clone +="<li><div class='mould-2tit'><div class='mould-2info'><div><p class='mould-2infoem'>"
	        				if(zhi1){
	        				li_clone +="<span class='b-f small-div'>"+zhi1+"</span>"
	        				}
	        				if(zhi2){
	        					li_clone +="<span class='y-f small-div'>"+zhi2+"</span>"
	        				}
	        				if(zhi3){
	        					li_clone +="<span class='y-f small-div'>"+zhi3+"</span>"
	        				}
	        				li_clone +="</p><p class='mould-2infoti'><span>"+zhi4+"</span></p></div></div>"
	        				li_clone +="<p class='mould-2time'><span class='font-10 color-85'>"+zhi5+"发布</span></p></div>"
	        				li_clone +='<div class="mould-2con clearfix"><div class="mould-2conleft"><ul><li><p><span class="font-18 ycolor">'+zhi6+'</span></p></li><li><p>项目金额（元）</p></li><li class="mould-2line"></li><li><p><span class="font-15 ycolor">'+zhi7+'个</span>月</p></li><li><p>项目期限</p></li><ul></div><div class="mould-2conmid"><div class="circle"><div class="pie_left"><div class="left"></div></div><div class="pie_right"><div class="right"></div></div><div class="mask ycolor"><p><span class="font-14">￥'+zhi8+'</span><br>可投资</p><p class="hide"><span class="NUM">'+zhi9+'</span>%</p></div></div></div><div class="mould-2conright"><ul><li class="mould-2jl"><img height="100%;" src="images/jiangli.png" alt="奖励"></li><li><p class="b-f mould-2num"><span class="font-15">'+zhi10+'</span></p></li><li class="mould-2line"></li><li class="font-15 ycolor">'+zhi11+'</li><li><p>年利率</p></li><ul></div></div>'
	        				li_clone +="<p class='mould-2bot'><a class='b-f' href='/'>查看详情</a></p>"
	        				li_clone +="</li>"

	        		};
					ul_obj.append(li_clone); //克隆元素
				    classpage(); //克隆元素样式
		            $(".loading").addClass("hide");
	                ajax=!1;//注明已经完成ajax加载
	        	},
	            error: function(xhr, type){
	                $(".loading").html("暂无内容！");
	            }
	        });
		}else if(!ajax && pages == page){
			console.log(pages);
			console.log(page);
			page++;//当前页增加1
	        ul_obj.append('<div class="loading" style="text-align:center">暂无内容！</div> ');//出现加载图片
		}else if(!ajax && pages < page){

		};
	});
})