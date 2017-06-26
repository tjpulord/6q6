(function(){
	var iLazy = {};
	iLazy.prototype = {
		signin:function(jsn){
			if(jsn.username==""){
				alert("请输入账号信息");
				return false;
			}
			if(jsn.password==""){
				alert("请输入账号密码");
				return false;
			}
			if(jsn.verifys==""){
				alert("请输入验证码");
				return false;
			}
			$.ajax({
			   type: "POST",
			   url: '?s=/home/user/signin_act.html',
			   data: jsn,
			   dataType:'json',
			   success: function(jsn){
			     	switch(jsn.code){
			     		case 2000:
			     		location.href=jsn.data;
			     		break;
			     		default:
			     		alert(jsn.msg)
			     		break;
			     	}
			   }
			}); 
		},
		verify:function(json){
			
		},
		appendResourse:function(source,urlOrClass,id,parent,params){
			var resourse = document.createElement(source);
			switch(source){
				case undefined:
					break;
				case "link":
					resourse.href=urlOrClass;
					break;
				case "script":
					resourse.src=urlOrClass;
					break;
				case "div":
					resourse.setAttribute("class",urlOrClass);
					break;
				default:
					resourse.setAttribute("class",urlOrClass);
					break;
			}
			if(params!=undefined && this.isJson(params)){
				for(var o in params){
					resourse.setAttribute(o,params[o]);
				}
			}
			if(id!=undefined)
			resourse.id = id;
			if(parent!=undefined){
				parent.appendChild(resourse);
				return resourse;
			}
			document.body.appendChild(resourse);
			return resourse;
		},
		isJson:function(o){
			var is = typeof(o) == "object" && Object.prototype.toString.call(o).toLowerCase() == "[object object]" && !o.length; 
			return is;
		},
		alert:function(msg){
			var myModal = document.getElementById("myModal");
			if(myModal==null){
				var div1 = $.iLazy.appendResourse("div","modal fade","myModal",undefined,{"tabindex":"-1","role":"dialog","aria-labelledby":"myModalLabel","aria-hidden":"true"});
				var div2 = $.iLazy.appendResourse("div","modal-dialog",undefined,div1);
				var div3 = $.iLazy.appendResourse("div","modal-content",undefined,div2);
				var div4 = $.iLazy.appendResourse("div","modal-header",undefined,div3);
				var button5 = $.iLazy.appendResourse("button","close",undefined,div4,{"type":"button","data-dismiss":"modal","aria-hidden":"true"});
				var h46 = $.iLazy.appendResourse("h4","modal-title","myModalLabel",div4);
				var div7 = $.iLazy.appendResourse("div","modal-body",undefined,div3);
				var div8 = $.iLazy.appendResourse("div","modal-footer",undefined,div3);
				// var button9 = $.iLazy.appendResourse("button","btn btn-default",undefined,div8);
				var button10 = $.iLazy.appendResourse("button","btn btn-primary",undefined,div8,{"type":"button","data-dismiss":"modal"});
				h46.innerHTML = "系统提示";
				button5.innerHTML= " &times;";
				div7.innerHTML = msg;
				button10.innerHTML = "确定";
			}
			$(".modal-body").html(msg);
		 	$(function (){$('#myModal').modal({
		      	keyboard: true
		    })});
		}
	};

	window.onload=function(){
		$.iLazy.appendResourse("link","http://libs.baidu.com/bootstrap/3.0.3/css/bootstrap.min.css");
		$.iLazy.appendResourse("script","http://libs.baidu.com/bootstrap/3.0.3/js/bootstrap.min.js");
	};
	window.realAlert = window.alert;
	window.alert = function(msg){
		$.iLazy.alert(msg);
	};
	$.extend({iLazy:iLazy.prototype});
})(jQuery);
