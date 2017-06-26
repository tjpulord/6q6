<?php 
namespace Home\Controller;
use Think\Controller;
class IModelController extends Controller{
	public $tableName;
	public $_show = true;
	/**
	 * 初始化判断
	 */
    function __construct($tbl)
    {
     	parent::__construct();
        $this->tableName = $tbl;
    }

	/**
     * 魔术写法
     */
    function __set($key ,$value)
    {
    	$this->$key = $value;
        return $this;
    }

    /**
     * 数据填充
     * @return [type] [description]
     */
   public function datafill($temp)
    {
        if($this->tableName==null)
        {
            die(C('s_params'));
        }
    	$model_db = M($this->tableName);
        if(isset($this->join))
        {
            $this->join = $this->join;
        }
        else
        {
            $this->join = array();
        }
        if(isset($this->search))
        {
            $this->search = $this->search;
        }
        else
        {
            $this->search = array();
        }
        if(isset($this->field))
        {
            $this->field = $this->field;
        }
        else
        {
            $this->field = array();
        }
        if(isset($this->pagesize))
        {
            $this->pagesize = $this->pagesize;
        }
        else
        {
            $this->pagesize = 10;
        }
        if(isset($this->map))
        {
            $this->map = $this->map;
        }
        else
        {
            $this->map = array();
        }
        
        $count = $model_db->join($this->join)->where($this->search)->count();    // 查询满足要求的总记录数
        //计算某一字段总和
        if(isset($this->sum_key))
        {
        	$sumScore = $model_db->join($this->join)->where($this->search)->sum($this->sum_key);
        	$this->assign('sumScore',$sumScore);        // 赋值总和输出
        }
        
        $Page  = new  \Think\Page($count,$this->pagesize);

        foreach($this->params as $key=>$val)
        {
        	$Page->parameter[$key]    = urlencode($val);
        }
        
        foreach($this->map as $key=>$val)
        {
        	if($val !=null)
        	{
        		$Page->parameter   .= "$key=".urlencode($val).'&';
        	}
        }

        //$Page->parameter = $parameter.$Page->parameter;
       // dump($Page->parameter);
        $show   = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list  = $model_db
                ->field($this->field)
                ->where($this->search)
                ->join($this->join)
                ->limit($Page->firstRow.','.$Page->listRows)
                ->order($this->order)
                ->group($this->group)
                ->select();

       // echo $model_db->getlastsql();
       
        $this->assign('count',$count);      // 赋值数据集
        $this->assign('list',$list);        // 赋值数据集
        $this->assign('page',$show);        // 赋值分页输出
        $this->assign('params',$this->params);  // 赋值分页输出参数
        if($this->_show)
        {
        	$this->display($temp);
        }else
        {
        	return $this;
        }
    }
 }?>
