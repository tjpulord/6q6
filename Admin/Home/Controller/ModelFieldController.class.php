<?php
namespace Home\Controller;
use Think\Controller;
use Home\Controller\IAutomaticController as IAuto;

/**
 * @cc 模型字段管理
 */
class ModelFieldController extends BaseController {

    private $field_db;
    /**
     * 初始化判断
     */
    function __construct()
    {
        parent::__construct();
        $this->model_db = M('model');
    }

    /**
     * @cc 模型字段列表
     * @return [type] [description]
     */
    public function listview(){
        $mid = $this->g_params['id'];
        $info = $this->model_db->where('id=%d', $mid)->find();
        $tabname = $info['tablename'];
        $modelname = $info['name'];

        $list = array();
        $conctrl = array();
        $listctrl= array();
        // 读取配置文件
        $modelpath = APP_PATH."".MODULE_NAME."/".C('ILAZY_MODEL_PATH')."/".$modelname.".config.php";

        if(file_exists($modelpath))
        {
            $myc = require_once $modelpath;
            $myconfig = $myc;
        }else{
            $myfile = fopen($modelpath, "w");
            $myconfig = $this->defaultArray($modelname, $tabname);
            fwrite($myfile, "<?php return ".var_export($myconfig, true)."; ?>");
            fclose($myfile);
        }
        if (array_key_exists("dosubmit", $this->g_params)) {
            $myfile = fopen($modelpath, "w");
            $condata = $this->g_params['info'];
            unset($myconfig['contrl']);
            unset($myconfig['listctrl']);
            foreach ($condata as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $vk => $vv) {
                        if ($vv['lable']) {
                            $myconfig[$key][$vv['lable']] = $vv;
                        }
                    }
                }else{
                    $myconfig[$key] = $value;
                }
            }
            fwrite($myfile, "<?php return ".var_export($myconfig, true)."; ?>");
            fclose($myfile);
        }


        $this->assign('myconfig',$myconfig);
    	$this->display();
    }

    /**
     * @cc 模型字段编辑
     * @return [type] [description]
     */
    public function itemedit(){
        $mid = $this->g_params['id'];
        $info = $this->model_db->where(array('id'=>$mid))->find();
        $tabname = $info['tablename'];
        $modelname = $info['name'];

        $modelpath = APP_PATH."".MODULE_NAME."/".C('ILAZY_MODEL_PATH')."/".$modelname.".config.php";
        if(file_exists($modelpath))
        {
            $myc = require_once $modelpath;
            $myconfig = $myc;
        }else{
            exit('没有'.$modelname.' 的配置文件' );
        }

        $front = $myconfig['front'];
        if ($this->g_params['itemkey']) {
            $itemkey = $this->g_params['itemkey'];
            $item  = $front[$itemkey];
            $this->assign('item',$item);
        }else{
            $column = $this->model_db->query("SHOW COLUMNS FROM td_".$tabname);
            $noin = array();
            foreach ($column as $key => $value) {
                if (!array_key_exists($value['field'], $front)) {
                    $noin[$key] = $value['field'];
                }
            }
            $this->assign('column',$noin);
        }
        $this->display();
    }


    /**
     * @cc 模型字段提交修改
     * @return [type] [description]
     */
    public function itemedit_act()
    {
        $mid = $this->g_params['id'];
        $info = $this->model_db->where(array('id'=>$mid))->find();
        $tabname = $info['tablename'];
        $modelname = $info['name'];
        $modelpath = APP_PATH."".MODULE_NAME."/".C('ILAZY_MODEL_PATH')."/".$modelname.".config.php";
        if(file_exists($modelpath))
        {
            $myc = require_once $modelpath;
            $myconfig = $myc;

            $itemkey = $this->g_params['itemkey'];
            $iteminfo = $this->g_params['info'];
            $myconfig['front'][$itemkey] = $iteminfo;

            $myfile = fopen($modelpath, "w");
            fwrite($myfile, "<?php return ".var_export($myconfig, true)."; ?>");
            fclose($myfile);
            $this->display();
        }else{
            exit('没有'.$modelname.' 的配置文件' );
        }
    }


    /**
     * @cc 删除模型项
     * @return [type] [description]
     */
    public function itemdel()
    {
        $mid = $this->g_params['id'];
        $info = $this->model_db->where('id=%d', $mid)->find();
        $tabname = $info['tablename'];
        $modelname = $info['name'];
        $modelpath = APP_PATH."".MODULE_NAME."/".C('ILAZY_MODEL_PATH')."/".$modelname.".config.php";
        if(file_exists($modelpath))
        {
            $myc = require_once $modelpath;
            $myconfig = $myc;
        }else{
            $this->getJson("删除失败",'没有'.$modelname.' 的配置文件',3000);
        }

        if ($this->g_params['itemkey']) {
            unset($myconfig['front'][$this->g_params['itemkey']]);
            $myfile = fopen($modelpath, "w");
            fwrite($myfile, "<?php return ".var_export($myconfig, true)."; ?>");
            fclose($myfile);
            $this->getJson('删除成功');
        }
        else
        {
            $this->getJson("删除失败",'没有key值',3000);
        }
        die();
    }


    /**
     * @cc 模型数据位置移动
     * @return [type] [description]
     */
    public function moveup()
    {

        $mid = $this->g_params['id'];
        $info = $this->model_db->where('id=%d', $mid)->find();
        $tabname = $info['tablename'];
        $modelname = $info['name'];

        $modelpath = APP_PATH."".MODULE_NAME."/".C('ILAZY_MODEL_PATH')."/".$modelname.".config.php";
        if(file_exists($modelpath))
        {
            $myc = require_once $modelpath;
            $myconfig = $myc;

            $ik = $this->g_params['itemkey'];
            $direct = $this->g_params['direct'];
            $iteminfo = $myconfig['front'];

            $it = "";
            $isfind = 0;
            foreach ($iteminfo as $key => $value) {
                if ($isfind == 1) {
                    $it = $ik;
                    $ik = $key;
                    break;
                }
                if ($key == $ik) {
                    $isfind = 1;
                    if ($direct == "-1") {  //上移
                        break;
                    }
                }else{
                    $it = $key;
                }
            }

            if ($it) {
                $rdata = $iteminfo[$ik];
                unset($iteminfo[$ik]);
                $changedd = array();
                foreach ($iteminfo as $key => $value) {
                    if ($key == $it) {
                        $changedd[$ik] = $rdata;
                    }
                    $changedd[$key] = $value;
                }
                $myconfig['front'] = $changedd;
            }else{
                $this->getJson('', '不能交换', 4000);
            }

            $myfile = fopen($modelpath, "w");
            fwrite($myfile, "<?php return ".var_export($myconfig, true)."; ?>");
            fclose($myfile);
            $this->getJson('', 'success');
        }else{
            // exit('没有'.$modelname.' 的配置文件' );
            $this->getJson('', '没有'.$modelname.' 的配置文件', 4000);
        }

    }


    // 模型配置文件默认配置
    private function defaultArray($modelname, $tabname)
    {
        $rstlist = array('table'     => $tabname,
                        'listtitle'  => '首页，一级菜单，二级菜单',
                        'listsearch' => '');
        $contrl = array(
                    '添加' =>
                        array(
                            'lable' => '添加',
                            'ctrl' => $modelname.'/itemedit',
                            'js' => 'article_edit',
                            'icon' => '&#xe63c;',
                        ),
                    );
        $listctrl = array(
                    '编辑' =>
                        array(
                            'lable' => '编辑',
                            'ctrl' => $modelname.'/itemedit',
                            'js' => 'article_edit',
                            'icon' => '&#xe6df;',
                        ),
                    '删除' =>
                        array(
                            'lable' => '删除',
                            'ctrl' => $modelname.'/itemdel',
                            'js' => 'article_del',
                            'icon' => '&#xe6e2;',
                        ),
                    );
        $rstlist['contrl']   = $contrl;
        $rstlist['listctrl'] = $listctrl;
        return $rstlist;
    }

    /**
     * @cc 生成控制器类
     * @return [type] [description]
     */
    public function createController()
    {
        $mid = $this->g_params['id'];
        $info = $this->model_db->where('id=%d', $mid)->find();

        if ($info['setting']) {
            echo json_encode(array('code'=>4000, 'data'=>"控制器类:".$info['name']."Controller 不允许自动生成"));
            exit();
        }
        $auto_template = new IAuto($mid);
        $res = $auto_template->createController();

        if ($res) {
            echo json_encode(array('code'=>2000, 'data'=>"生成控制器类:".$info['name']."Controller 成功"));
        }else{
            echo json_encode(array('code'=>4000, 'data'=>"生成控制器类:".$info['name']."Controller 失败"));
        }
    }


    /**
     * @cc 一键生成所有模型控制类
     */
    public function createAllControllers()
    {
        $modellist = $this->model_db->where('1')->select();
        foreach ($modellist as $key => $value) {
            if (!$value['setting']) {
                $auto_template = new IAuto($value['id']);
                $res = $auto_template->createController();
                if (!$res) {
                    echo json_encode(array('code'=>4000, 'data'=>"生成控制器类:".$value['name']."Controller 失败"));
                    exit();
                }
            }
        }
        echo json_encode(array('code'=>2000, 'data'=>"一键生成所有模型控制类成功"));
    }

}


