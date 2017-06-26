<?php
namespace iLazy;


class iLazy
{
    public $db_mdel;            //执行的模型
    public $tablename;          //操纵的数据表
    public $pagesize = 20;      //分页大小
    public $params = array();
    /**
     * 数据化
     */
    function __construct($tablename='log')
    {
        $this->tablename = $tablename;
        $this->db        = M($this->tablename);
        return $this;
    }

    /**
     * 魔术写法
     * @param [type] $key   [description]
     * @param [type] $value [description]
     */
    function __set($key,$value)
    {
        $this->$key = $value;
        return $this;
    }

    /**
     * 获取指定的参数
     * @param  [type] $key [description]
     * @return [type]      [description]
     */
    function __get($key){
        return $this->$key;
    }


    /**
     * 设置内部属性
     * @param [type] $key   [description]
     * @param [type] $value [description]
     */
    function _set($key,$value)
    {
        $this->params[$key] = $value;
        return $this;
    }

    /**
     * 变更模型
     * @param  [type] $key 模型名称
     * @return [type]      [description]
     */
    function _M($key)
    {
        $this->tablename = $key;
        $this->db  = M($key);
        return $this;
    }

    /**
     * 获取动态设置的值
     * @param  [type] $key [description]
     * @return [type]      [description]
     */
    function getConfig($key,$default=array())
    {
        if(array_key_exists($key,$this->params))
        {
            return $this->params[$key];
        }
        else{
            return $default;
        }
    }

    /**
     * @cc 自定义方法
     * @author peterfzh@126.com
     */
    function initConfig(){
        $this->where  = isset($this->where) ? $this->where : $this->getConfig('where');                 //查询条件
        $this->ispage = isset($this->ispage) ? $this->ispage : $this->getConfig('ispage',false);        //是否为分页模式
        $this->join   = isset($this->join) ? $this->join : $this->getConfig('join');                    //多表联合
        $this->order  = isset($this->order) ? $this->order : $this->getConfig('order');                 //排序
        $this->fields = isset($this->fields) ? $this->fields : $this->getConfig('fields',array('*'));   //查询字段
        $this->data   = isset($this->data) ? $this->data : $this->getConfig('data');                    //保存数据
        $this->pri    = isset($this->pri) ? $this->pri : $this->getConfig('pri');                       //表主键
        return $this;
    }

    /**
     * 数据绑定
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    function _data($data){
        $this->data = $data;
        return $this;
    }

    /**
     * @cc 启用分页
     * @author peterfzh@126.com
     */
    function _ispage($data){
        $this->ispage = $data;
        return $this;
    }

    /**
     * @cc 分页大小
     * @author peterfzh@126.com
     */
    function _pagesize($data){
        $this->pagesize = $data;
        return $this;
    }

    /**
     * 数据排序
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    function _order($data){
        $this->order = $data;
        return $this;
    }

    /**
     * 查询字段
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    function _fields($data){
        $this->fields = $data;
        return $this;
    }

    /**
     * 数据组件
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    function _pri($data){
        $this->pri = $data;
        return $this;
    }

    /**
     * 添加多表联合
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    function _where($data){
        $this->where = $data;
        return $this;
    }

    /**
     * 添加多表联合
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    function _join($data){
        $this->join = $data;
        return $this;
    }

    /**
     * 执行sql语句
     * @param  [type] $sql [description]
     * @return [type]      [description]
     */
    function _query($sql){
        $this->result = $this->db->query($sql);
        return $this;
    }

    /**
     * 获取最后的执行语句
     * @return [type] [description]
     */
    function getsql(){
        $this->result = $this->db->getlastsql();
        return $this->result;
    }

    /**
     * @cc 获取相关数据
     * @author peterfzh@126.com
     */
    function _getData(){
        return $this->result;
    }

    /**
     * @cc 获取列表信息
     * @author peterfzh@126.com
     */
    function _getList(){
        $this->initConfig();
        if($this->ispage)   //分页模式
        {
            $this->count = $this->db->join($this->join)->where($this->where)->count();
            $Page        = new \Think\Page($this->count,$this->pagesize);
            $show        = $Page->show();
            $list        = $this->db
                         ->field($this->fields)
                         ->where($this->where)
                         ->join($this->join)
                         ->limit($Page->firstRow.','.$Page->listRows)
                         ->order($this->order)
                         ->select();
            $this->result = array('count'=>$this->count,'list'=>$list,'page'=>$show);
        }
        else                //返回模式
        {
            $this->result = $this->db->field($this->fields)->where($this->where)->join($this->join)->order($this->order)->select();
        }
        return $this;
    }

    /**
     * @cc 获取某条信息
     * @author peterfzh@126.com
     */
    function _find($id){
        $this->initConfig();
        $this->result = $this->db->field($this->fields)->where($this->where)->join($this->join)->order($this->order)->find($id);
        return $this;
    }

    /**
     * @cc 添加数据信息
     * @author peterfzh@126.com
     */
    function _add(){
        $this->initConfig();
        $this->result = $this->db->data($this->data)->add();
        return $this;
    }

    /**
     * @cc 删除数据
     * @author peterfzh@126.com
     */
    function _del($id){
        //dosomething
        $this->initConfig();
        $this->result = $this->db->where($this->data)->delete($id);
        return $this;
    }

    /**
     * @cc 排序方法
     * @author peterfzh@126.com
     */
    function _sorder($order='sorder'){
        $rtn = 0;
        if(is_array($this->data) && !empty($this->data))
        {
            $rtn = 0;
            foreach ($this->data as $k => $v) {
                $rst = $this->db->where('id=%d',$k)->data(array($order=>$v))->save();
                if($rst){
                    $rtn++;
                }
            }
        }
        $this->result = $rtn;
        return $this;
    }

    /**
     * @cc 多条数据保存
     * @author peterfzh@126.com
     */
    function _msave(){
        $rtn = 0;
        if(is_array($this->data) && !empty($this->data))
        {
            $rtn = 0;
            foreach ($this->data as $k => $v) {
                $rst = $this->db->where('id=%d',$k)->data($v)->save();
                if($rst){
                    $rtn++;
                }
            }
        }
        $this->result = $rtn;
        return $this;
    }



    /**
     * 保存信息
     * @param  [type] $d [description]
     * @param  [type] $k [description]
     * @return [type]    [description]
     */
    function _save(){
        $this->initConfig();
        if(empty($this->data) || (empty($this->where) && empty($this->pri))){
            $this->result = false;
        }
        else
        {
            $where = empty($this->where) ? $this->pri."=".$this->data[$this->pri]."" : $this->where;
            $this->result = $this->db->where($where)->data($this->data)->save();
        }
        return $this;
    }



}