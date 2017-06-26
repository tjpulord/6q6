<?php
namespace Home\Controller;
use Think\Controller;


/**
 *  @cc 系统设置管理
 */
class SystemController extends ICommonController {

	function __construct($ltb)
	{
		parent::__construct();
		$this->tlb = $ltb;
		$this->imodel = M('admin_user');
        $this->ilog   = M('log');
	}
	/**
	 *  @cc 编辑自己的密码
	 */
    public function changepwd()
    {
    	if($_POST['password'] !=null && cookie('admin_id') !=null)
    	{
            $userinfo = array('password' => md5($_POST['password']));
    		$m_data = $this->imodel->data($userinfo)
                            ->where('id=%d', cookie('admin_id'))
                            ->save();
    		if($m_data > 0)
    		{
    			echo $this->setjson(2000,'success');
    		}else
    		{
    			echo $this->setjson(4000,'faild');
    		}
    	}else
    	{
    		$this->display();
    	}
    }

    /**
     *  @cc 菜单列表
     * @return [type] [description]
     */
    public function menulist()
    {
        $ilazy = new \iLazy\Menu();
        if(array_key_exists("doSubmit",$this->data) && $this->data['doSubmit'])
        {
            $ilazy->mulisave($this->data['morder']);
        }
        $list = $ilazy->show();
        $this->assign('list',$list);
        $this->display();
    }

    /**
     *  @cc 编辑地区信息
     * @return [type] [description]
     */
     function arealist()
     {
        $ilazy = new \iLazy\Area();
        if(array_key_exists("doSubmit",$this->data) && $this->data['doSubmit'])
        {
            $ilazy->mulisave($this->data['morder']);
        }
        $list = $ilazy->getList($this->data['pid']);
        $this->assign('list',$list);
        $this->display();
    }

    /**
     *  @cc 编辑地区信息
     * @return [type] [description]
     */
     function areaedit()
     {
        $mitr = new \iLazy\Area();
        $list = $mitr->show();
        $this->assign('list',$list);
        $data['parent_id'] = $this->data['parent_id'];
        if(array_key_exists('id', $this->data) && $this->data['id'])
        {
            $data = $mitr->find($this->data['id']);
        }
        $this->assign('data',$data);
        if(array_key_exists('doSubmit', $this->data) && $this->data['doSubmit'])
        {
            $rst = $mitr->save($this->data,'area_id');
            $this->assign('save',$rst);
        }
        $this->display();
    }

    /**
     *  @cc 删除菜单
     * @return [type] [description]
     */
    public function areadel()
    {
        $ilazy = new \iLazy\Area();
        $rst  = false;
        if(array_key_exists('id', $this->data) && $this->data['id'])
        {
            $rst = $ilazy->del($this->data['id']);
        }
        if($rst)
        {
            $this->getJson("删除成功");
        }
        else
        {
            $this->getJson("删除失败",'error',3000);
        }
    }

    /**
     *  @cc 修改表单
     * @return [type] [description]
     */
    public function menuedit()
    {
        $ilazy = new \iLazy\Menu();
        $list = $ilazy->show();
        $this->assign('list',$list);
        $data['parentid'] = $this->data['parentid'];
        if(array_key_exists('id', $this->data) && $this->data['id'])
        {
            $data = $ilazy->find($this->data['id']);
        }
        $this->assign('data',$data);
        if(array_key_exists('doSubmit', $this->postdata) && $this->postdata['doSubmit'])
        {
            $rst = $ilazy->save($this->postdata,'id');
            $this->assign('save',$rst);
        }

        $this->display();
    }


    /**
     *  @cc 删除菜单
     * @return [type] [description]
     */
    public function menudel()
    {
        $ilazy = new \iLazy\Menu();
        $rst  = false;
        if(array_key_exists('id', $this->data) && $this->data['id'])
        {
            $rst = $ilazy->del($this->data['id']);
        }
        if($rst)
        {
            $this->getJson("删除成功");
        }
        else
        {
            $this->getJson("删除失败",'error',3000);
        }
    }

    /**
     *  @cc 系统日志列表
     * @return [type] [description]
     */
    function listview()
    {
    	// $this->imodel->_listview(null,null,$where,$count=15,$order=" log_id desc ");
    }

    /**
     *  @cc 监控系统
     * @return [type] [description]
     */
    function monitor_list()
    {
        $ilazy = new \iLazy\Monitor();
        $list = $ilazy->show();
        $this->assign('list',$list);
        $this->display();

    }
}