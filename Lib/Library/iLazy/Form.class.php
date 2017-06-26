<?php 
namespace iLazy;
use Think\Controller;
use iLazy\Base as mBase;
/**
* Form的使用和输出
*/
class Form extends Controller
{
	private $result;
	private $dd;
	private $values;
	private $type;
	private $fi	=  array(
					'type'       => 'text',										//类型
					'name'       => '',											//名称
					'tip'        => '',											//提示
					'value'      => '',											//默认值
					'password'   => false,										//是否为密码
					'length'     => 50,											//长度
					'css'        => '',											//css样式
					'range'      => array(1,50),								//数值范围
					'mustbe'     => false,										//是否必填字段
					'search'     => false,										//是否为搜索功能
					'display'    => false,										//前台显示
					'listshow'   => false,										//前台列表显示
					'match'      => '',											//正则验证
					'width'      => '',											//宽度
					'height'     => '',											//高度
					'notpost'    => '',											//错误提示
					'editor'     => '',											//编辑器样式 {Namol,Short}
					'options'    => array(),									//选项内容
					'optionout'  => 'value',									//输出格式值还是名称
					'optiontype' => 'radio',									//选项类型
					'imgsize'    => 20,											//上传大小 单位：M
					'imgtype'    => 'jpg|gif|jpeg|png|bmp',						//图片格式
					'imgmuitl'   => false,										//多图上传
					'imgcount'   => 10,											//附件个数
					'intsize'    => 2,											//小数后几位
					'datetype'   => 'Y-m-d H:i:s',								//日期格式
					'areapanel'  => 'province|city|country',					//省市县联动
					'smsenable'  => false,										//短信验证码
					'union'      => array(),									//关联的那个表的字段
					'ispublic'   => false,										//是否为公用字段
					'publicstr'  => ''											//公用字段内容
				);
	function __construct($data,$id,$onerow=true)
	{
		parent::__construct();
		$this->dd = $data;
		$this->id = $id;
		$this->onerow = $onerow;
		$this->formatForm();
		return $this;
	}

	/**
	 * 魔术写法
	 */
	function __set($k,$v){
		$this->$k = $v;
		return $this;
	}

	/**
	 * 设置值
	 * @param  [type] $str [description]
	 * @return [type]      [description]
	 */
	function value($str)
	{
		$this->values = $str;
		return $this;
	}

	/**
	 * 获取最后的信息
	 * @return [type] [description]
	 */
	function getInfo($islist = true){
		return $this->result;
	}

	/**
	 * 获取执行方法
	 * @return [type] [description]
	 */
	function get_Function($arr = array()){
		if(empty($arr))
		{
			return "--";
		}
		else
		{
			switch ($arr[0]) {
				case 'isfalse':
					if(array_key_exists("if", $arr))
					{
						return $arr['if'][$arr['value']];
					}
					else
					{
						return $arr['value'] == 1 ? '√' : '×';
					}

					break;
				
				default:
					# code...
					break;
			}
		}
		return '--';
	}

	/**
	 * 配置信息
	 * @return [type] [description]
	 */
	function initConfig()
	{	
		foreach ($this->fi as $k => $v) {
			if(array_key_exists($k, $this->dd) && (is_array($this->dd[$k]) ||  strlen($this->dd[$k])>0))
			{
				$this->fi[$k] = $this->dd[$k];
			}
		}
		return $this;
	}

	/**
	 * 格式化输出
	 * @return [type] [description]
	 */
	function formatForm(){
		$this->initConfig();
		// var_dump($this->fi);
		switch ($this->fi['type']) {
			case 'text':	//输入框模式
				$this->getinput();
				break;
			case 'area':	//省市县
				$this->getarea();
				break;
			case 'select':	//选项
				$this->getselects();
				break;
			default:
				# code...
				break;
		}
		return $this;
	}

	/**
	 * 获取下拉菜单、单选、多选
	 * @return [type] [description]
	 */
	function getselects(){
		$select = "";
		if(isset($this->fi['optiontype']))
		{
			switch ($this->fi['optiontype']) {
				case 'select':	//下来菜单
					$select .= "<select ";
					$select .= isset($this->id) ? "name = \"".$this->id."\" id = \"".$this->id."\"" : "";
					$select .= isset($this->fi['css']) ? " class = \"".$this->fi['css']."\"" : "";
					$select .= ">";
					$select .= "<option value=\"\">请选择</option>";
					$select .= $this->getselectvalue();
					$select .= "</select>";
					break;
				
				default:
					break;
			}
		}
		else
		{

		}
		$this->result = $select;
		return $this;
	}

	/**
	 * 获取选项的内容
	 * @return [type] [description]
	 */
	function getselectvalue(){
		$rtn  = "";
		if(isset($this->fi['options']))
		{
			$option = $this->fi['options'];
			switch($option[0]) {
				case 'config':		//从配置文件中获得
					$arr = C($option[1]);
					foreach ($arr as $k => $v) {
						$rtn .= "<option value=\"$k\">$v</option>\n";
					}
					break;
				default:
					# code...
					break;
			}
		}
		return $rtn;
	}

	/**
	 * 获取省市县联动
	 * @return [type] [description]
	 */
	function getarea(){
		$select = '<div class="formControls col-7" id="provincePanel"> </div>';
		$this->result = $select;
		return $this;
	}

	/**
	 * 获取输入框
	 * @return [type] [description]
	 */
	function getinput(){
		$input  = "<input  AUTOCOMPLETE=\"off\"";
		$input .= isset($this->id) ? "name = \"".$this->id."\" id = \"".$this->id."\"" : "";
		$input .= isset($this->fi['name']) ? " placeholder = \"".$this->fi['name']."\"" : "";
		$input .= isset($this->fi['css']) ? " class = \"".$this->fi['css']."\"" : "";
		$input .= isset($this->fi['type']) ? " type = \"".(isset($this->fi['password']) ? ($this->fi['password'] ? "password" : "text") : "text")."\"" : "text";
		$input .= (isset($this->fi['display']) && $this->fi['display']===false) ?  " style=\"display:none;\"" : "";
		$input .= (strlen($this->values)>0) ?  " value=\"".$this->values."\"" : "";
		if($this->onerow){
			$input .= (strlen($this->fi['width'])>0) ?  " style=\"width:".$this->fi['width'].";\"" : "";
		}
		$input .= "/>"; 
		$this->result = $input;
		return $this;
	}
}
 ?>