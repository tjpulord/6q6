/**
 * jQuery :  城市联动插件
 * @author   XiaoDong <cssrain@gmail.com>
 *			 http://www.cssrain.cn
 * @example  $("#test").ProvinceCity();
 * @params   暂无
 */
$.fn.ProvinceCity = function(pro,city,cotry){
	var _self = this;
	_self.isfirst = false;
	//定义3个默认值
	_self.data("province",["请选择省份", "0"]);
	_self.data("city1",["请选择城市", "0"]);
	_self.data("city2",["请选择区县", "0"]);
	//插入3个空的下拉框
	_self.append("<span class=\"select-box col-2\"><select class=\"select\" name=\"province\" id=\"province\"></select></span> <BR><BR>");
	_self.append("<span class=\"select-box col-2\"><select class=\"select\" name=\"city\" id=\"city\"></select></span> <BR><BR>");
	_self.append("<span class=\"select-box col-2\"><select class=\"select\" name=\"country\" id=\"country\"></select></span> ");
	//分别获取3个下拉框
	var $sel1 = _self.find("select").eq(0);
	var $sel2 = _self.find("select").eq(1);
	var $sel3 = _self.find("select").eq(2);
	//默认省级下拉
	if(_self.data("province")){
		$sel1.append("<option value='"+_self.data("province")[1]+"'>"+_self.data("province")[0]+"</option>");
	}
	$.each( province , function(index,data){
		$sel1.append("<option value='"+data.area_id+"'>"+data.area_name+"</option>");
	});
	//默认的1级城市下拉
	if(_self.data("city1")){
		$sel2.append("<option value='"+_self.data("city1")[1]+"'>"+_self.data("city1")[0]+"</option>");
	}
	//默认的2级城市下拉
	if(_self.data("city2")){
		$sel3.append("<option value='"+_self.data("city2")[1]+"'>"+_self.data("city2")[0]+"</option>");
	}
	if(pro!=undefined && pro>0)
	{
		$sel1.val(pro);
	}
	//省级联动 控制
	var index1 = "" ;
	$sel1.change(function(){
		// //清空其它2个下拉框
		$sel2[0].options.length=0;
		$sel3[0].options.length=0;
		index1 = this.selectedIndex;
		//默认的1级城市下拉
		if(_self.data("city1")){
			$sel2.append("<option value='"+_self.data("city1")[1]+"'>"+_self.data("city1")[0]+"</option>");
		}
		//默认的2级城市下拉
		if(_self.data("city2")){
			$sel3.append("<option value='"+_self.data("city2")[1]+"'>"+_self.data("city2")[0]+"</option>");
		}
		if(index1==0){	//当选择的为 “请选择” 时
			
		}else{
			parent.layer.load(0, {time: 1000});
			$.getJSON("./admin.php?s=/home/Base/getAreaJs/pid/"+this.value+".html",function(json){
				for(var o in json){
					$sel2.append("<option value='"+json[o].area_id+"'>"+json[o].area_name+"</option>");
				}
				if(!_self.isfirst){
					$sel2.val(city);
					_self.isfirst = true;
				}
			});
		}
	}).change();

	//1级城市联动 控制
	var index2 = "" ;
	$sel2.change(function(){
		$sel3[0].options.length=0;
		index2 = this.selectedIndex;
		//默认的2级城市下拉
		if(_self.data("city2")){
			$sel3.append("<option value='"+_self.data("city2")[1]+"'>"+_self.data("city2")[0]+"</option>");
		}
		if(index2==0){	//当选择的为 “请选择” 时
			
		}else{
			parent.layer.load(0, {time: 1000});
			$.getJSON("./admin.php?s=/home/Base/getAreaJs/pid/"+this.value+".html",function(json){
				for(var o in json){
					$sel3.append("<option value='"+json[o].area_id+"'>"+json[o].area_name+"</option>");
				}
			});
			$sel3.val(cotry);
		}
	}).change();
	// if(city!=undefined && city>0)
	// {
	// 	if($sel2[0].options.length==0)
	// 	{
	// 		$.getJSON("/admin.php?s=/home/Base/getAreaJs/pid/"+pro+".html",function(json){
	// 			for(var o in json){
	// 				$sel2.append("<option value='"+json[o].area_id+"'>"+json[o].area_name+"</option>");
	// 			}
	// 		});
	// 	}
	// 	$sel2.val(city);
	// }
	if(cotry!=undefined && cotry>0)
	{
		$.getJSON("./admin.php?s=/home/Base/getAreaJs/pid/"+city+".html",function(json){
			for(var o in json){
				$sel3.append("<option value='"+json[o].area_id+"'>"+json[o].area_name+"</option>");
			}
			$sel3.val(cotry);
		});
	}
	return _self;
};