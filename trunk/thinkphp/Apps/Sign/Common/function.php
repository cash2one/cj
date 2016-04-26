<?php
/**
 * Function.php
 * 说明
 * @author: anything
 * @createTime: 2016/3/1 13:51
 * @version: $Id$ 
 * @copyright: 畅移信息
 */

/**
 * 验证日期字符串是否合法
 * @param $date
 * @param string $format
 * @return bool
 */
function validateDate($date, $format = 'Y-m-d H:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

/**
 * 判断当天是排班的第几天
 * @param $now_timestamp 当前时间
 * @param $start_timestamp 排班初始时间
 * @return int
 */
function find_schedule_day($now_timestamp, $start_timestamp, $day){


    //初始开始日期
    $start_date = rgmdate($start_timestamp, 'Ymd');

    //当前签到日期
    $now_date = rgmdate($now_timestamp, 'Ymd');

    $result = -1;

    if($start_date != $now_date){
        $i = (($now_timestamp - $start_timestamp) / 86400 % $day) + 1;
        $result = $i;
    }elseif($start_date == $now_date){
        $result = 1;
    }

    return $result;
}

/**
 * 非空验证
 * @param $variable
 * @return bool
 */
function is_empty_variable($variable){
//    foreach($_REQUEST as $key => $value)
//    {
//        if(is_empty_variable($value))
//        {
//            throw new \Exception("{$key} 为空");
//        }
//    }
    return (!isset($variable) || trim($variable)==='');
}

/**
 * 获取两个日期相差的天数
 * @param $str_s 开始日期 Y-m-d
 * @param $str_e 结束日期 Y-m-d
 */
function differ_days($str_s, $str_e){
    $d1 = rstrtotime($str_s);
    $d2 = rstrtotime($str_e);
    $Days = round(($d2 - $d1) / 3600 / 24);
    return $Days;
}

/**
 * 获取两个日期相差的小时
 * @param $str_s 开始日期
 * @param $str_e 结束日期
 */
function differ_hours($str_s, $str_e){
    $hours = floor(($str_e - $str_s) % 86400 / 3600);
    return $hours;
}


/**
 * 计算中英文字符串长度
 * @param $str
 * @return int
 */
function rstrlen($str)
{
    preg_match_all("/./us", $str, $matches);
    return count(current($matches));
}