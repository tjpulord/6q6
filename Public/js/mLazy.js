var app;
(function(){
	var mLazy = {};
	mLazy.prototype = {
		data:{},
		trim:function(str){
			return (str==undefined) ? "" : str.replace(/(^\s*)|(\s*$)/g,'');  
		},
		isEmpty:function(str){
			return (this.trim(str)==null || this.trim(str).length==0 || this.trim(str)=="") ? true : false;
		},
		isTel:function(tel){
			return !tel.match(/^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/);
		},
		isEmail:function(mail){
			var filter  = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	 		return filter.test(mail);
		},
		isCard:function(code){
			var city={11:"北京",12:"天津",13:"河北",14:"山西",15:"内蒙古",21:"辽宁",22:"吉林",23:"黑龙江 ",31:"上海",32:"江苏",33:"浙江",34:"安徽",35:"福建",36:"江西",37:"山东",41:"河南",42:"湖北 ",43:"湖南",44:"广东",45:"广西",46:"海南",50:"重庆",51:"四川",52:"贵州",53:"云南",54:"西藏 ",61:"陕西",62:"甘肃",63:"青海",64:"宁夏",65:"新疆",71:"台湾",81:"香港",82:"澳门",91:"国外"};var tip="";var pass=true;if(!code||!/^\d{6}(18|19|20)?\d{2}(0[1-9]|1[12])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)$/i.test(code)){tip="身份证号格式错误";pass=false}else if(!city[code.substr(0,2)]){tip="地址编码错误";pass=false}else{if(code.length==18){code=code.split('');var factor=[7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2];var parity=[1,0,'X',9,8,7,6,5,4,3,2];var sum=0;var ai=0;var wi=0;for(var i=0;i<17;i++){ai=code[i];wi=factor[i];sum+=ai*wi}var last=parity[sum%11];if(parity[sum%11]!=code[17]){tip="校验位错误";pass=false}}}
		    return pass;
		},
		isJson:function(o){
			var is = typeof(o) == "object" && Object.prototype.toString.call(o).toLowerCase() == "[object object]" && !o.length; 
			return is;
		},
		hasAngularjs(){
			var angulars = $("script[src*='angular']");
			return angulars.length==0 ? false : true;
		},
		getmObject:function(o){if(this.isJson(o)){this.data = o;}},
		Activity:{
			parentShow:function(o){
				o==undefined ? this : $(o).show();
			},
			alert:function(o){
				o==undefined ? this : alert(o);
			},
		},
		angularJs:function(params){
			this.getmObject(params);
			if(!this.hasAngularjs()){return false;}
			app = angular.module('myApp', []);
			switch(this.data.url)
			{
				case undefined: //未定义
					var datas = this.data.dft;
					if(this.data.dft==undefined){return false;}
					app.controller('myCtrl', function($scope){
						$scope.data = datas;//
					});
					break;
				default: 		//已经有内容
					app.controller('myCtrl', function($scope){
						$scope.data = [{"src":"/Public/weixin//image/product1.png","link":"product_info.html","name":"燕赵"},{"src":"/Public/weixin//image/product2.png","link":"product_info.html","name":"燕赵1"},{"src":"/Public/weixin//image/product3.png","link":"product_info.html","name":"燕赵2"},{"src":"/Public/weixin//image/product1.png","link":"product_info.html","name":"燕赵"},{"src":"/Public/weixin//image/product2.png","link":"product_info.html","name":"燕赵1"},{"src":"/Public/weixin//image/product3.png","link":"product_info.html","name":"燕赵2"}];
					});
					break;
			}
			this.Activity.parentShow(this.data.parent);
			this.Activity.alert(this.data.alert);
		}
	};
	$.extend({mLazy:mLazy.prototype});
})(jQuery);