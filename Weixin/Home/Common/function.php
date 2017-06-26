<?php

// 获取观察期
function getObservation($planid, $cardnum, $addintime)
{
    // 计算周岁
    // $year  = substr($cardnum, 6, 4);
    // $month = substr($cardnum, 10, 2);
    // $day   = substr($cardnum, 12, 2);

    // $age = intval($agestr);
    // $nz = strtotime("$year-$month-$day");
    // $diffday = (time()-$nz)/3600/24;
    // $diffyear = floor($diffday/365.2425);

    $diffyear = getage($cardnum, $addintime);
    // 获取计划规则中观察期
    $planrulerdb = M('accumulation');
    $rulerdata = $planrulerdb->where('planid=%d', $planid)->select();
    $days = diffday($addintime);
    foreach ($rulerdata as $key => $value) {
        if($diffyear>=$value['min'] && $diffyear<=$value['max']){
            if ($days > $value['observe']) {
                 return "保障期";
             }else{
                return "观察期".$value['observe']."天";
             }
        }
    }

    return "无法获取";
}


// 获取理赔金额
function getclaim($planid, $cardnum, $addintime)
{
    $diffyear = getage($cardnum);
    // 获取计划规则中观察期
    $planrulerdb = M('accumulation');
    $rulerdata = $planrulerdb->where('planid=%d', $planid)->select();
    $days = diffday($addintime);
    foreach ($rulerdata as $key => $value) {
        if($diffyear>=$value['min'] && $diffyear<=$value['max']){
            $ary = array($value['base'], $value['day'], $value['base']+$value['day']*$days);
            // return $value['base']+$value['day']*$days;
            return $ary;
        }
    }

    return false;
}

?>