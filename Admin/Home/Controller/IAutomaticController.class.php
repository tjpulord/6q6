<?php
namespace Home\Controller;
use Think\Controller;

/**
 * @cc 代码自动生成类
 * @author Administrator
 *
 */
class IAutomaticController extends Controller
{
    private $modelpath, $conpath, $temppath;
    private $modelid, $modelname, $tablename;
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
        $this->modelname = $info['name'];
        $this->tablename = $info['tablename'];
        $this->modelpath = APP_PATH.MODULE_NAME."/".C('ILAZY_MODEL_PATH')."/".$this->modelname.".config.php";
        $controllername  = $this->modelname."Controller";
        $this->conpath   = APP_PATH.MODULE_NAME."/Controller/".$controllername.".class.php";
        $this->temppath  = APP_PATH.MODULE_NAME."/View/".$this->modelname."/";
    }


    /**
     * 自动生成控制类及其模板文件
     * @return [type] [description]
     */
    public function createController()
    {
        if (file_exists($this->modelpath)) {
            $myconifg = require_once $this->modelpath;
        }else{
            exit('没有'.$this->modelname.' 的配置文件');
        }

        if (!file_exists($this->temppath)) {
            mkdir($this->temppath);
        }
        $tres = $this->createTemplates($myconifg);
        $cres = $this->createControllerClass();

        if ($tres>0 && $cres>0) {
            return true;
        }
        return false;
    }


    /**
     * 自动生成控制类文件
     * @return [type] [description]
     */
    private function createControllerClass()
    {
        $classfile = fopen($this->conpath, "w");
        $bytes = fwrite($classfile, $this->controllerclass());
        fclose();
        return $bytes;
    }


    /**
     * 自动生成模板文件
     * @return [type] [description]
     */
    private function createTemplates($data)
    {
        $listfilestr = "";
        $viewfilestr = "";

        $viewfile = fopen($this->temppath."itemedit.html", "w");
        $bytes = fwrite($viewfile, $this->htmlheader());
        // foreach ($data['front'] as $dk => $dv) {
        //     $func = $dv['type'];
        //     if($dv['display'] == "0" || !method_exists($this, $func) ) continue;
        //     $viewfilestr .= $this->$func($dk, $dv);
        // }
        fwrite($viewfile, $viewfilestr);
        $bytes = fwrite($viewfile, $this->htmlfooter());
        fclose($myfile);

        if (!$bytes) {
            return 0;
        }

        // listview 模板文件
        $listfile = fopen($this->temppath."listview.html", "w");
        fwrite($listfile, $this->htmlheader(1));
        fwrite($listfile, $this->htmltitle($data['listtitle']));
        fwrite($listfile, $this->htmlctrl($data));
        fwrite($listfile, $this->htmltableheader($data));
        $bytes = fwrite($listfile, $this->htmlfooter(1));
        fclose($listfile);
        return $bytes;
    }

    // 获取配置文件内容
    public function getConfigData()
    {
        $myconifg = require_once $this->modelpath;
        return $myconifg;
    }

    // 根据数据及其模型id，获得html展示数据值
    public function get($data=array())
    {
        $myconifg = require_once $this->modelpath;
        $fontdata = $myconifg['front'];
        $newdata = array();
        foreach ($fontdata as $fk => $fv) {
            $func = $fv['type'];
            if ($fv['display'] == '0' || !method_exists($this, $func)) {
                continue;
            }
            $star = "";
            if ($fv['mustbe'] == "1") {
                $star = '<span class="c-red">*</span>';
            }
            $newdata[$star.$fv['name']] = $this->$func($data, $fk, $fv);
        }
        if ($data['id']) {
            # code...
            $newdata['id'] = $data['id'];
        }
        return $newdata;
    }

    function text($datavalue, $field, $fieldinfo) {
        extract($fieldinfo);
        $setting = string2array($setting);
        $type = $password=="1" ? 'password' : 'text';
        if ($mustbe) {
            $formattribute .= ' required="required"';
        }
        $valuestr = $datavalue[$field];
        if ($showfunc) {
            $valuestr = $showfunc($valuestr,true);
        }
        $tmpstr = '<input type="'.$type.'" name="info['.$field.']" id="'.$field.'" size="'.$length.'" value="'.$valuestr.'" class="'.$css.'" '.$formattribute.'>';
        return $tmpstr;
    }


    function select($datavalue, $field, $fieldinfo, $ename="info") {
        extract($fieldinfo);
        $setting = string2array($setting);
        $size = $setting['size'];
        if ($mustbe) {
            $formattribute .= ' required="required"';
        }
        $tmpstr = '<div class="select-box "><select name="'.$ename.'['.$field.']" id="'.$field.'" class="'.$css.'" '.$formattribute.'>';
        if ($options) {
        $optionlist = explode(",", $options);
        	$tmpstr .= fill_option($optionlist, $datavalue[$field]);
        }elseif ($joindb) {
            $opdb = M($joindb);
            $opdata = $opdb->where($where)->select();
            if ($colname) {
                $tmpstr .= fill_option($opdata, $datavalue[$field], $colname);
            }else{
                $tmpstr .= fill_option($opdata, $datavalue[$field]);
            }
        }
        $tmpstr .= '</select></div>';
        return $tmpstr;
    }


    function searches($datavalue, $field, $fieldinfo, $ename="info"){
        extract($fieldinfo);
        $setting = string2array($setting);
        $size = $setting['size'];
        if ($mustbe) {
            $muststr = ' required="required" ';
        }
        $valuestr = $datavalue[$field];
        if ($showfunc) {
            $valuestr = $showfunc($valuestr,true);
        }

        $tmpstr = "";
        $colname = $colname?$colname:'name';
        if ($valuestr) {
            $opdb = M($joindb);
            $searchdata = $opdb->where(array('id'=>$valuestr))->find();
            $valuestr = $searchdata[$colname];
        }
        $tmpstr = '<input type="text" class="'.$css.'" value="'.$valuestr.'" id="'.$field.'" '.$formattribute.$muststr.'>';
        if ($formattribute=="") {
            $tmpstr .= '<input type="hidden" name="'.$ename.'['.$field.']"  value="" />';
            $tmpstr .= '<div class="hint-block">
                            <ul class="hint-ul">

                            </ul>
                        </div>';
            $tmpstr .= '<script type="text/javascript">
                        $("#'.$field.'").focus(function(){
                            animteDown();
                        });
                        $("#'.$field.'").blur(function(){
                            setTimeout(function(){
                                animateUp();
                            },200);
                        });
                        $("#'.$field.'").keyup(function(event){
        $.ajax({
            url: "interface.php?s=/home/Test/dizzsearch",
            type: "post",
            dataType: "json",
            data: {name: $(this).val(), db:"'.$joindb.'", colunm:"'.$colname.'"},
            success: function(data){
                var $hintBlock = $(".hint-block");
                var $hintUl = $(".hint-ul");
                $hintUl.empty();
                if (data.code == 2000) {
                    for(var i = 0; i < data.data.length; i++) {
                        $hintUl.append("<li value="+data.data[i][\'id\']+">"+ data.data[i]["'.$colname.'"] +"</li>");
                    }
                }else{
                    $hintUl.append("<li>"+ data.data +"</li>");
                }
                $hintUl.delegate("li","click",function(){
                    var text = $(this).text();
                    var tid = $(this).attr("value");
                    $("#'.$field.'").val(text);
                    $("input[name=\''.$ename.'['.$field.'\']").val(tid);
                    animateUp();
                });
            }
        });
                        });

                </script>';
        }
        return $tmpstr;
    }


    function radio($datavalue, $field, $fieldinfo, $ename="info")
    {
        extract($fieldinfo);
        $setting = string2array($setting);

        $tmpstr = "";
        if ($optionr) {
            $optionlist = $this->opt2array($optionr);
            foreach ($optionlist as $it => $vo) {
                $selected = "";
                if ($datavalue[$field] == $vo) {
                    $selected = 'checked="true"';
                }
                $tmpstr .= ' <input type="radio" name="'.$ename.'['.$field.']" value="'.$vo.'" class="'.$css.'" '.$selected.' '.$formattribute.' />'.$it;
            }
        }
        return $tmpstr;
    }


    function checkbox($datavalue, $field, $fieldinfo)
    {
        extract($fieldinfo);
        $setting = string2array($setting);

        if ($mustbe) {
            $formattribute .= ' required="required"';
        }
        $tmpstr = "";
        if ($optionr) {
            $optionlist = $this->opt2array($optionr);
            foreach ($optionlist as $it => $vo) {
                $selected = "";
                if ($datavalue[$field] == $vo) {
                    $selected = 'checked="true"';
                }
                $tmpstr .= ' <input type="checkbox" name="info['.$field.']" value="'.$vo.'" class="'.$css.'" '.$selected.' />'.$it;
            }
        }
        return $tmpstr;
    }


    function file($datavalue, $field, $fieldinfo)
    {
    	extract($fieldinfo);
        $setting = string2array($setting);

        if ($mustbe) {
            $formattribute .= ' required="required"';
        }
        $tmpstr = '<input type="text" name="info['.$field.']" id="'.$field.'" value="'.$datavalue[$field].'" class="'.$css.'" '.$formattribute.' />';
        $tmpstr .= '
            <script language="JavaScript">
                var uploadUrl = "./upload_json.php";
                KindEditor.ready(function(K){
                    var editor = K.editor({allowFileManager : true,uploadJson:uploadUrl });
                    K("#'.$field.'").click(function() {
                        editor.loadPlugin("image", function() {
                            editor.plugin.imageDialog({
                                showRemote : false,
                                imageUrl : K("#'.$field.'").val(),
                                clickFn : function(url, title, width, height, border, align) {
                                    K("#'.$field.'").val(url);
                                    editor.hideDialog();
                                }
                            })
                        })
                    });
                });
            </script>
        ';

        return $tmpstr;
    }


    function editor($datavalue, $field, $fieldinfo) {
        extract($fieldinfo);
        $setting = string2array($setting);
        $width = $width ? $width : "100%";
        $height = $height ? $height : "300px";
        $tempstr = '<script type="text/javascript">
        KindEditor.ready(function(K) {
            var editor1 = K.create('.$field.', {
                uploadJson : "./upload_json.php",
                allowFileManager : true,
                width:"'.$width.'",
                height:"'.$height.'",
                afterCreate: function(){
                    this.sync();
                },
                afterBlur: function(){
                    this.sync();
                }
            });
            //prettyPrint();
        });
            </script>';
        $tempstr .= '
                <textarea name="info['.$field.']" id="'.$field.'" style="width:'.$width.';height:'.$height.';">'.$datavalue[$field].'</textarea>';

        return $tempstr;
    }


    function textarea($datavalue, $field, $fieldinfo) {
        extract($fieldinfo);
        $setting = string2array($setting);

        $tmpstr = '<textarea name="info['.$field.']" style="overflow:scroll;overflow-y:hidden;overflow-x:hidden;width:100%; height:200px;"  class="'.$css.'" '.$formattribute.'>'.$datavalue[$field].'</textarea>';
        return $tmpstr;
    }

    function minmax($datavalue, $field, $fieldinfo) {
        extract($fieldinfo);
        $setting = string2array($setting);

        if ($datatype == "text") {
            $tmpstr = '<input type="text" name="info['.$min.']" style="width:43%" class="'.$css.'" value="'.$datavalue[$min].'" /> —— ';
            $tmpstr .='<input type="text" name="info['.$max.']" style="width:43%" class="'.$css.'" value="'.$datavalue[$max].'" />';
        }elseif($datatype == "datetime"){
            $tmpstr = '<input type="date" name="info['.$min.']" style="width:43%" class="'.$css.'" value="'.$datavalue[$min].'" /> —— ';
            $tmpstr .='<input type="date" name="info['.$max.']" style="width:43%" class="'.$css.'" value="'.$datavalue[$max].'" />';
        }
        return $tmpstr;
    }

    function datetime($datavalue, $field, $fieldinfo) {
        extract($fieldinfo);
        $setting = string2array($setting);

        if ($mustbe) {
            $formattribute .= ' required="required"';
        }
        $tmpstr = '<input type="date" name="info['.$field.']" style="width:'.$width.'px;height:'.$height.'px;" class="'.$css.'" '.$formattribute.' value="'.$datavalue[$field].'" />';
        return $tmpstr;
    }

    function dtime($datavalue, $field, $fieldinfo){
        extract($fieldinfo);
        $setting = string2array($setting);

        if ($mustbe) {
            $formattribute .= ' required="required"';
        }
        $tmpstr = '<input type="text" name="info['.$field.']" onfocus="WdatePicker({skin:\'whyGreen\',dateFmt:\'H:mm:ss\'})" class="Wdate" '.$formattribute.' value="'.$datavalue[$field].'" />';
        return $tmpstr;
    }


    //  将radio中选项字符串转化为数组
    function opt2array($optionr)
    {
        $replacestr = array(" ", "\t", "\n", "\r");
        $optionlist = explode("],[", str_replace($replacestr, '', $optionr));
        if (count($optionlist) > 1) {
            $optionlist[0] = substr($optionlist[0], 1);
            $optstr = $optionlist[count($optionlist)-1];
            $optionlist[count($optionlist)-1] = substr($optstr, 0, -1);
        }
        $optarray = array();
        foreach ($optionlist as $it => $vo) {
            $vlist = explode(',', $vo);
            $optarray[$vlist[0]] = $vlist[count($vlist)-1];
        }

        return $optarray;
    }

    // template html 页面header
    private function htmlheader($cate = 0)
    {
        $tmpstr = '<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta name="renderer" content="webkit|ie-comp|ie-stand">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
<meta http-equiv="Cache-Control" content="no-siteapp" />
<link href="__PUBLIC__/css/H-ui.min.css" rel="stylesheet" type="text/css" />
<link href="__PUBLIC__/css/H-ui.admin.css" rel="stylesheet" type="text/css" />
<link href="__PUBLIC__/lib/icheck/icheck.css" rel="stylesheet" type="text/css" />
<link href="__PUBLIC__/lib/Hui-iconfont/1.0.1/iconfont.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="__PUBLIC__/css/default.css" />
<link rel="stylesheet" href="__PUBLIC__/editor/plugins/code/prettify.css" />

<link href="__PUBLIC__/lib/webuploader/0.1.5/webuploader.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="__PUBLIC__/lib/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript" src="__PUBLIC__/editor/kindeditor.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/kindeditor.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/lang/zh_CN.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/suggest.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/searches.js"></script>
<script type="text/javascript" src="__PUBLIC__/lib/My97DatePicker/WdatePicker.js"></script>
<title>添加代理</title>
</head>
<body>
<div class="pd-20">
    <form method="post" class="form form-horizontal" id="form-article-add">
        <input type="hidden" name="id" id="id" value="{{$data.id}}">
        <?php unset($data["id"]) ?>
        <foreach name="data" item="v" key="k" >
            <div class="row cl">
                <label class="form-label col-2">{{$k}}</label>
                <div class="formControls col-5"><?php $k =preg_replace("/<(.*?)>(.*?)<(\/.*?)>/si","",$k);  echo str_replace("id=","placeholder=\"请输入$k\" id=",$v); ?>
                </div>
            </div>
        </foreach>';


        $tmpstr1 = '<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta name="renderer" content="webkit|ie-comp|ie-stand">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
<meta http-equiv="Cache-Control" content="no-siteapp" />
<!--[if lt IE 9]>
<script type="text/javascript" src="Public/lib/html5.js"></script>
<script type="text/javascript" src="Public/lib/respond.min.js"></script>
<script type="text/javascript" src="Public/lib/PIE_IE678.js"></script>
<![endif]-->
<link href="__PUBLIC__/css/H-ui.min.css" rel="stylesheet" type="text/css" />
<link href="__PUBLIC__/css/H-ui.admin.css" rel="stylesheet" type="text/css" />
<link href="__PUBLIC__/css/style.css" rel="stylesheet" type="text/css" />
<link href="__PUBLIC__/lib/Hui-iconfont/1.0.1/iconfont.css" rel="stylesheet" type="text/css" />';

        if ($cate) {
            return $tmpstr1;
        }
        return $tmpstr;
    }

    // templates html 页面底部代码
    private function htmlfooter($cate = 0)
    {
        $tmpstr = '<div class="row cl">
            <div class="col-8 col-offset-2">
                <input type="hidden" name="doSubmit" value="ok">
                <input type="button" name="doSubmits" id="doSubmit" class="btn btn-primary radius Hui-iconfont" value="确定&#xe6a7;">
                <button onClick="layer_close(\'form-article-add\')" class="btn btn-default radius" type="button">&nbsp;&nbsp;取消&nbsp;&nbsp;</button>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript" src="__PUBLIC__/lib/jquery/1.9.1/jquery.min.js"></script>
<script src="/Public/weixin/js/ilazyui.js?json=no"></script>
<script>
$.iLazy.checkform("form-article-add");
</script>
<script type="text/javascript" src="__PUBLIC__/lib/layer/1.9.3/layer.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/mLazy.js"></script>
<if condition="$save">
<script type="text/javascript">
layer.msg("保存成功",{icon: 6,time:1000},function(){
    layer_close();
});

</script>
</if>

<script type="text/javascript">

/*关闭弹出框口*/
function layer_close(){
    var index = parent.layer.getFrameIndex(window.name);
    parent.layer.close(index);
}
$(document).ready(function() {
    $("#doSubmit").bind("click",function(){

        $("#form-article-add").submit();
    });
});
</script>
</body>
</html>';


    $tmpstr1 = '
<script type="text/javascript" src="__PUBLIC__/lib/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript" src="__PUBLIC__/lib/layer/1.9.3/layer.js"></script>
<script type="text/javascript" src="__PUBLIC__/lib/My97DatePicker/WdatePicker.js"></script>
<script type="text/javascript" src="__PUBLIC__/lib/datatables/1.10.0/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/common.js"></script>
<script type="text/javascript">
function jump_other(title,url){
    var w = w == undefined ? 800 : w;
    var h = h == undefined ? 600 : h;
    var index = layer.open({
        type: 2,
        area: [w+"px", h +"px"],
        title: title,
        content: url,
        success: function(layero, index){
            console.log(layero, index);
        },
        yes: function(index, layero){
            //do something
            layer.close(index); //如果设定了yes回调，需进行手工关闭
        },
        end:function(index,laero){
            location.replace(location.href);
        }
    });
    layer.full(index);
}
/*添加*/
function article_add(title,url,w,h){
    w = w == undefined ? 800 : w;
    h = h == undefined ? 600 : h;
    var index = layer.open({
        type: 2,
        area: [w+"px", h +"px"],
        title: title,
        content: url,
        success: function(layero, index){
            console.log(layero, index);
        },
        yes: function(index, layero){
            //do something
            layer.close(index); //如果设定了yes回调，需进行手工关闭
        },
        end:function(index,laero){
            location.replace(location.href);
        }
    });
    layer.full(index);
}
/*编辑*/
function article_edit(title,url,id,w,h){
    w = w == undefined ? 800 : w;
    h = h == undefined ? 600 : h;
    var index = layer.open({
        type: 2,
        area: [w+"px", h +"px"],
        title: title,
        content: url,
        success: function(layero, index){
            console.log(layero, index);
        },
        yes: function(index, layero){
            //do something
            layer.close(index); //如果设定了yes回调，需进行手工关闭
        },
        end:function(index,laero){
            location.replace(location.href);
        }
    });
    layer.full(index);
}
/*删除*/
function article_del(title,url){
    layer.confirm("确认要删除吗？",function(index)
    {
        $.getJSON(url,
            function(msg){
                if(msg.code == 2000)
                {
                    layer.msg("已删除!",{icon:6,time:1000});
                    location.replace(location.href);
                }else
                {
                    layer.msg("删除失败!",{icon:5,time:1000});
                }
        });
    });
}

</script>
<script type="text/javascript">
var search = <?php if(empty($sdata)){echo "\"\"";}else{echo json_encode($sdata);}?>;
$(document).ready(function(){
    if(search!="")
    {
        for(var o in search)
        {
            $("#"+o+"").val(search[o]);
        }
    }
});
</script>
</body>
</html>';

        if ($cate) {
            return $tmpstr1;
        }
        return $tmpstr;
    }


    // listview 页面title部分
    private function htmltitle($titlestr)
    {
        $titlelist = explode("，", $titlestr);
        $title = "";
        if (count($titlelist) > 0) {
            $title = $titlelist[count($titlelist)-1];
        }
        $tmpstr = '
    <title>'.$title.'</title>
</head>
<body>

<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i>'.implode(' <span class="c-gray en">&gt;</span> ', $titlelist).' <a class="btn btn-success radius r mr-20" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" >
<i class="Hui-iconfont">&#xe68f;</i></a></nav>';

        return $tmpstr;
    }


    // 生成select
    private function createselect($selectopt, $field)
    {
        $css = $selectopt['type'] == "select" ? $selectopt['css']:"select";
        $colname = $selectopt['colname'];
        $tmpstr = '<div class="select"><select name="search['.$field.']" id="'.$field.'" class="'.$css.'" >';
        if ($selectopt['type'] == "select") {
            if ($selectopt['joindb']) { // 根据数据库动态生成
                $tmpstr .=  "<?php
            \$options = M('".$selectopt['joindb']."')->where('".$selectopt['where']."')->select();
        ?>";
                if ($colname) {
                    $tmpstr .= "{{:fill_option(\$options, '', '".$colname."')}}";
                }else{
                    $tmpstr .= "{{:fill_option(\$options, '')}}";
                }
            // $tmpstr .= var_dump($optionlist);
            }else{
                $optionlist = explode(",", $selectopt['options']);
                $tmpstr .= fill_option($optionlist, '');
            }
        }elseif ($selectopt['type'] == "radio") {
            $tmplist = $this->opt2array($selectopt['optionr']);
            $optionlist = array();
            foreach ($tmplist  as $okey => $oval) {
                $dbsyle = array('id'=>$oval, 'name'=>$okey);
                array_push($optionlist, $dbsyle);
            }
            $tmpstr .= fill_option($optionlist, '');
        }
        $tmpstr .= "</select></div>";
        return $tmpstr;
    }

    //<!-- 页面控制器开始 -->
    private function htmlctrl($info)
    {
        $tmpstr = '<div class="pd-20">
        <div class="text-c">
        <form id="searchForm" method="post">';

        $serch = "<ul>";
        foreach ($info['front'] as $key => $value) {
            if ($value['search']) {
                $search .="<li>";
                if ($value['type'] == "select" || $value['type'] == "radio") {
                    $search .= "<label>".$value['name'].":</label>";
                    $search .= $this->createselect($value, $key);
                //     $search .= $this->select(array(), $key, $value, "search", false);
                // }elseif ($value['type'] == "radio") {
                //     $search .= $this->radio(array(), $key, $value, "search");
                }else{
                    switch ($value['search']) {
                        case '1':
                            $search .= "<div  class='searchForm-input'><label>".$value['name'].":</label><input class='input-text' type='text' name='search1[".$key."]' placeholder='".$value['name']."'></div>";
                            break;
                        case '2':
                            $search .= "<div  class='searchForm-input'><label>".$value['name'].":</label><input class='input-text' type='text' name='search[".$key."]' placeholder='".$value['name']."'></div>";
                            break;
                        case '3':
                            $search .= "<div  class='searchForm-input'><label>".$value['name'].":</label><input  class='input-text' type='text' name='search3[".$key."]' placeholder='".$value['name']."(max)'>";
                            $search .= "<input type='text' class='input-text' name='search2[".$key."]' placeholder='".$value['name']."(min)'></div>";
                            break;
                        default:
                            break;
                    }
                }
                $search .="</li>";
            }
        }
        $search = str_replace("required=\"required\"","",$search);
        if ($search && $search !="<ul>") {
            $search .= '<li><button name="" id="" class="btn btn-success" type="submit" ><i class="Hui-iconfont">&#xe665;</i> 搜索</button></li></ul>';
        }
        $search  = str_replace(array('disabled','<div class="select">','</select></div>'),array('','','</select>'),$search);
        $tmpstr .= $search;
        $tmpstr .= '</form>
    <script type="text/javascript" src="__PUBLIC__/lib/jquery/1.9.1/jquery.min.js"></script>
    <script src="/Public/weixin/js/ilazyui.js?json=no"></script>
    <script>$.iLazy.FillDataUseJson(<?php $json=array_merge($_POST,$_GET);if($json){echo json_encode($json);}else{echo \'""\';}?>,true);</script>
    <div class="text-c">
    <form action="" method="post" id="submitForm">
    <div class="cl pd-5 bg-1 bk-gray mt-20">
    <span class="l">';

        foreach ($info['contrl'] as $key => $value) {
            $tmpstr .= '<a class="btn btn-primary radius" onclick="'.$value['js'].'(\''.$value['lable'].'\',\'.<?php echo U(\''.$value['ctrl'].'\'); ?>\')" href="javascript:;"><i class="Hui-iconfont">'.$value['icon'].'</i>'.$value['lable'].' </a>';
        }
    $tmpstr .='
    </span>
    <span class="r"><span class="label label-success radius">共有数据：<strong>{{$list|count}}</strong> 条</span></span> </div>';
        return $tmpstr;
    }


    // html模板表格头部
    private function htmltableheader($tabinfo)
    {
        $tmpstr = '
    <div class="mt-20">
        <table class="table table-border table-bordered table-bg table-hover table-sort">
            <thead><tr class="text-c">';

        foreach ($tabinfo['front'] as $key => $value) {
            if ($value['listshow'] == "1") {
                if ($value['width']) {
                    $tmpstr .= "<th width=".$value['width']."% >".$value['name']."</th>";
                }else{
                    $tmpstr .= '<th>'.$value['name'].'</th>';
                }
            }
        }
        if (!empty($tabinfo['listctrl'])) {
            $tmpstr .= '<th>操作</td>';
        }
        $tmpstr .= '</tr>
            </thead>
            <tbody>
                <volist name="list" id="vo">
                <tr class="text-l">';
        foreach ($tabinfo['front'] as $key => $value) {
            if ($value['listshow'] == "1") {
                // 增加对select、radio等类型的数据解析
                if ($value['type'] == 'radio') {
                    $tmpstr .= '
                        <td>{{:getRadioValue($vo['.$key.'], "'.$value['optionr'].'")}}</td>';
                }elseif ($value['type'] == 'select' && $value['where'] || $value['type'] == 'searches') {
                    $tmpstr .= '
                        <td>{{:getSelectValue($vo['.$key.'], "'.$value['joindb'].'", "'.$value['where'].'","'.$value['colname'].'")}}</td>';
                }elseif ($value['showfunc']){
                    $tmpstr .= '
                        <td>{{$vo.'.$key.'|'.$value['showfunc'].'=###}}</td>';
                }else{
                    $tmpstr .= '
                        <td>{{$vo.'.$key.'}}</td>';
                }
            }
        }
        if (!empty($tabinfo['listctrl'])) {
            $tmpstr .='
                    <td>';
            foreach ($tabinfo['listctrl'] as $key => $value) {
                if ($value['js']) {
                    $tmpstr .= '<a style="text-decoration:none" class="ml-5" onClick="'.$value['js'].'(\''.$value['lable'].'\',\'.<?php echo U(\''.$value['ctrl'].'\',array(\'id\'=>$vo[\'id\'])); ?>\')" href="javascript:;" title="'.$value['lable'].'">
                                <i class="Hui-iconfont">'.$value['icon'].'</i>'.$value['lable'].'
                            </a>';
                }else{
                    $tmpstr .= '<a style="text-decoration:none" class="ml-5" href="'.$value['ctrl'].'" title="'.$value['lable'].'">
                                <i class="Hui-iconfont">'.$value['icon'].'</i>'.$value['lable'].'
                            </a>';
                }
            }
            $tmpstr .= "</td>";
        }
        $tmpstr .= '</tr>
                </volist>
            </tbody>
        </table>
        <div id="page" class="pagin">{{$page}}</div>
    </div>
    </form>
</div>';

        return $tmpstr;
    }


    // 控制类
    private function controllerclass()
    {
        $tmpstr = '<?php
namespace Home\Controller;
use Think\Controller;
use iLazy\Base as mBase;

/**
 * @cc 自动生成'.$this->modelname.'Controller控制类
 */
class '.$this->modelname.'Controller extends BaseController {
    private $tlb;
    private $base;
    private $iauto;
    /**
     * 初始化参数
     * @param string $model  [description]
     * @param array  $optons [description]
     */
    function __construct()
    {
        parent::__construct();
        $this->tlb   = "'.$this->tablename.'";
        $this->base  = new mBase($this->tlb);
        $this->iauto = new IAutomaticController("'.$this->modelid.'");
    }

    /**
     * @cc '.$this->modelname.'列表
     * @return [type] [description]
     */
    function listview()
    {
        $where = "1";
        $sorder= "id desc";
        $configdata = $this->iauto->getConfigData();
        if ($configdata["listorder"]) {
            $sorder = $configdata["listorder"];
        }
        if ($configdata["listsearch"]) {
            $where = $configdata["listsearch"];
        }
        if (!empty($this->data)) {
            foreach ($this->data as $key => $value) {
                if ($key == "p") {
                    continue;
                }
                $where .= " and $key=\'$value\'";
            }
        }
        if ($this->postdata["search"]) {
            foreach ($this->postdata["search"] as $key => $value) {
                if ($value) {
                    $where .= " and $key=\'$value\'";
                }
            }
        }
        if ($this->postdata["search1"]) {
            foreach ($this->postdata["search1"] as $key => $value) {
                if ($value) {
                    $where .= " and $key like \'%$value%\'";
                }
            }
        }
        if ($this->postdata["search2"]) {
            foreach ($this->postdata["search2"] as $key => $value) {
                if ($value) {
                    $where .= " and $key > \'$value\'";
                }
            }
        }
        if ($this->postdata["search3"]) {
            foreach ($this->postdata["search3"] as $key => $value) {
                if ($value) {
                    $where .= " and $key < \'$value\'";
                }
            }
        }
        $data = $this->base->_where($where)
                     ->_M($this->tlb)
                     ->_order($sorder)
                     ->_ispage(true)
                     ->_getList()
                     ->_getData();
        $this->assign("list",$data["list"]);
        $this->assign("page",$data["page"]);
        $this->display();
    }


    /**
     * @cc '.$this->modelname.'编辑、保存
     * @return [type] [description]
     */
    function itemedit()
    {
        $id = array_key_exists("id",$this->g_params) ? $this->g_params["id"] : "";

        if(array_key_exists("doSubmit", $this->postdata) && $this->postdata["doSubmit"])
        {
            $configdata = $this->iauto->getConfigData();
            $itemdata   = $this->postdata["info"];
            if ($id) {
                foreach ($configdata["front"] as $key => $value) {
                    if ($value["dataupdate"]=="1" && $value["type"] == "auto") {
                        if ($value["function"]) {
                            $itemdata[$key] = $value["function"]();
                        }else{
                            $itemdata[$key] = $value["constant"];
                        }
                    }
                }
                $rst = $this->base->_M($this->tlb)->_data($itemdata)->_where("id=\'$id\'")->_save();
            }else{
                foreach ($configdata["front"] as $key => $value) {
                    if ($value["datacreate"]=="1" && $value["type"] == "auto") {
                        if ($value["function"]) {
                            $itemdata[$key] = $value["function"]();
                        }else{
                            $itemdata[$key] = $value["constant"];
                        }
                    }
                }
                $rst = $this->base->_M($this->tlb)->_data($itemdata)->_add();
            }
            $this->assign("save",$rst);
        }

        if($id)
        {
            $data = $this->base
                  ->_M($this->tlb)
                  ->_where(array("id"=>$id))
                  ->_find()
                  ->_getData();
        }
        $tempdata = $this->iauto->get($data);

        $this->assign("data",$tempdata);
        $this->display();
    }

    /**
     * @cc 删除'.$this->modelname.'数据
     * @return [type] [description]
     */
    function itemdel()
    {
        $id = array_key_exists("id",$this->g_params) ? $this->g_params["id"] : "";
        if($id)
        {
            $data = $this->base
                  ->_M($this->tlb)
                  ->_del($id)
                  ->_getData();
            if(empty($data))
            {
                $this->base->_json(array(),3000,"empty");
            }
            else
            {
                $this->base->_json($data,2000);
            }
        }
        else
        {
            $this->base->_json(array(),3000,"error");
        }
        die();
    }
}';
        return $tmpstr;
    }
}