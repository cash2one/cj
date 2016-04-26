<?php
/**
 * SignScheduleCpController.class.php
 * 考勤排班管理
 * @author: anything
 * @createTime: 2015/02/29 18:36
 * @version: $Id$ 
 * @copyright: 畅移信息
 */
namespace Sign\Controller\Apicp;
use Common\Common\Cache;
use Common\Common\Department;
use Sign\Model\SignScheduleModel;
//use Sign\Common\GetGps;

class SignScheduleCpController extends AbstractController {


    public function test_get(){

//		$Gps= new getGps();
//		$res = $Gps->transform('31.1729959588','121.41026998294');
//		echo $res;

//        echo $this->_strlen("你是我的小苹果");
//        echo "\n";


//        $firstday = rgmdate(NOW_TIME, 'Y-m-01 H:i:s');
//        echo $firstday;die;
//        $t1 = array(
//            'id' =>'1',
//            'type' => '1'
//        );
//        $t2 = array(
//            'type' => '2'
//        );
//        $t[] = $t1;
//        $t[] = $t2;
//        $r = array_column($t, 'id');
//        echo var_export($r,true);
//        die;
        // 执行计划任务
//        $client = &\Com\Rpc::phprpc('http://local.vchangyi.net/UcRpc/Rpc/Crontab');
//        $client->set_async(true);
//        $client->run(array('sign_off'));
        die;
        //$nowday = rgmdate(NOW_TIME, 'w');
//        $nowday = rgmdate(NOW_TIME, '');
//        echo $nowday;die;
//        if(-1){
//            echo intval(date("d"));
//        }
//        $weekarray=array(7,1,2,3,4,5,6);
//        //echo date("w");
//        echo "星期".$weekarray[date("w")];die;
//        // 模拟各种签到时间
//        $t1 = 1457163862;
//        $t1_s = rgmdate($t1, 'Ymd');
//
//
//        // 模拟当天时间 27号
//        $now = 1456991062;
//        $now_s = rgmdate($now, 'Ymd');
//
//        // 模拟第一天签到时间 4号
//       // $t2 = 1456991062;
//       // $t2_s = rgmdate($t2, 'Ymd');
//
//
//
//        // 周期
//        $z = 7;
//
//        //  模拟第一天签到 27 号
////        if($now_s == $t2_s){
////            echo "第一天，默认查询第一个规则";
////            echo "\n";
////        }
//
//        // 28号
//        if($now_s != $t1_s){
//            $a = (($t1 - $now) / 86400 % $z) + 1;
//            echo $a;
//        }
//        echo "\n";
        die;

//        $arr[] =  array(
//            '1' => '1432714553-1432714552',
//            '2' => '1432714552-1432714552',
//            '3' => '1432714552-1432714552'
//        );
//        $arr[] = array(
//            '1' => '1432714553-1432714552',
//            '2' => '1432714552-1432714552',);

        //$t = array(1);

        //$t[array_search(9, $t)];
        //unset($t[array_search(1, $t)]);

//        $t = array(
//            '1' =>  '1432714553-1432714552',
//            '2' => '1432714552-1432714552',
//        );
//
//        $_result[] =array(
//            'type' => 1,
//            'sb' => $t
//        );
//        $_result[] =array(
//            'type' => 2,
//            'sb' => array()
//        );

//        echo var_export($_result,true);
//        die;
        //echo var_export(is_array($arr),true);
        //$r = validateDate('2016-02-28', 'Y-m-d');
        //echo $r;die;
        //echo var_export(end(current($arr)),true);die;
        //echo strstr(current(current($arr)), '-', true);echo "\n";
        //echo substr(strstr(end(current($arr)), '-'),1);die;
        //reset($arr);
//       foreach($arr as $v){
//           echo var_export($v,true);echo "\n";
//       }
        $this->_result =$_result;
    }

    /**
     * 新增排班
     * @return bool
     */
    public function add_post(){
        $params = I('post.');

        if(empty($params['cd_id'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }
        if(!is_array($params['cd_id'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }
        if(empty($params['schedule_array'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

        foreach($params['schedule_array'] as $_v){
            if(empty($_v)){
                E('_ERR_SIGN_SCHEDULE_TIME_FAILD');
                return false;
            }
            foreach($_v as $_cv){
                if(empty($_cv)){
                    E('_ERROR_PARAM_VAL_NULL');
                    return false;
                }
				if(empty($_cv['type'])){
					E('_ERROR_PARAM_VAL_NULL');
					return false;
				}
				if($_cv['type'] == SignScheduleModel::SCHEDULE_WORK_STATUS){
					if(empty($_cv['id'])){
						E('_ERROR_PARAM_VAL_NULL');
						return false;
					}
					if(empty($_cv['time'])){
						E('_ERROR_PARAM_VAL_NULL');
						return false;
					}
					if(!preg_match("/^[0-9]{10}-[0-9]{10}+$/", $_cv['time'])){
						E('_ERROR_PARAM_VAL_NULL');
						return false;
					}
				}
            }
        }

        if(empty($params['schedule_begin_time'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }
        //验证日期合法性
        if(!validateDate($params['schedule_begin_time'], 'Y-m-d')){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

        //周期单位
        if(empty($params['cycle_unit'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

        //验证周期数
        if($params['cycle_unit'] == SignScheduleModel::CYCLE_UNIT_DAY){
            if(is_empty_variable($params['cycle_num'])){
                E('_ERR_SIGN_CYCLE_NUM_NULL_FAILD');
                return false;
            }
            if($params['cycle_num'] >7 || $params['cycle_num'] < 1){
                E('_ERR_SIGN_CYCLE_NUM_RANGE_FAILD');
                return false;
            }
            if(count($params['schedule_array']) < $params['cycle_num']){
                E('_ERR_SIGN_CYCLE_NUM_ERROR');
                return false;
            }
        }

        //如果开启了考勤范围，验证参数
        if($params['range_on'] == SignScheduleModel::SIGN_RANGE_ON){
            //考勤地点
            if(empty($params['address'])){
                E('_ERROR_PARAM_VAL_NULL');
                return false;
            }
            //考勤范围
            if(is_empty_variable($params['address_range'])){
                E('_ERROR_PARAM_VAL_NULL');
                return false;
            }
            //精度
            if(empty($params['longitude'])){
                E('_ERROR_PARAM_VAL_NULL');
                return false;
            }
            //纬度
            if(empty($params['latitude'])){
                E('_ERROR_PARAM_VAL_NULL');
                return false;
            }
        }

        $sign_setting_service = D('Sign/SignSchedule', 'Service');
        $sign_setting_service->add_schedule($params);

    }

    /**
     * 排班列表 分页
     */
    public function list_get(){

        $page = I('get.page', 1, 'intval');
        $limit = I('get.limit', 10, 'intval');

        //部门ID数组
        $cdids = I('get.cdids');

        //排班起止时间
        $start_time = I('get.start_time');
        $end_time = I('get.end_time');

        if(!empty($start_time) && !empty($end_time)){
            $st = rstrtotime($start_time . ' 00:00:00');
            $et = rstrtotime($end_time . ' 00:00:00');
            if($et < $st){
                E('_ERR_SIGN_SCHEDULE_SEARCH_TIME_FAILD');
                return false;
            }
        }

        list($start, $limit, $page) = page_limit($page, $limit);

        // 分页
        $page_option = array($start, $limit);

        // 排序
        $order_by = array('created' => 'DESC');

        $params = array(
            'cdid_array' => $cdids ? explode(',', $cdids) : array(),
            'start_time' => $start_time,
            'end_time' => $end_time
        );
        $sign_setting_service = D('Sign/SignSchedule', 'Service');
        list($list, $total) = $sign_setting_service->list_page($params, $page_option, $order_by);

        $pages = ceil($total / $limit);

        // 返回数据
        $this->_result = array(
            'page' => $page,
            'list' => $list,
            'count' => $total,
            'pages' => $pages,
            'limit' => $limit,
        );


    }

    /**
     * 根据id获取排班详情
     */
    public function get_schedule_get(){
        $id = I('get.id');
        if(empty($id)){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

        $sign_schedule_service = D('Sign/SignSchedule', 'Service');
        $result = $sign_schedule_service->get_schedule_by_id($id);

        $this->_result = array(
            'data' => $result
        );

    }

    /**
     * 修改排班
     * @return bool
     */
    public function modify_post(){
        $params = I('post.');

        if(empty($params['id'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

        $sign_schedule_service = D('Sign/SignSchedule', 'Service');

        //修改类型 1-全公司；2-其他部门排班
        if(empty($params['type'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

        //验证是否可编辑
        if(!$sign_schedule_service->check_disable($params['id'])){
            E('_ERR_SIGN_SCHEDULE_NOTEDIT_FAILD');
            return false;
        }

        if(empty($params['cd_id'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

        if(empty($params['schedule_array'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }
        //验证排班详情
        foreach($params['schedule_array'] as $_v){
            if(empty($_v)){
                E('_ERR_SIGN_SCHEDULE_TIME_FAILD');
                return false;
            }
			foreach($_v as $_cv){
				if(empty($_cv)){
					E('_ERROR_PARAM_VAL_NULL');
					return false;
				}
				if(empty($_cv['type'])){
					E('_ERROR_PARAM_VAL_NULL');
					return false;
				}
				if($_cv['type'] == SignScheduleModel::SCHEDULE_WORK_STATUS){
					if(empty($_cv['id'])){
						E('_ERROR_PARAM_VAL_NULL');
						return false;
					}
					if(empty($_cv['time'])){
						E('_ERROR_PARAM_VAL_NULL');
						return false;
					}
					if(!preg_match("/^[0-9]{10}-[0-9]{10}+$/", $_cv['time'])){
						E('_ERROR_PARAM_VAL_NULL');
						return false;
					}

				}
			}
        }

        if(empty($params['schedule_begin_time'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }
        //验证日期合法性
        if(!validateDate($params['schedule_begin_time'], 'Y-m-d')){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

        //周期单位
        if(empty($params['cycle_unit'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

        //如果周期单位是天，验证周期数
        if($params['cycle_unit'] == SignScheduleModel::CYCLE_UNIT_DAY){
            if(is_empty_variable($params['cycle_num'])){
                E('_ERR_SIGN_CYCLE_NUM_NULL_FAILD');
                return false;
            }
            if($params['cycle_num'] >7 || $params['cycle_num'] < 1){
                E('_ERR_SIGN_CYCLE_NUM_RANGE_FAILD');
                return false;
            }
            if(count($params['schedule_array']) < $params['cycle_num']){
                E('_ERR_SIGN_CYCLE_NUM_ERROR');
                return false;
            }
        }

        //如果开启了考勤范围，验证参数
        if($params['range_on'] == SignScheduleModel::SIGN_RANGE_ON){
            //考勤地点
            if(empty($params['address'])){
                E('_ERROR_PARAM_VAL_NULL');
                return false;
            }
            //考勤范围
            if(is_empty_variable($params['address_range'])){
                E('_ERROR_PARAM_VAL_NULL');
                return false;
            }
            //精度
            if(empty($params['longitude'])){
                E('_ERROR_PARAM_VAL_NULL');
                return false;
            }
            //纬度
            if(empty($params['latitude'])){
                E('_ERROR_PARAM_VAL_NULL');
                return false;
            }
        }

        $sign_schedule_service->modify_schedule($params);

    }


    /**
     * 删除排班
     * @return bool
     */
    public function delete_post(){
        $params = I('post.');
        if(empty($params['id'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

        $sign_schedule_service = D('Sign/SignSchedule', 'Service');
        //验证是否可删除
        if(!$sign_schedule_service->check_disable($params['id'])){
            E('_ERR_SIGN_SCHEDULE_DELETE_FAILD');
            return false;
        }


        $sign_schedule_service->delete_schedule($params['id']);
    }

    /**
     * 启用/禁用排班
     */
    public function enabled_post(){
        $params = I('post.');
        if(empty($params['id'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

        if(empty($params['enabled'])){
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

        $sign_schedule_service = D('Sign/SignSchedule', 'Service');
        $sign_schedule_service->enabled_schedule($params);

    }

}