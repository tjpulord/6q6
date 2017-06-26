<?php
namespace Home\Controller;
use Think\Controller;
use iLazy\Base as mBase;

/**
 * @cc 自动生成SystemSettingController控制类
 */
class SystemSettingController extends BaseController {
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
        $this->base  = new mBase($this->tlb);
        $this->iauto = new IAutomaticController("20");
    }

    /**
     * @cc SystemSetting列表
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
                $where .= " and $key='$value'";
            }
        }
        if ($this->postdata["search"]) {
            foreach ($this->postdata["search"] as $key => $value) {
                if ($value) {
                    $where .= " and $key='$value'";
                }
            }
        }
        if ($this->postdata["search1"]) {
            foreach ($this->postdata["search1"] as $key => $value) {
                if ($value) {
                    $where .= " and $key like '%$value%'";
                }
            }
        }
        if ($this->postdata["search2"]) {
            foreach ($this->postdata["search2"] as $key => $value) {
                if ($value) {
                    $where .= " and $key > '$value'";
                }
            }
        }
        if ($this->postdata["search3"]) {
            foreach ($this->postdata["search3"] as $key => $value) {
                if ($value) {
                    $where .= " and $key < '$value'";
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
     * @cc SystemSetting编辑、保存
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
                $rst = $this->base->_M($this->tlb)->_data($itemdata)->_where("id='$id'")->_save();
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
                  ->_find($id)
                  ->_getData();
        }
        $tempdata = $this->iauto->get($data);

        $this->assign("data",$tempdata);
        $this->display();
    }

    /**
     * @cc 删除SystemSetting数据
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
}