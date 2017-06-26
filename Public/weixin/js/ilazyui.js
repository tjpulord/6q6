(function($){
	var iLazy = {};
	iLazy.prototype = {
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
		checkform:function(f){
			$("#"+f).bind('submit', function(event) {
				var a = $(this).find("*[required='required']");
				for(i=0;i<a.length;i++){
					var b=$(this).find("*[required='required']:eq("+i+")");
					if($.iLazy.isEmpty(b.val()))
					{
						if(b.attr("placeholder")!="")
						{
							alert(b.attr("placeholder"));
						}
						else
						{
							alert("带星号的信息必须填写");
						}

						b.focus();
						return false;
					}
				}
				return true;
			});
		},
		FillDataUseJson:function(a,b){
			if(this.isJson(a))
			{
				if(b==true)
				{
					for(var j in a){
						if(this.isJson(a[j]))
						{
							for(var c in a[j])
							{
								$("*[name='"+j+"\["+c+"\]']").val(a[j][c]);
							}
						}
					}
				}

			}
		},
		uploadimage: function(a, b,c) {
			var uploadphoto = document.getElementById("uploadphoto");
			if(uploadphoto==null)
			{
				$("body").append('<input type="file" id="uploadphoto" name="uploadfile" value="" style="position: absolute; top: -300px; left: -300px; height:1px; width:1px; line-height:1px; overflow:hidden; display:none;" />');
			}
			uploadphoto = document.getElementById("uploadphoto");
			$('#uploadphoto').localResizeIMG({
				width: 400,
				quality: 0.5,
				success: function(result) {
					var submitData = {
						base64_string: result.clearBase64,
					};
					$.ajax({
						type: "POST",
						url: "/Lib/Library/iLazy/BaseUpload.php",
						data: submitData,
						dataType: "json",
						success: function(data) {
							if (0 == data.status) {
								$.iLazy.toast(data.content);
								return false
							} else {
								$.iLazy.toast(data.content);
								$(a).attr("src", data.url);
								$(b).val(data.url);
								$(c).attr("style","background-image: url("+data.url+");background-size:100% auto;" );
								return false
							}
						},
						complete: function(XMLHttpRequest, textStatus) {},
						error: function(XMLHttpRequest, textStatus, errorThrown) {
							$.iLazy.toast(textStatus)
						}
					})
				}
			});
			uploadphoto.click()
		},
		uploadmultiimage: function(b){
			var uploadphoto = document.getElementById("uploadphoto");
			if(uploadphoto==null)
			{
				$("body").append('<input type="file" id="uploadphoto" name="uploadfile" value="" style="position: absolute; top: -300px; left: -300px; height:1px; width:1px; line-height:1px; overflow:hidden; display:none;" />');
				uploadphoto = document.getElementById("uploadphoto");
			}
			$("#uploadphoto").localResizeIMG({
				width: 400,
				quality: 0.5,
				success: function(result) {
					var submitData = {
						base64_string: result.clearBase64,
					};
					$.ajax({
						type: "POST",
						url: "/Lib/Library/iLazy/BaseUpload.php",
						data: submitData,
						dataType: "json",
						success: function(data) {
							if (0 == data.status) {
								$.iLazy.toast(data.content);
								return false
							} else {
								$.iLazy.toast(data.content);
								b(data.url);
								// $(b).append('<li class="weui_uploader_file" style="background-image:url('++')"></li>');
								return false
							}
						},
						complete: function(XMLHttpRequest, textStatus) {},
						error: function(XMLHttpRequest, textStatus, errorThrown) {
							$.iLazy.toast(textStatus)
						}
					})
				}
			});
			uploadphoto.click()
		},
		isEmpty:function(v){
			var c = v.replace(/\ +/g,"").replace(/[ ]/g,"").replace(/[\r\n]/g,"");
			return (c=="") ? true : false;
		},
		toast:function(a){
			var alertstr = '<div id="alertilazy"> <div class="weui-mask_transparent"></div><div class="weui-toast"><p class="weui-toast__content">'+a+'</p></div></div>';
			if(document.getElementById("alertilazy")==null)  $("body").append(alertstr);
			$("#alertilazy").css({"left":parseInt(($(window).width()-$("#alertilazy").width())/2)});
			$("#alertilazy").fadeOut(3000, function() {
				if(document.getElementById("alertilazy")!=null){document.getElementById("alertilazy").remove();}
			});
		},
		load:function(s,t){
			var st='<div id="loadingToastilazy" class="weui_loading_toast" style="display: none;"><div class="weui_mask_transparent"></div><div class="weui_toast"><div class="weui_loading"><div class="weui_loading_leaf weui_loading_leaf_0"></div><div class="weui_loading_leaf weui_loading_leaf_1"></div><div class="weui_loading_leaf weui_loading_leaf_2"></div><div class="weui_loading_leaf weui_loading_leaf_3"></div><div class="weui_loading_leaf weui_loading_leaf_4"></div><div class="weui_loading_leaf weui_loading_leaf_5"></div><div class="weui_loading_leaf weui_loading_leaf_6"></div><div class="weui_loading_leaf weui_loading_leaf_7"></div><div class="weui_loading_leaf weui_loading_leaf_8"></div><div class="weui_loading_leaf weui_loading_leaf_9"></div><div class="weui_loading_leaf weui_loading_leaf_10"></div><div class="weui_loading_leaf weui_loading_leaf_11"></div></div><p class="weui_toast_content">'+s+'</p></div></div>';
			if(document.getElementById("loadingToastilazy")==null)  $("body").append(st);
			$("#loadingToastilazy").show();
			setTimeout(function (){
                    document.getElementById("loadingToastilazy").remove();
            }, (t==undefined ? 1000 : t));
		},
		prompts:function(obj){
			this.dialogobj = null;
			this.show = function(){this.dialogobj.show(100);};
			this.hide = function(){this.dialogobj.hide(100);};
			this.remove = function(){$("#dialogpromopilazy").remove()};
			var title = "";
			var tips  = "";
			if(obj.title!=undefined) title = obj.title;
			if(obj.tips!=undefined) tips = obj.tips;
			var dialog = '<div class="weui_dialog_confirm" id="dialogpromopilazy" style="display:none;"><div class="weui-mask"></div><div class="weui-dialog"><div class="weui-dialog__hd"><strong class="weui-dialog__title">'+title+'</strong></div><div class="weui-dialog__bd"><textarea id="ilazystring" class="weui-textarea" placeholder="'+tips+'" rows="3"></textarea></div><div class="weui-dialog__ft"><a href="javascript:;" class="weui-dialog__btn weui-dialog__btn_default">取消</a><a href="javascript:;" class="weui-dialog__btn weui-dialog__btn_primary">确定</a></div></div></div>';
			if(document.getElementById("dialogpromopilazy")==null)  $("body").append(dialog);
			this.dialogobj = $("#dialogpromopilazy");
			this.v = document.getElementById("ilazystring");
			this.confirmbtn = $(".weui-dialog__btn_primary");
			if(typeof(obj.yes)=="function") this.dialogobj.find(".weui-dialog__btn_primary").bind("click",obj.yes);
			if(typeof(obj.cancel)=="function")this.dialogobj.find(".weui-dialog__btn_default").bind("click",obj.cancel);
		},
		confirm:function(obj){
			this.dialogobj = null;
			this.show = function(){this.dialogobj.show(100);};
			this.hide = function(){this.dialogobj.hide(100);};
			var title = "";
			var tips  = "";
			if(obj.title!=undefined) title = obj.title;
			if(obj.text!=undefined) tips = obj.text;
			var dialog = '<div class="weui_dialog_confirm" id="dialogconfirmilazy" style="display:none;"><div class="weui-mask"></div><div class="weui-dialog weui-skin_android"><div class="weui-dialog__hd"><strong class="weui-dialog__title">'+title+'</strong></div><div class="weui-dialog__bd">'+tips+'</div><div class="weui-dialog__ft"><a href="javascript:;" class="weui-dialog__btn weui-dialog__btn_default">取消</a><a href="javascript:;" class="weui-dialog__btn weui-dialog__btn_primary">确定</a></div></div></div>';
			if(document.getElementById("dialogconfirmilazy")==null)  $("body").append(dialog);
			this.dialogobj = $("#dialogconfirmilazy");
			if(typeof(obj.yes)=="function") this.dialogobj.find(".weui-dialog__btn_primary").bind("click",obj.yes);
			if(typeof(obj.cancel)=="function")this.dialogobj.find(".weui-dialog__btn_default").bind("click",obj.cancel);
		},
		/*alert对话框*/
		alert:function(a, f){
			var alertstr = '<div class="weui_dialog_alert" id="weui_dialog_alerttilazy"><div class="weui-mask"></div><div class="weui-dialog"><div class="weui-dialog__hd"><strong class="weui-dialog__title">友情提示</strong></div><div class="weui-dialog__bd">'+a+'</div><div class="weui-dialog__ft"><a href="javascript:;" class="weui-dialog__btn weui-dialog__btn_primary">确定</a></div></div></div>';
			if(document.getElementById("weui_dialog_alerttilazy")==null)  $("body").append(alertstr);
			$("#weui_dialog_alerttilazy").find(".weui-dialog__btn_primary").bind("click",function(){
				document.getElementById("weui_dialog_alerttilazy").remove();
				if (f) {
					f();
				};
			});
		},
		menucolor:function(a){
			if(a.o==undefined){alert("not object");return false;}
			if(a.i==undefined){alert("not item");return false;}
			if(a.n==undefined){alert("not normal");return false;}
			if(a.a==undefined){alert("not active");return false;}
			if(a.d!=undefined){
				if(a.n.indexOf(".")==-1){
					a.o.find(a.i).css({"background":a.n});
					a.d.css({"background":a.a});
				}
				else{
					a.o.find(a.i).removeClass(a.a).removeClass(a.n).addClass(a.n);
					a.d.addClass(a.n);
				}
			}
			a.o.find(a.i).bind('click', function(event) {
				if(a.n.indexOf(".")==-1){
					a.o.find(a.i).css({"background":a.n});
					$(this).css({"background":a.a});
				}
				else{
					a.o.find(a.i).removeClass(a.a).removeClass(a.n).addClass(a.n);
					$(this).addClass(a.n);
				}
			});
		},
		upadownshow:function(a){
			if(a.f==undefined){alert("not object");return false;}
			if(a.t==undefined){alert("not item");return false;}
			if(a.f.attr("show")=="" || a.f.attr("show")==undefined)
			{
				a.f.attr("show","true");
				a.f.html("&#xe6d6;");
				a.t.show();
			}
			else
			{
				a.f.attr("show","");
				a.t.hide();
				a.f.html("&#xe6d5;");
			}
		},
		png:function(){
			var imgs = document.getElementsByTagName("img");
			for(i=0;i<imgs.length;i++)
			{
				var img = $("img:eq("+i+")");
				if(img.attr("src").indexOf('.png')!=-1)
				{
					document.domain
					confirm(img.attr("src"));
				}
			}
		},
		scroll:function(){

		}
	};
	window.onload=function(){
		if($("script[src*='ilazyui.js?']").length==0)
		{
			$.iLazy.load("加载中");
		}
	};
	$(document).ready(function(){$("#loadingToastilazy").remove();});
	if($("script[src*='ilazyui.js?json=no']").length==0)
	{
		window.realAlert = window.alert;
		window.alert = function(msg){
				$.iLazy.alert(msg);
		};
	}
	$.extend({iLazy:iLazy.prototype})
})(jQuery);
