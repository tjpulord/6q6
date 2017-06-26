<?php
namespace Home\Controller;
use Think\Controller;
class PlanController extends BaseController {

    /**
     * 初始化加载
     */
    function __construct()
    {
        parent::__construct();
    }
    /**
     * 判断是否符合购买条件
     */
    public function canBuyPlan($flag=false)
    {
        $planid  = $this->data['planid'];
        $cardnum = $this->data['cardnum'];
        // 判断参数
        if ($planid && $cardnum) {
            $plancounddb = M('plan_account');
            $plandb      = M('plan');
            $planrulerdb = M('accumulation');
            $res = $plancounddb->where(array('planid'=>$planid, 'cardnumber'=>$cardnum))->select();

            $number = $this->buynumbers($planid, $cardnum);
            if ($res) { //已加入计划，再判断是否能符合购买2份条件
                if (($number - count($res))>0) {
                    if ($flag) {
                        return true;
                    }
                    $this->getJson("ok", 1);
                }else{
                    if ($flag) {
                        return false;
                    }
                    $this->getError('已购买该计划');
                }
            }else{  // 查看是否具备购买资格
                if ($number>0) {
                    if ($flag) {
                        return true;
                    }
                    $this->getJson('ok', $number);
                }else{
                    if ($flag) {
                        return false;
                    }
                    $this->getError('年龄限制不具备购买资格');
                }
            }
        }else{
            if ($flag) {
                return false;
            }
            $this->getError('缺少必要参数，请检查参数是否正确');
        }
    }


    public function canBuyDouble($flag=false)
    {
        $planid  = $this->data['planid'];
        $cardnum = $this->data['cardnum'];

        // 判断参数
        if ($planid && $cardnum) {
            $plancounddb = M('plan_account');
            $res = $plancounddb->where(array('planid'=>$planid, 'cardnumber'=>$cardnum))->select();
            if ($res) {
                if ($flag) {
                    return false;
                }
                $this->getError('已购过买一份，不能再次购买双份');
            }else{
                $number = $this->buynumbers($planid, $cardnum);
                if ($number >= 2) {
                    if ($flag) {
                        return true;
                    }
                    $this->getJson('ok');
                }else{
                    if ($flag) {
                        return false;
                    }
                    $this->getError('年龄限制不具备购买双份资格');
                }
            }
        }else{
            if ($flag) {
                return false;
            }
            $this->getError('缺少必要参数，请检查参数是否正确');
        }
    }


    /**
     * 获取可以加入计划的家人列表信息
     * @return [type] [description]
     */
    public function buylist()
    {
        $planid  = $this->data['planid'];
        $userid  = $this->data['userid'];

        if (!$planid || !$userid) {
            $this->getError('缺少必要参数，请检查参数是否正确');
        }

        $familydb = M('relationship');
        $familylist = $familydb->where("userid='$userid'")->select();

        if (!$familylist) {
            $this->getError('没有家人列表');
        }
        $retdata = array();
        foreach ($familylist as $fk => $fv) {
            $this->data['cardnum'] = $fv['cardnum'];
            $res = $this->canBuyPlan(true);
            if ($res) {
                $ress = $this->canBuyDouble(true);
                if ($ress) {
                    $retdata[$fv['id']] = 2;
                }else{
                    $retdata[$fv['id']] = 1;
                }
            }else{
                $retdata[$fv['id']] = 0;
            }
        }
        $this->getJson('ok', $retdata);
    }



    // 根据用户身份证信息计算购买该计划的份数
    private function buynumbers($planid, $cardnum)
    {
        $rulerdb   = M('accumulation');
        $userage   = getage($cardnum);
        $rulerdata = $rulerdb->where(array('planid'=>$planid))->select();

        foreach ($rulerdata as $key => $value) {
            if ($userage >= $value['min'] && $userage <= $value['max']) {
                return $value['number'];
            }
        }
        return 0;
    }

}