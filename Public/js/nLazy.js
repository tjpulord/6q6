/**
 * @author peterfzh@126.com
 * @description 我懒
 * 一个可以快速使用的js框架
 * 包含：angular、jquery
 * 必须: angular.js、jquery
 * MVC：快速的3层结构
 */
var app;
var page;
(function(){
	var iLazy = {};
	iLazy.prototype = {
		//配置信息
		a:{},
		//任务池内容
		r:{},
		//当前窗体的尺寸，滚动条的位置
		s:{},
		//传递的参数
		p:{},
		//里面的内容
		d:{},
		//模型管理
		m:{},
		//展示控制
		v:{
			show:function(o){o==undefined ? this : $(o).show();},
			hide:function(o){o==undefined ? this : $(o).hide();},
			toggle:function(o){o==undefined ? this : $(o).toggle();},
			load:function(){$('#loadingToast').show();setTimeout(function (){$('#loadingToast').hide();}, 2000);},
			loadfnish:function(){$('#loadingToast').hide();},
			more:function(o){

			}
		},
		//控制器
		c:{
			join:function(o){
				$.iLazy.r.o = o;
			}
		},
		//各种格式化
		f:{
			//判断是否为json
			isJson:function(o){
				return typeof(o) == "object" && Object.prototype.toString.call(o).toLowerCase() == "[object object]" && !o.length; 
			},
		},
		//各种包含信息
		h:{
			angularJs:function(){
				return $("script[src*='angular']").length==0 ? false : true;
			},
		},
		//各种事件
		e:{
			_listeners:{},
			// 添加
		    addEvent: function(type, fn) {
		        if (typeof this._listeners[type] === "undefined") {
		            this._listeners[type] = [];
		        }
		        if (typeof fn === "function") {
		            this._listeners[type].push(fn);
		        }    
		        return this;
		    },
		    // 触发
		    fireEvent: function(type) {
		        var arrayEvent = this._listeners[type];
		        if (arrayEvent instanceof Array) {
		            for (var i=0, length=arrayEvent.length; i<length; i+=1) {
		                if (typeof arrayEvent[i] === "function") {
		                    arrayEvent[i]({ type: type });    
		                }
		            }
		        }    
		        return this;
		    },
		    // 删除
		    removeEvent: function(type, fn) {
		    	var arrayEvent = this._listeners[type];
		        if (typeof type === "string" && arrayEvent instanceof Array) {
		            if (typeof fn === "function") {
		                // 清除当前type类型事件下对应fn方法
		                for (var i=0, length=arrayEvent.length; i<length; i+=1){
		                    if (arrayEvent[i] === fn){
		                        this._listeners[type].splice(i, 1);
		                        break;
		                    }
		                }
		            } else {
		                // 如果仅仅参数type, 或参数fn邪魔外道，则所有type类型事件清除
		                delete this._listeners[type];
		            }
		        }
		        return this;
    		}
		},
		/**
		 * 各种内容初始化
		 * @type {Object}
		 */
		i:{
			a:function(a,o){o.p = a; $.iLazy.a.t = ($.iLazy.a.t==undefined) ? 0 : $.iLazy.a.t;},
			s:function(){
				$.iLazy.s = {
					w:$(window).width(),
					h:$(window).innerHeight(),
					t:$(window).scrollTop(),
					o:$(document).height(),
				}
				$(".topImage").html($.iLazy.s.t+"-" + $.iLazy.s.h + "-" + $.iLazy.s.o + "-" + $.iLazy.a.t );
				if($.iLazy.s.t!=0 && $.iLazy.s.o - $.iLazy.s.t - $.iLazy.s.h   < 30)
				{
					if($.iLazy.a.g)
					{
						if($.iLazy.s.t!=$.iLazy.a.t && $.iLazy.s.t  > $.iLazy.a.t)
						{
							alert(page);
							$.iLazy.a.t = $.iLazy.s.t;
							$.iLazy.e.fireEvent("more");
						}
					}
					$.iLazy.a.g = false;
				}
				else{
					$.iLazy.a.g = true;
				}
			}
		},
		/**
		 * 简便使用angularjs
		 * @param  {[type]} p 传递的参数集合 格式：json
		 * @return {[type]}   [description]
		 */
		angularJs:function(p){
			this.v.load();
			this.i.a(p,this);
			if(!this.h.angularJs()){return false;}
			app = angular.module('myApp', [])
				  .controller('myCtrl', function($scope,$rootScope){
				  		$rootScope.data = {};
				  		$rootScope.change = function(d){
				  			$rootScope.data = d;
				  		}
				  });



			switch(this.p.url)
			{
				case undefined:  //未定义
					var dd = this.p.dft;
					app.controller('sc', function($scope){
						$rootScope.change((dd == undefined ? {} : dd)); 
					});
					
					break;
				default: 		 //有参数设定
					// app.controller('myCtrl', function($scope){
					// 	$scope.data = [{"src":"/Public/weixin//image/product1.png","link":"product_info.html","name":"燕赵"},{"src":"/Public/weixin//image/product2.png","link":"product_info.html","name":"燕赵1"},{"src":"/Public/weixin//image/product3.png","link":"product_info.html","name":"燕赵2"},{"src":"/Public/weixin//image/product1.png","link":"product_info.html","name":"燕赵"},{"src":"/Public/weixin//image/product2.png","link":"product_info.html","name":"燕赵1"},{"src":"/Public/weixin//image/product3.png","link":"product_info.html","name":"燕赵2"}];
					// });
					break;
			}
			this.v.show(this.p.parent);
			this.v.loadfnish();
			if(this.p.more!=undefined && this.p.more)
			{
				this.e.addEvent("more",function(p){
					if(page==undefined)
					{
						page = 1;
					}
					else
					{
						page++;
					}
					$.iLazy.angularJs(p);
				});
			}
		},
	};
	$.extend({iLazy:iLazy.prototype});
	$.iLazy.i.s();
	$(window).scroll(function(e){$.iLazy.i.s();});
})(jQuery);
