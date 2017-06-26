<?php


	/**
	 * listview页面中，获取radio对应的值
	 * @param  [string] $value   [radio索引值]
	 * @return [string]          [radio值对应表]
	 */
	function getRadioValue($value, $radio){
		$replacestr = array(" ", "\t", "\n", "\r");
        $optionlist = explode("],[", str_replace($replacestr, '', $radio));
        if (count($optionlist) > 1) {
            $optionlist[0] = substr($optionlist[0], 1);
            $optstr = $optionlist[count($optionlist)-1];
            $optionlist[count($optionlist)-1] = substr($optstr, 0, -1);
        }
        $res = "--";
        foreach ($optionlist as $it => $vo) {
            $vlist = explode(',', $vo);
            if ($value == $vlist[count($vlist)-1]) {
             	$res = $vlist[0];
             	break;
             }
        }
        echo $res;
	}

	/**
	 * listview页面中，获取select对应的值
	 * @param  [string] $value 	 [select的索引值]
	 * @param  [type]   $dbname  [select字段关联的数据表]
	 * @param  [string] $search  [查询条件]
	 * @return [string] select值
	 */
	function getSelectValue($value, $dbname, $search, $colname=""){
		$sdb = M($dbname);
		if ($value) {
			$comp_if = $sdb->where("$search and id='".$value."'")
						   ->find();
			if ($comp_if) {
				$field = "name";
				if ($colname) {
					$field = $colname;
				}
				echo $comp_if[$field];
			}else{
				echo "--";
			}
		}else{
			echo "";
		}
	}

	/**
	 * 由字段名查找字段名称
	 * @param  [type] $field 	[字段名]
	 * @return [type]           [字段名称]
	 */
	function getStrategyFieldName($field){
		$comp_db = M("strategy_fields");
		if ($field) {
			$comp_if = $comp_db->where("field='%s'", $field)
							   ->find();
			if ($comp_if) {
				echo $comp_if['name'];
			}else{
				echo "没找到产品类型";
			}
		}else{
			echo "";
		}
	}

	/**
	 * 填充select里option
	 * @param  [type] $list [option列表]
	 * @param  [type] $data [选中的value值]
	 * @return [type]       [html字符串]
	 */
	function fill_option($list, $data, $col='name') {
		$html = "";
		$has_item = 0;
		foreach ($list as $key => $val) {
			if (is_array($val))
			{
				$id = $val['id'];
				$name = $val[$col];
				if ($id == $data)
				{
					$selected = "selected";
					$has_item = 1;
				} else
				{
					$selected = "";
				}
				$html = $html . "<option value='{$id}' $selected>{$name}</option>";
			} else {
				if ($val == $data) {
					$selected = "selected";
				} else {
					$selected = "";
				}
				$html = $html . "<option value='{$val}' $selected>{$val}</option>";
			}
		}
		if ($has_item == 0) {
			$html = "<option value='' selected>请选择</option>" . $html;
		}
		return  $html;
	}



/**
* 将字符串转换为数组
*
* @param	string	$data	字符串
* @return	array	返回数组格式，如果，data为空，则返回空数组
*/
function string2array($data) {
	$data = trim($data);
	if($data == '') return array();
	if(strpos($data, 'array')===0){
		@eval("\$array = $data;");
	}else{
		if(strpos($data, '{\\')===0) $data = stripslashes($data);
		$array=json_decode($data,true);
		if(strtolower(CHARSET)=='gbk'){
			$array = mult_iconv("UTF-8", "GBK//IGNORE", $array);
		}
	}
	return $array;
}
/**
* 将数组转换为字符串
*
* @param	array	$data		数组
* @param	bool	$isformdata	如果为0，则不使用new_stripslashes处理，可选参数，默认为1
* @return	string	返回字符串，如果，data为空，则返回空
*/
function array2string($data, $isformdata = 1) {
	if($data == '' || empty($data)) return '';

	if($isformdata) $data = new_stripslashes($data);
	if(strtolower(CHARSET)=='gbk'){
		$data = mult_iconv("GBK", "UTF-8", $data);
	}
	if (version_compare(PHP_VERSION,'5.3.0','<')){
		return addslashes(json_encode($data));
	}else{
		return addslashes(json_encode($data,JSON_FORCE_OBJECT));
	}
}


/**
 * 时间戳2时间字符串
 * @param  string $value [description]
 * @return [type]        [description]
 */
function timeformat($value='', $flag=false)
{
	$res = "";
	if ($value) {
		$res = date("Y-m-d H:i:s", $value);
	}else{
		$res = "--";
	}

	if ($flag) {
		return $res;
	}else{
		echo $res;
	}
}

/**
 * json字符串 获取计划账户信息
 * @param string $value [description]
 */
function getaccountinfo($value='', $flag=false)
{
	if ($value) {
		$planaccountdb = M('relationship');
		$accountary = json_decode($value);
		$result = "";
		foreach ($accountary as $key => $value) {
			$userinfo = $planaccountdb->where("id=$key")->find();
			$result .= ",";
			if ($userinfo) {
				$result .= $userinfo['name'].":".$value."份";
			}else{
				$result .= "匿名:".$value."份";
			}
		}
		if ($flag) {
			return substr($result, 1);
		}
		echo substr($result, 1);
	}else{
		if ($flag) {
			return "";
		}
		echo "--";
	}
}


?>