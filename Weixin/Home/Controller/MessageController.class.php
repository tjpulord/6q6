<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 个人消息控制器类
 */
class MessageController extends BaseController {

    private $message_db;
	/**
	 * 构造函数
	 */
	function __construct()
    {
    	parent::__construct();
        $this->message_db = M('message');
    	# code...
    }

    // 我的消息列表展示
    public function index()
    {
        $messages = $this->message_db->where(array('userid'=>$this->_USER['id'], 'isdel'=>0))
        ->order('isread asc, addtime desc')->limit(15)->select();
        if ($messages){
            $this->assign('messages',$messages);
        }
        $this->display();

    }
    // 我的消息列表展示
    public function ajaxAdd()
    {
        $ids ='('.substr($_POST['ids'], 0, -1).')';
        $messages = $this->message_db
            ->where(array('userid'=>$this->_USER['id'], 'isdel'=>0))->where("id not in $ids")
            ->order('isread asc, addtime desc')->limit(10)->select();//$_POST['page'] * 10 +5,
        if ($messages){
            foreach ($messages as $key => $value) {
                $messages[$key]['time'] = date('Y-m-d H:i:s',$value['addtime']);
            }
            echo json_encode($messages);
        } else {
            echo json_encode(array());
        }

        // if ($messages){
        //     $this->assign('messages',$messages);
        // }
        // $this->display();

    }
    // 我的消息列表展示
    public function read()
    {
        $messages = $this->message_db
            ->where(array('userid'=>$this->_USER['id'], 'id'=>$_POST['mid']))
            ->save(array('isread'=>1));
        echo json_encode(1);
        // if ($messages){
        //     $this->assign('messages',$messages);
        // }
        // $this->display();

    }

    // 我的消息详情展示
    public function message()
    {
        // if (isset($_GET['id']) && is_numeric($_GET['id'])){
        //     $res = $this->message_db->where(array('userid'=>$this->_USER['id'], 'id'=>$_GET['id']))->find();
        //     if ($res) {
        //         $this->message_db->where(array('userid'=>$this->_USER['id'], 'id'=>$_GET['id']))->save(array('isread'=>1));
        //         $this->assign('message',$res);
        //         $this->display();
        //     } else {
        //         echo '<script>alert("找不到这条消息");history.go(-1)</script>';
        //         exit;
        //     }
        // } else {
        //     echo '<script>alert("参数错误");history.go(-1)</script>';
        //     exit;
        // }
        $this->display();
    }
    /**
     * ajax删除消息
     *
     */
    public function delmes()
    {
        if ($this->message_db->where(array('id'=>$_POST['id'], 'userid'=>$this->_USER['id']))->save(array('isdel'=>1))){
            echo json_encode(1);
        } else {
            echo json_encode(0);
        }
    }
}