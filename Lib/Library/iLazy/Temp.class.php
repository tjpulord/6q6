<?php
namespace iLazy;
use Think\Controller;
use iLazy\Base as mBase;
use iLazy\Form as Form;
/**
 * 模型控制器类
 */
class Temp extends Controller{
    private $base;
    private $ctrl;
    private $action;
    private $model;
    private $modelpath;
    private $cfg;
    /**
     * 初始化参数
     * @param string $model  [description]
     * @param array  $optons [description]
     */
    public function __construct($model='log',$optons=array())
    {
        parent::__construct();
        $this->base = new mBase($model);
        $this->base->_getConfig();
        $this->ctrl   = $this->base->mCtrl['c'];
        $this->action = $this->base->mCtrl['a'];
        $this->model  = $model;
        return $this;
    }


    /**
     * @cc 魔术写法
     * @author peterfzh@126.com
     */
    function __set($key,$value){
        $this->$key = $value;
    }
    /**
     * 输出日志
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    function log($data)
    {
        $this->base->_log($data);
    }

    function setOther($ctrl,$fun,$param,$step)
    {
        return $this;
    }

    /**
     * 初始化
     * @return [type] [description]
     */
    function init()
    {
        $this->cfg = $this->base->config;
        switch ($this->action) {
            case 'listview':        //列表形式
                $this->listview();
                $this->show("iLazy/listview");
                break;
            case 'itemview':        //单条信息展示保存
                $this->itemview();
                $this->show("iLazy/itemview");
                break;
            case 'itemdel':         //删除数据
                $this->itemdel();
                break;
            case 'listfield':       //字段管理
                $this->listfield();
                $this->show("iLazy/listview");
                break;
            default:
                # code...
                break;
        }
        return $this;
    }

    /**
     * 删除某条数据
     * @return [type] [description]
     */
    function itemdel(){
        $id   = array_key_exists('id',$this->base->mData) ? $this->base->mData['id'] : "";
        if($id)
        {
            $data = $this->base
                  ->_M($this->cfg['table'])
                  ->_del($id)
                  ->_getData();
            if(empty($data))
            {
                $this->base->_json(array(),3000,'empty');
            }
            else
            {
                $this->base->_json($data,2000);
            }
        }
        else
        {
            $this->base->_json(array(),3000,'error');
        }
        die();
    }

    /**
     * @cc 展示某条信息
     * @author peterfzh@126.com
     */
    function itemview(){
        $id   = array_key_exists('id',$this->base->mData) ? $this->base->mData['id'] : "";

        $field = $this->getFieldsFromTable();
        //列表抬头
        $this->assign('input',$this->getInputParam($field,$this->cfg['front']));
        if($id)
        {
            $data = $this->base
                  ->_M($this->cfg['table'])
                  ->_find($id)
                  ->_getData();
            $data = M($this->cfg['table'])->where(array('id'=>$id))->find();
            $this->assign('data',$data);
        }

        if(array_key_exists('doSubmit', $this->base->mData) && $this->base->mData['doSubmit'])
        {
            if($id)
            {
                $rst = $this->base->_M($this->cfg['table'])->_data($this->base->mData)->_pri($this->cfg['key'])->_save();
            }
            else
            {
                $rst = $this->base->_M($this->cfg['table'])->_data($this->base->mData)->_add();
            }
            $this->assign('save',$rst);
        }
        $this->assign('show',$this->cfg['front']);
    }

    /**
     * @cc 列表展示
     * @author peterfzh@126.com
     */
    function listview(){
        $where = array_merge($_GET,$_POST);
        unset($where['p']);
        $search = array();
        foreach ($where as $k => $v) {
            if(isset($k) &&  isset($v))
            {
              $search[$k] = array('like',"%$v%");
            }
        }
        $data = $this->base
              ->_M($this->cfg['table'])
              ->_fields($this->cfg['fields'])
              ->_join($this->cfg['join'])
              ->_where($search)
              ->_order($this->cfg['order'])
              ->_ispage(true)
              ->_getList()
              ->_getData();
        $field = $this->getFieldsFromTable();
        //参数
        $this->assign('config',$this->cfg);
        //参数
        $this->assign('sdata',$this->base->mData);
        //列表抬头
        $this->assign('front',$this->getListTitle($field,$this->cfg['front']));
        //表头控制器
        $this->assign('contrl',$this->cfg['contrl']);
        //行业控制器
        $this->assign('listctrl',$this->cfg['listctrl']);

        $this->assign('value',$this->getFieldValue($field,$this->cfg['front']));

        $this->assign('search',$this->getSearchInnerHtml($field,$this->cfg['front']));

        $this->assign('count',$data['count']);

        $this->assign('lists',$this->getListData($field,$this->cfg['front'],$data['list']));

        $this->assign('list',$data['list']);

        $this->assign('page',$data['page']);
    }

    /**
     * @cc 字段管理
     * @author peterfzh@126.com
     */
    function listfield(){
        $data = $this->base
              ->_M($this->cfg['table'])
              ->_fields($this->cfg['fields'])
              ->_join($this->cfg['join'])
              ->_where($this->cfg['search'])
              ->_order($this->cfg['order'])
              ->_ispage(true)
              ->_getList()
              ->_getData();
        $field = $this->getFieldsFromTable();
        //参数
        $this->assign('config',$this->cfg);
        //参数
        $this->assign('sdata',$this->base->mData);
        //列表抬头
        $this->assign('front',$this->getListTitle($field,$this->cfg['front']));
        //表头控制器
        $this->assign('contrl',$this->cfg['contrl']);
        //行业控制器
        $this->assign('listctrl',$this->cfg['listctrl']);
        $this->assign('value',$this->getFieldValue($field,$this->cfg['front']));
        $this->assign('search',$this->getSearchInnerHtml($field,$this->cfg['front']));
        $this->assign('count',$data['count']);
        $this->assign('lists',$this->getListData($field,$this->cfg['front'],$data['list']));
        $this->assign('list',$data['list']);
        $this->assign('page',$data['page']);
    }

    /**
     * 获取当前数据库表的字段
     * @return [type] [description]
     */
    function getFieldsFromTable()
    {
        $sql = "SHOW COLUMNS FROM ".C("db_prefix").strtolower($this->cfg['table']);
        return $this->base
                ->_M($this->cfg['table'])
                ->_query($sql)
                ->_getData();
    }

    /**
     * 前台页面输出字段
     * @param  [type] $field [description]
     * @param  [type] $front [description]
     * @return [type]        [description]
     */
    function getListTitle($field,$front)
    {
        $arr = array();
        foreach ($field as $k => $v) {
            $field_name = $v['field'];
            if(array_key_exists($field_name, $front))
            {
                $key_info = $front[$field_name];
                if(array_key_exists("listshow", $key_info) && $key_info['listshow'] &&  array_key_exists("name", $key_info) && $key_info['name'])
                {
                    $arr[$field_name] = $key_info['name'];
                }
            }
        }
        return $arr;
    }

    /**
     * 获取列表的列表内容值
     * @param  [type] $field [description]
     * @param  [type] $front [description]
     * @return [type]        [description]
     */
    function getListData($field,$front,$data){
        $counts = count($data);
        $arr    = array();
        for ($r=0;$r<$counts;$r++)
        {
            $temparr = $data[$r];
            foreach ($front as $k => $v) {
              if(array_key_exists($k, $temparr))
              {
                if(array_key_exists("function", $v))
                {
                  $form  = new Form();
                  $func  = array_merge($v['function'],array('value'=>$temparr[$k]));
                  $temparr[$k] = $form->get_Function($func);
                }
              }
            }
            $newrows = $temparr ;
            $arr[] = $newrows;
        }
        return $arr;
    }

    /**
     * 获取前端展示的输出值
     * @param  [type] $field [description]
     * @param  [type] $front [description]
     * @return [type]        [description]
     */
    function getFieldValue($field,$front)
    {
        $arr = array();
        foreach ($field as $k => $v) {
            $field_name = $v['field'];
            if(array_key_exists($field_name, $front))
            {
                $key_info = $front[$field_name];
                if(array_key_exists("listshow", $key_info) && $key_info['listshow'])
                {
                    $arr[] = $field_name;
                }
            }
        }
        return $arr;
    }

    /**
     * 获取前端数据的内容值
     * @param  [type] $field [description]
     * @param  [type] $front [description]
     * @return [type]        [description]
     */
    function getInputParam($field,$front)
    {

        $arr = array();
        foreach ($field as $k => $v) {
            $field_name = $v['field'];
            if(array_key_exists($field_name, $front))
            {
                $key_info = $front[$field_name];
                if(array_key_exists("display", $key_info) && $key_info['display'])
                {
                    $form  = new Form($front[$field_name],$field_name,false);
                    $arr[] = array('lable'=>$key_info['name'],'show'=>$form->getInfo(),'mustbe'=>$key_info['mustbe'],'type'=>$key_info['type'],'name'=>$field_name);
                }
            }
        }
        return $arr;
    }

    /**
     * 获取前端搜索内容
     * @param  [type] $field [description]
     * @param  [type] $front [description]
     * @return [type]        [description]
     */
    function getSearchInnerHtml($field,$front)
    {

        $arr = array();
        foreach ($field as $k => $v) {
            $field_name = $v['field'];
            if(array_key_exists($field_name, $front))
            {
                $key_info = $front[$field_name];
                if(array_key_exists("search", $key_info) && $key_info['search'])
                {
                    $form  = new Form($front[$field_name],$field_name);
                    $arr[] = $form->getInfo();
                }
            }
        }
        return $arr;
    }

    /**
     * 前台输出
     * @return [type] [description]
     */
    function show($temp)
    {
        $ctrl   = $this->ctrl;
        $action = $this->action;
        $this->display($temp);
        die();
    }












}