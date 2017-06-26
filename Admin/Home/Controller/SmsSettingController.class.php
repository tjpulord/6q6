<?php
namespace Home\Controller;
use Think\Controller;
use iLazy\Base as mBase;

/**
 * @cc 自动生成SmsSettingController控制类
 */
class SmsSettingController extends BaseController {
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
        $this->tlb   = "system_setting";
        $this->tlist = M("sms_template");
        $this->base  = new mBase($this->tlb);
    }

    /**
     * @cc SmsSetting列表
     * @return [type] [description]
     */
    function listview()
    {
        $where = "1";
        $configdata = $this->iauto->getConfigData();
        if ($configdata["listsearch"]) {
            $where = $configdata["listsearch"];
        }
        $data = $this->base->_where($where)
                     ->_M($this->tlb)
                     ->_ispage(true)
                     ->_getList()
                     ->_getData();
        $this->assign("list",$data["list"]);
        $this->assign("page",$data["page"]);
        $this->display();
    }


    /**
     * @cc SmsSetting编辑、保存
     * @return [type] [description]
     */
    function itemedit()
    {
        $id = array_key_exists("id",$this->postdata) ? $this->postdata["id"] : "1";

        if(isset($this->postdata['info']))
        {
            $itemdata   = $this->postdata["info"];
            $rst = $this->base->_M($this->tlb)->_data($itemdata)->_where("id='$id'")->_save();
            $this->assign('save',$rst);
        }

        if (isset($this->postdata['data'])) {
            $tmpdata = $this->postdata['data'];
            foreach ($tmpdata as $key => $value) {
                $ret = $this->tlist->data($value)->where(array('id'=>$key))->save();
                // echo "sql:".$this->tlist->getLastsql();
            }
            $this->assign('save',$ret);
        }

        if($id)
        {
            $data = $this->base
                  ->_M($this->tlb)
                  ->_find($id)
                  ->_getData();
        }
        // $tempdata = $this->iauto->get($data);
        $templist = $this->tlist->where('1')->select();
        $this->assign('list',$templist);
        $this->assign("data",$data);
        $this->display();
    }

    /**
     * @cc 删除SmsSetting数据
     * @return [type] [description]
     */
    function itemdel()
    {
        $id = array_key_exists("id",$this->data) ? $this->data["id"] : "";
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
}