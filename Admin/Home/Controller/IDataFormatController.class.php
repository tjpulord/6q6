<?php
namespace Home\Controller;
use Think\Controller;

/**
 * @cc listview 格式化模型控制类
 * @author Administrator
 *
 */
class IDataFromatController extends Controller
{
    private $modelpath, $conpath, $temppath;
    private $modelid, $modelname;
    private $modeldb;
    /**
     * 模块儿初始化
     */
    function __construct($modelid)
    {
        parent::__construct();
        $this->modelid = $modelid;
        $this->modeldb = M('model');

        $info = $this->modeldb->where('id=%d', $modelid)->find();
        $this->modelname = ucfirst($info['tablename']);
        $this->modelpath = APP_PATH.MODULE_NAME."/".C('ILAZY_MODEL_PATH')."/".$this->modelname.".config.php";
        $controllername  = $this->modelname."Controller";
        $this->conpath   = APP_PATH.MODULE_NAME."/Controller/".$controllername.".class.php";
        $this->temppath  = APP_PATH.MODULE_NAME."/View/".$this->modelname."/";
    }


    // 根据数据及其模型id，获得html展示数据值
    public function get($data=array())
    {
        $myconifg = require_once $this->modelpath;
        $fontdata = $myconifg['front'];
        foreach ($data as $k => $v) {
            $func = $fv['type'];
            if ($fv['listshow'] == '0' || !method_exists($this, $func)) {
                continue;
            }
            
            $data[$k] = $this->$func();
        }
        return $data;
    }


    function select($datavalue, $field, $fieldinfo) {
        extract($fieldinfo);
        $setting = string2array($setting);
        $size = $setting['size'];

        $tmpstr = '<select name="info['.$field.']" id="'.$field.'" class="'.$css.'" '.$formattribute.'>';
        if ($options) {
        $optionlist = explode(",", $options);
        	$tmpstr .= fill_option($optionlist, $datavalue);
        }elseif ($joindb) {
            $opdb = M($joindb);
            $opdata = $opdb->where($where)->select();
            $tmpstr .= fill_option($opdata, $datavalue);
        }
        $tmpstr .= '</select>';
        return $tmpstr;
    }


    function radio($datavalue, $field, $fieldinfo)
    {
        extract($fieldinfo);
        $setting = string2array($setting);

        $tmpstr = "";
        if ($options) {
            $replacestr = array(" ", "\t", "\n", "\r");
            $optionlist = explode("],[", str_replace($replacestr, '', $options));
            if (count($optionlist) > 1) {
                $optionlist[0] = substr($optionlist[0], 1);
                $optstr = $optionlist[count($optionlist)-1];
                $optionlist[count($optionlist)-1] = substr($optstr, 0, -1);
            }
            foreach ($optionlist as $it => $vo) {
                $vlist = explode(',', $vo);
                $radiovalue = $vlist[count($vlist)-1];
                $selected = "";
                if ($datavalue == $radiovalue) {
                    $selected = 'checked="true"';
                }
                $tmpstr .= '<input type="radio" name="info['.$field.']" value="'.$radiovalue.'" class="'.$css.'" '.$selected.' />'.$vlist[0];
            }
        }
        return $tmpstr;
    }

    function textarea($datavalue, $field, $fieldinfo) {
        extract($fieldinfo);
        $setting = string2array($setting);

        $tmpstr = '<textarea name="info['.$field.']" style="width:'.$width.'px;height:'.$height.'px;" class="'.$css.'" '.$formattribute.'>'.$datavalue.'</textarea>';
        return $tmpstr;
    }

    
}