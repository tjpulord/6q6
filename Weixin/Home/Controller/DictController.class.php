<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 邀请控制类
 */
class DictController extends Controller {

	/**
	 * 构造函数
	 */
	function __construct()
    {
    	parent::__construct();
    	# code...
    }


    public function index()
    {
    	$this->dictionary();
    }

    /**
     * 数据字段
     * @param  [String] $tbname [数据库表]
     * @return [Array]         [数据表模版]
     */
    function dictionary(){
        $data = array_merge($_GET,$_POST);
        $tbname = $data['tbname'];
        echo "<title>数据字典</title>";
        $model   = M();
        $db_name = C("DB_NAME");
        echo "<table style='font-size:12px;' width='50%' border=0 cellspacing=1 cellpadding=10 bgcolor='#ffffff'><tr><td width='50%'><b>数据库：$db_name</b><td>";
        $db_rst  = $model->query("SHOW TABLES"); 
        $table_field_name = strtolower("Tables_in_$db_name");
        $tb_lst  = $tbname ? array(array($table_field_name=>$tbname)) : $db_rst;
        echo "<td align=right><select onchange='location.href=this.value;'>";
        echo "<option value='".U()."'>╇请选择数据表</option>";
        foreach ($db_rst as $k => $v) {
            $tablename            = $v[$table_field_name];
            $cmt_rst              = $model->query("SHOW CREATE TABLE $tablename");
            $cmt_str              = $cmt_rst[0]['create table'];
            $cmt_str              = substr($cmt_str,strpos($cmt_str,"COMMENT='")+8);
            $selected = ($v[$table_field_name]==$tbname) ? "selected=\"selected\"" : "";
            echo "<option value='".U($this,array('tbname'=>$v[$table_field_name]))."' $selected> ┗━".$v[$table_field_name]."(".$cmt_str.")</option>";
        }
        echo "</select></td></tr></table>";
        foreach ($tb_lst as $k => $v){
            $dict[]['table_Name'] = $v[$table_field_name];
            $tablename            = $v[$table_field_name];
            $cmt_rst              = $model->query("SHOW CREATE TABLE $tablename");
            $cmt_str              = $cmt_rst[0]['create table'];
            $cmt_str              = substr($cmt_str,strpos($cmt_str,"COMMENT='")+8);
            echo "<table style='font-size:12px;' width='50%' border=0 cellspacing=1 cellpadding=0 bgcolor='#666666'>";
            echo "<tr><td colspan='4' style='border-bottom:solid  1px #000;color:blue;' bgcolor='#ffffff'><b>".$v[$table_field_name]."($cmt_str)</b></td></tr>";
            echo "<tr><td style='border-bottom:solid 1px #000'><b >数据字段</td><td style='border-bottom:solid 1px #000'><b>字段描述</td><td style='border-bottom:solid 1px #000'><b>字段类型</td><td style='border-bottom:solid 1px #000'><b>允许为空</td></tr>";
            $fld_rst            = $model->query("select * from information_schema.columns where  table_schema = '$db_name' and table_name = '$tablename'");
            $dict_child         = array();
            foreach ($fld_rst as $f => $d) {
                $dict_child[]   = self::fieldformat($d);
            }
            $dict[]['Field_List'] = $dict_child;
            echo "</table>";
            echo "<BR><BR>";
        }
    }

     //输出直线并且换行
    function _cl(){
        echo "--------------------------------------------------------------------------------<Br>";
    }

    //字段格式化
    function fieldformat($arr = ""){
        if(is_array($arr)){
            echo "<tr bgcolor='#ffffff'>";
            echo "<td width='20%'>".$arr['column_name']."</td>";
            echo "<td width='40%'>".$arr['column_comment']."</td>"; 
            echo "<td width='20%'>".$arr['column_type']."</td>"; 
            echo "<td width='20%'>".$arr['is_nullable']."</td>"; 
            echo "</tr>";
        }
    }
}