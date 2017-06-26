<?php

namespace iLazy;

/**
 * 用户的通用类库
 */
class Base extends Common{
    public $mData;
    public $mCtrl;
    public $model;
    public $config;
    /**
     * 初始化类库
     * @param [type] $table 表名字
     * @param [type] $model 数据模型
     */
    public function __construct($model='log')
    {
        parent::__construct($model);
        $this->mData  = array_merge($_POST,$_GET);
        $this->mCtrl  = array(
                'm' => MODULE_NAME,
                'a' => ACTION_NAME,
                'c' => CONTROLLER_NAME,
            );
        $this->model  = $model;
        $this->config = array(
                'table'  => $model,     //表名称
                'key'    => 'id',       //主键
                'fields' => '*',        //字段
                'join'   => array(),    //多表联合
                'order'  => array(),    //排序
                'search' => array(),    //前端搜索显示
                'front'  => array(),    //前端界面输出
            );
        return $this;
    }


    /**
     * @cc 获取用户的配置文件
     * @author peterfzh@126.com
     */
    function _getConfig(){
        $modelpath = APP_PATH."".MODULE_NAME."/".C('ILAZY_MODEL_PATH')."/".$this->model.".config.php";
        if(file_exists($modelpath))
        {
            $require_data = require $modelpath;
            if(array_key_exists('table',$require_data) && $require_data['table'])
            {
                $this->config['table'] = $require_data['table'];
            }
            if(array_key_exists('fields',$require_data) && $require_data['fields'])
            {
                $this->config['fields'] = $require_data['fields'];
            }
            if(array_key_exists('join',$require_data) && $require_data['join'])
            {
                $this->config['join'] = $require_data['join'];
            }
            if(array_key_exists('search',$require_data) && $require_data['search'])
            {
                $this->config['search'] = $require_data['search'];
            }
            if(array_key_exists('order',$require_data) && $require_data['order'])
            {
                $this->config['order'] = $require_data['order'];
            }
            if(array_key_exists('front',$require_data) && $require_data['front'])
            {
                $this->config['front'] = $require_data['front'];
            }
            if(array_key_exists('contrl',$require_data) && $require_data['contrl'])
            {
                $this->config['contrl'] = $require_data['contrl'];
            }
            if(array_key_exists('listctrl',$require_data) && $require_data['listctrl'])
            {
                $this->config['listctrl'] = $require_data['listctrl'];
            }
            if(array_key_exists('listtitle',$require_data) && $require_data['listtitle'])
            {
                $this->config['listtitle'] = $require_data['listtitle'];
            }


            if(!empty($this->mData))
            {
                $search = array();
                foreach ($this->mData as $k => $v) {
                    if($v && $k!="p")  // p代表分页
                    {
                        $search[$k] = array('like',"%$v%");
                    }
                }
                // $this->config['search'] = $search;
                // var_dump($this->config['search']);
                // die();
            }
        }
        return $this;
    }


    /**
     * @cc 自定义方法
     * @author peterfzh@126.com
     */
    function _log($data){
        if (is_array($data) || is_object($data))
        {
            echo("<script>console.log('".json_encode($data)."');</script>");
        }
        else
        {
            echo("<script>console.log('".$data."');</script>");
        }
    }

    /**
     * 返回数据格式
     * @param  [type]  $data [description]
     * @param  integer $code [description]
     * @param  string  $msg  [description]
     * @return [type]        [description]
     */
    function _json($data,$code=2000,$msg='ok'){
        echo json_encode(array(
                'code'  => $code,
                'data'  => $data,
                'msg'   => $msg,
            ));
        die();
    }





}