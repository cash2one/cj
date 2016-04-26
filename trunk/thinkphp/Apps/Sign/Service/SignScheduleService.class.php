<?php
/**
 * SignScheduleService.class.php
 * $author$
 */

namespace Sign\Service;

use Common\Service\AbstractSettingService;
use Common\Common\Cache;
use Common\Common\Department;
use Sign\Model\SignBatchModel;
use Sign\Model\SignScheduleModel;
use Sign\Model\SignRecordModel;
use Sign\Model\SignSettingModel;
use Think\Log;

class SignScheduleService extends AbstractSettingService
{

    //全公司
    const ALL_COMPANY = 0;
    //休息
    const REST_STATUS = 0;

	//前天
	const BEFORE_YESTERDAY = 2;
	//昨天
	const YESTERDAY = 1;
	//今天
	const TODAY = 0;

	//顶级根部门
	const TOP_DEPARTMENT = 1;

    // 构造方法
    public function __construct()
    {

        parent::__construct();
        $this->_d = D('Sign/SignSchedule');
    }

    /**
     * 读取所有并检查 pluginid, agentid 参数是否正确
     * @see \Common\Service\AbstractSettingService::list_kv()
     */
    public function list_kv()
    {

        // 取表中的数据
        $sets = parent::list_kv();

        // 获取插件列表
        $cache = &Cache::instance();
        $plugins = $cache->get('Common.plugin');

        // 获取 pluginid, agentid
        $pluginid = empty($sets['pluginid']) ? 0 : (int)$sets['pluginid'];
        $agentid = empty($sets['agentid']) ? 0 : (int)$sets['agentid'];
        // 如果插件信息不存在, 则从插件重新获取 pluginid 和 agentid
        if (empty($plugins[$pluginid]) || $agentid != $plugins[$pluginid]['cp_agentid'] || 'sign' != rstrtolower($plugins[$pluginid]['cp_identifier'])) {

            // 遍历所有插件
            foreach ($plugins as $_p) {
                // 如果不是留言本, 则取下一个
                if ('sign' != rstrtolower($_p['cp_identifier'])) {
                    continue;
                }

                // 取 pluginid, agentid 信息
                $pluginid = $_p['cp_pluginid'];
                $agentid = (int)$_p['cp_agentid'];

                // 更新表数据
                $this->update_kv(array(
                    'pluginid' => $pluginid,
                    'agentid' => $agentid
                ));
            }
        }

        // 更新相关值
        $sets['pluginid'] = $pluginid;
        $sets['agentid'] = $agentid;

        return $sets;
    }


    public function add_schedule($params)
    {

        //排班数组
        $schedule_array = $params['schedule_array'];
        //节假日排除日期
        $remove_day_arr = $params['remove_day'];
        //节假日增加日期
        $add_work_day_arr = $params['add_work_day'];

        //验证全公司是否已启用
        //$this->__check_all_company_enabled();

        //验证部门是否已存在排班
        $this->__check_cdid_scheduled($params['cd_id']);

        //验证排班 班次之间时间不得冲突，且总时间跨度不得超过二十四小时
        $sbids = $this->__validate_schedule($schedule_array);

		$this->__validate_schedule_sign_range($schedule_array);

        //验证假期增加工作日期
        $this->__validate_add_work_day($add_work_day_arr);

        //验证假期排除日期
        $this->__validate_remove_work_day($remove_day_arr);

        //验证假期排除日期与增加日期是否冲突
        if (!empty($remove_day_arr) && !empty($add_work_day_arr)) {
            $this->__validate_work_day($remove_day_arr, $add_work_day_arr);
        }

        //封装插入排班表数据
        $insert_data = array();
        //默认禁用
        $insert_data['enabled'] = SignScheduleModel::SCHEDULE_DISABLE;
        //每天排班详情
        $insert_data['schedule_everyday_detail'] = serialize($schedule_array);
        //所有班次Id,逗号相隔
        $insert_data['sbid'] = $sbids;
        //排班开始时间
        $insert_data['schedule_begin_time'] = rstrtotime($params['schedule_begin_time'] . '00:00:00');
        //排班结束时间
        $insert_data['schedule_end_time'] = $params['schedule_end_time'] ? rstrtotime($params['schedule_end_time'] . '00:00:00') : '';
        //周期单位
        $insert_data['cycle_unit'] = $params['cycle_unit'];
        if ($params['cycle_unit'] == SignScheduleModel::CYCLE_UNIT_DAY) {
            //周期数
            $insert_data['cycle_num'] = $params['cycle_num'];
        }
        //排除节假日
        $insert_data['remove_day'] = $remove_day_arr ? serialize($remove_day_arr) : '';
        //增加上班日期
        $insert_data['add_work_day'] = $add_work_day_arr ? serialize($add_work_day_arr) : '';
        //开启考勤范围
        $insert_data['range_on'] = $params['range_on'];
        if ($params['range_on'] == SignScheduleModel::SIGN_RANGE_ON) {
            //地点
            $insert_data['address'] = $params['address'];
            //考勤范围
            $insert_data['address_range'] = $params['address_range'];
            //精度
            $insert_data['longitude'] = $params['longitude'];
            //纬度
            $insert_data['latitude'] = $params['latitude'];
        }

        try {
            //开启事务
            $this->start_trans();

            foreach ($params['cd_id'] as $_v) {
                $insert_data['cd_id'] = $_v;
                $insert_sign_schedule_data[] = $insert_data;
            }
            //插入排班表
            $this->__batch_insert_schedule($insert_sign_schedule_data);

            //提交事务
            $this->commit();

        } catch (\Exception $e) {
            Log::record('新增排班异常：');
            Log::record($e->getMessage());
            $this->rollback();
            E('_ERR_SIGN_SYSTEM_BUSY');
            return false;
        }

        return true;

    }


    /**
     * 批量插入排班表
     * @param $array
     */
    private function __batch_insert_schedule($array)
    {

        //每次插入100条
        $speed = 100;
        $batch_count = 0;
        $size = count($array);
        do {
            // 开始索引
            $from = $batch_count * $speed;
            $batch_count++;

            $tmp = array_slice($array, $from, $speed, true);
            $tmp = array_values($tmp);

            if (!$res_id = $this->_d->insert_all($tmp)) {
                E('_ERR_SIGN_INSERT_SCHEDULE_FAILD');
                return false;
            }
            //unset($tmp);
        } while ($size > ($speed * $batch_count));
    }

    /**
     * 验证排班 班次之间时间不得冲突，且总时间跨度不得超过二十四小时
     * @param array $params
     * @return bool
     */
    private function __validate_schedule(&$params = array())
    {

		$batch_model = D('Sign/SignBatch');
		$ymd = rgmdate(NOW_TIME,'Y-m-d');
        //返回所有班次id
        $sbid_array = array();
        foreach($params as $key => &$_val){
            $frist_timestamp = 0;
			$last_timestamp = 0;
			$sbids = array_column($_val, 'id');
			//Log::record('$sbids..........'.var_export($sbids,true));
			if(empty($sbids)){
				continue;
			}
			//按开始时间排序
			$batchs = $batch_model->list_batch_orderby_work_begin($sbids);
			//Log::record('对班次排序....' .var_export($batchs,true));
			$new_batchs = array();
            foreach($batchs as $_ckey => $_cval){
				$new_batchs[] = array(
					'id' => $_cval['sbid'],
					'type' => SignScheduleModel::SCHEDULE_WORK_STATUS,
					'name' => $_cval['name'],
					'time' => $_cval['work_begin'] . '-' . $_cval['work_end'],
				);


				$start_hi = rgmdate($_cval['work_begin'],'H:i');
				$end_hi = rgmdate($_cval['work_end'],'H:i');

				$validate_start_timestamp = $_cval['work_begin'];
				$validate_end_timestamp = $_cval['work_end'];

				$validate_start_timestamp = rstrtotime($ymd . ' ' . $start_hi);

				$validate_end_timestamp = rstrtotime($ymd . ' ' . $end_hi);

				//次日
				if(differ_days(rgmdate($_cval['work_begin'],'Y-m-d'), rgmdate($_cval['work_end'],'Y-m-d')) > 0){
					$validate_end_timestamp = $validate_end_timestamp + 86400;
				}

				//Log::record('$validate_start_timestamp....'.rgmdate($validate_start_timestamp,'Y-m-d H:i:s'));
				//$last_timestamp = $_cval['work_end'];
				//Log::record('$last_timestamp....'.rgmdate($last_timestamp,'Y-m-d H:i:s'));
				//第一个班次的开始时间
				if (empty($frist_timestamp)) {
					$frist_timestamp = $validate_start_timestamp;
				}


				$last_timestamp = $validate_end_timestamp;

                foreach ($batchs as $_ck => $_cv) {
                    if ($_ckey == $_ck) {
                        continue;
                    }

					$_start_hi= rgmdate($_cv['work_begin'],'H:i');
					$_end_hi= rgmdate($_cv['work_end'],'H:i');

					$_ft = rstrtotime($ymd . ' ' . $_start_hi);
					$_et = rstrtotime($ymd . ' ' . $_end_hi);

					if(differ_days(rgmdate($_cv['work_begin'],'Y-m-d'), rgmdate($_cv['work_end'],'Y-m-d')) > 0){
						$_et = $_et + 86400;
					}

                    if ($validate_start_timestamp >= $_ft && $validate_start_timestamp <= $_et) {
                        E('_ERR_SIGN_SCHEDULE_TIME_INCLUDE_FAILD');
                        return false;
                    }
                    if ($validate_end_timestamp >= $_ft && $validate_end_timestamp <= $_et) {
                        E('_ERR_SIGN_SCHEDULE_TIME_INCLUDE_FAILD');
                        return false;
                    }

                }

                $sbid_array[] = (int)$_cval['sbid'];
            }

			//Log::record('$new_batchs.......'.var_export($new_batchs,true));
			$_val = $new_batchs;
			//Log::record('$frist_timestamp.......'.rgmdate($frist_timestamp,'Y-m-d H:i:s'));
			//Log::record('$last_timestamp.......'.rgmdate($last_timestamp,'Y-m-d H:i:s'));
            //验证是否超过24小时
            if (($last_timestamp - $frist_timestamp) > 86400) {
                E('_ERR_SIGN_SCHEDULE_TIME_MAX_FAILD');
                return false;
            }

        }


        return ',' . implode(',', $sbid_array) . ',';
    }

	/**
	 * 验证天与天之间的排班最晚、最早打卡时间不能冲突
	 * @param $params
	 * @return bool
	 */
	private function __validate_schedule_sign_range($params){

		$sign_batch_model = D('Sign/SignBatch');
		$now_ymd = rgmdate(NOW_TIME, 'Y-m-d');
		//Log::record('$params'.var_export($params,true));
		for($i=0; $i<count($params); $i++){
			$batch_ids = array_column($params[$i],'id');
			//Log::record('$batch_ids'.var_export($batch_ids,true));
			//休息
			if(empty($batch_ids)){
				continue;
			}

			//最后一个班次的班次id
			$last_sbid = $batch_ids[count($batch_ids)-1];

			//班次信息
			$batch_info = $sign_batch_model->get($last_sbid);

			//下班时间
			$work_end = $batch_info[0]['work_end'];

			//允许下班打卡时间
			$sign_end_range = $work_end;
			if($batch_info[0]['type'] == SignBatchModel::BTACH_COMMON_TYPE){
				$sign_end_range = $sign_end_range + $batch_info[0]['sign_end_range'];
			}

//			Log::record('下班最晚结束打卡时间。。。。'.rgmdate($sign_end_range, 'Y-m-d H:i:s'));
//			Log::record('work_begin---------'.rgmdate($batch_info[0]['work_begin'], 'Y-m-d'));
//			Log::record('$sign_end_range---------'.rgmdate($sign_end_range, 'Y-m-d'));
			//下班结束打卡时间是次日
			if(differ_days(rgmdate($batch_info[0]['work_begin'], 'Y-m-d'), rgmdate($sign_end_range,'Y-m-d')) > 0){


				if($i+1 == count($params)){
					$tomorrow_batch_ids = array_column($params[0],'id');
				}elseif($i+1 < count($params)){
					//明天的排班班次id
					$tomorrow_batch_ids = array_column($params[$i+1],'id');
				}

				//Log::record('下班结束打卡时间是次日');
				$sign_end_range_hi = rgmdate($sign_end_range,'H:i');
				$sign_end_range = rstrtotime($now_ymd . ' ' . $sign_end_range_hi);
				//Log::record('下班最晚结束打卡时间转换后------'.rgmdate($sign_end_range, 'Y-m-d H:i:s'));



				//休息
				if(empty($tomorrow_batch_ids)){
					continue;
				}

				//明天的第一个班次id
				$first_sbid = $tomorrow_batch_ids[0];

				//班次信息
				$batch_info = $sign_batch_model->get($first_sbid);
				//上班时间
				$work_begin = $batch_info[0]['work_begin'];
				$work_begin_hi = rgmdate($work_begin,'H:i');
				$work_begin = rstrtotime($now_ymd . ' ' . $work_begin_hi);

				//允许最早上班打卡时间
				$sign_begin_range = $work_begin;
				if($batch_info[0]['type'] == SignBatchModel::BTACH_COMMON_TYPE){
					$sign_begin_range = $sign_begin_range - $batch_info[0]['sign_start_range'];
				}

//				$sign_begin_range_hi = rgmdate($sign_begin_range,'H:i');
//				$sign_begin_range = rstrtotime($now_ymd . ' ' . $sign_begin_range_hi);

				//Log::record('明天第一个班次上班时间'.rgmdate($sign_begin_range,'Y-m-d H:i:s'));

				if($sign_end_range >= $sign_begin_range){
					E('_ERR_SIGN_RAGNE_ERROR');
					return false;
				}
			}

		}
	}

    /**
     * 验证假期排除日期与增加日期是否冲突
     * @param array $remove_day
     * @param array $add_work_day
     * @return bool
     */
    private function __validate_work_day($remove_day = array(), $add_work_day = array())
    {

        $array_merge = array_merge($remove_day['public'], $remove_day['user']);
        foreach ($array_merge as $key => $_obj) {

            $start_timestamp = rstrtotime($_obj['startTime'] . ' 00:00:00');
            $end_timestamp = rstrtotime($_obj['endTime'] . ' 00:00:00');

            foreach ($add_work_day as $_awd) {
                $_st = rstrtotime($_awd['startTime']) . ' 00:00:00';
                $_et = rstrtotime($_awd['endTime']) . ' 00:00:00';

                if ($start_timestamp >= $_st && $start_timestamp <= $_et) {
                    E('_ERR_SIGN_ADD_DAY_INCLUDE_FAILD');
                    return false;
                }
                if ($end_timestamp >= $_st && $end_timestamp <= $_et) {
                    E('_ERR_SIGN_ADD_DAY_INCLUDE_FAILD');
                    return false;
                }
            }

        }

        return true;
    }


    /**
     * 验证假期排除日期
     * @param $remove_day
     * @return bool
     */
    private function __validate_remove_work_day($remove_day = array())
    {
        $i = 0;
        $count = count($remove_day);

        $array_merge = array_merge($remove_day['public'], $remove_day['user']);

        foreach ($array_merge as $key => $_obj) {
            $start_timestamp = rstrtotime($_obj['startTime'] . ' 00:00:00');
            $end_timestamp = rstrtotime($_obj['endTime'] . ' 00:00:00');
            //假期排除日期范围错误,结束日期不能小于开始日期
            if ($end_timestamp < $start_timestamp) {
                E('_ERR_SIGN_REMOVE_DAY_TIME_FAILD');
                return false;
            }

            //验证假期排除日期有无重复
            for ($j = 0; $j < $count; $j++) {
                if ($i == $j) {
                    continue;
                }
                $_st = rstrtotime($array_merge[$j]['startTime']) . ' 00:00:00';
                $_et = rstrtotime($array_merge[$j]['endTime']) . ' 00:00:00';

                if ($start_timestamp >= $_st && $start_timestamp <= $_et) {
                    E('_ERR_SIGN_REMOVE_DAY_REPEAT_FAILD');
                    return false;
                }
                if ($end_timestamp >= $_st && $end_timestamp <= $_et) {
                    E('_ERR_SIGN_REMOVE_DAY_REPEAT_FAILD');
                    return false;
                }
            }

            $i++;
        }

        return true;
    }

    /**
     * 验证假期增加工作日期
     * @param $add_work_day
     * @return bool
     */
    private function __validate_add_work_day($add_work_day = array())
    {

        $i = 0;
        $count = count($add_work_day);

        foreach ($add_work_day as $_awd) {
            $start_timestamp = rstrtotime($_awd['startTime'] . '00:00:00');
            $end_timestamp = rstrtotime($_awd['endTime'] . '00:00:00');
            //假期增加日期范围错误,结束日期不能小于开始日期
            if ($end_timestamp < $start_timestamp) {
                E('_ERR_SIGN_ADD_DAY_TIME_FAILD');
                return false;
            }

            //验证假期增加日期有重复，请重新设置
            for ($j = 0; $j < $count; $j++) {
                if ($i == $j) {
                    continue;
                }
                $_st = rstrtotime($add_work_day[$j]['startTime']) . '00:00:00';
                $_et = rstrtotime($add_work_day[$j]['endTime']) . '00:00:00';
                if ($start_timestamp >= $_st && $start_timestamp <= $_et) {
                    E('_ERR_SIGN_ADD_DAY_REPEAT_FAILD');
                    return false;
                }
                if ($end_timestamp >= $_st && $end_timestamp <= $_et) {
                    E('_ERR_SIGN_ADD_DAY_REPEAT_FAILD');
                    return false;
                }
            }

            $i++;
        }

        return true;
    }

    /**
     * 排班分页
     * @param $params
     * @param $page_option
     * @param $order_option
     * @return array
     */
    public function list_page($params, $page_option, $order_option)
    {
		if(!empty($params['start_time'])){
			$params['start_time'] = rstrtotime($params['start_time'] . ' 00:00:00');
		}
		if(!empty($params['end_time'])){
			$params['end_time'] = rstrtotime($params['end_time'] . ' 23:59:59');
		}

        $total = $this->_d->count_by_params($params);

        $res_list = array();

        if ($total > 0) {
            $list = $this->_d->list_page($params, $page_option, $order_option);

            foreach ($list as &$val) {
                $this->refresh_schedule_disable($val);
                $this->format($val);
            }
            $res_list[] = $list;
            $res_list[] = $total;
        } else {
            return array($res_list, 0);
        }

        return $res_list;
    }

    /**
     * 每次列表查询 更新禁用中的状态
     * @param $data
     */
    public function refresh_schedule_disable(&$data)
    {
        $now_timestamp = NOW_TIME;
        $update_timestamp = $data['updated'];
        if ($data['enabled'] == SignScheduleModel::SCHEDULE_DISABLING) {
            if (differ_days(rgmdate($update_timestamp, 'Y-m-d'), rgmdate($now_timestamp, 'Y-m-d')) > 0) {
                $conds = array(
                    'enabled' => SignScheduleModel::SCHEDULE_DISABLE
                );
                $this->_d->update($data['id'], $conds);
                $data['enabled'] = SignScheduleModel::SCHEDULE_DISABLE;
                unset($conds);
            }
        }

        //验证排班是否过期
        if (!empty($data['schedule_end_time'])) {
            if (NOW_TIME >= $data['schedule_end_time']) {
                $pm = array(
                    'enabled' => SignScheduleModel::SCHEDULE_DISABLE
                );
                $this->_d->update($data['id'], $pm);
                $data['enabled'] = SignScheduleModel::SCHEDULE_DISABLE;
                unset($pm);
            }
        }
    }

    public function format(&$data)
    {

        // 时间字段
        $time_fields = array('created', 'updated', 'deleted');
        foreach ($time_fields as $_key) {
            $data['_' . $_key] = rgmdate($data[$_key], 'Y-m-d H:i:s');
        }

        if (empty($data['schedule_end_time'])) {
            $data['_schedule_time'] = '开始于' . rgmdate($data['schedule_begin_time'], 'Y-m-d');
        } else {
            $data['_schedule_time'] = rgmdate($data['schedule_begin_time'], 'Y-m-d') . '至' . rgmdate($data['schedule_end_time'], 'Y-m-d');
        }


        if (SignScheduleModel::SCHEDULE_ENABLED == $data['enabled']) {
            $data['_enabled'] = '已启用';
        } else if (SignScheduleModel::SCHEDULE_DISABLE == $data['enabled']) {
            $data['_enabled'] = '已禁用';
        } else if (SignScheduleModel::SCHEDULE_DISABLING == $data['enabled']) {
            $data['_enabled'] = '禁用中';
        }

        if (SignScheduleModel::CYCLE_UNIT_DAY == $data['cycle_unit']) {
            $data['_cycle_unit'] = '按天循环';
        } else if (SignScheduleModel::CYCLE_UNIT_WEEK == $data['cycle_unit']) {
            $data['_cycle_unit'] = '按周循环';
        } else if (SignScheduleModel::CYCLE_UNIT_MONTH == $data['cycle_unit']) {
            $data['_cycle_unit'] = '按月循环';
        }

        if ($data['range_on'] == SignScheduleModel::SIGN_RANGE_ON) {
            $data['_address'] = $data['address'] . ',  ' . $data['address_range'] . '米';
        } else {
            $data['_address'] = '';
        }


        $schedule_everyday_detail = unserialize($data['schedule_everyday_detail']);
        $data['schedule_everyday_detail'] = $schedule_everyday_detail;

		if(empty($data['add_work_day'])){
			$data['add_work_day'] = array();
		}else{
			$data['add_work_day'] = unserialize($data['add_work_day']);
		}

		if(empty($data['remove_day'])){
			$data['remove_day'] = array(
				'public' => array(),
				'user' => array()
			);
		}else{
			$data['remove_day'] = unserialize($data['remove_day']);
		}


        //部门id等于0
        if ($data['cd_id'] == 0) {
            $data['cd_name'] = '全公司';
        }

        $sign_batch_model = D('Sign/SignBatch');
        $sbname_array = array();
        foreach ($schedule_everyday_detail as $_val) {
            foreach ($_val as $k => $v) {
                if (empty($v['id'])) {
                    $sbname_array[] = '休息';
                } else {
                    $res = $sign_batch_model->get($v['id']);
                    $sbname_array[] = $res[0]['name'];
                }
            }
        }
        if (!empty($sbname_array)) {
            $sbname_array = array_flip(array_flip($sbname_array));
            $data['sbnames'] = implode(',', $sbname_array);
        } else {
            $data['sbnames'] = '';
        }

        unset($sbname_array);
        return $data;
    }

    /**
     * 根据Id获取排班详情
     * @param $id
     * @return bool
     */
    public function get_schedule_by_id($id)
    {
        $data = $this->_d->get($id);
        if (!empty($data)) {
            if ($data['cd_id'] == self::ALL_COMPANY) {
                $data['type'] = SignScheduleModel::SCHEDULE_ALL;
            } else {
                $data['type'] = SignScheduleModel::SCHEDULE_DEPT;
            }

            $data['_schedule_begin_time'] = rgmdate($data['schedule_begin_time'], 'Y-m-d');
            if (is_empty_variable($data['_schedule_end_time'])) {
                $data['_schedule_end_time'] = '';
            } else {
                $data['_schedule_end_time'] = rgmdate($data['schedule_end_time'], 'Y-m-d');
            }

            $schedule_everyday_detail = unserialize($data['schedule_everyday_detail']);

			if(empty($data['add_work_day'])){
				$data['add_work_day'] = array();
			}else{
				$data['add_work_day'] = unserialize($data['add_work_day']);
			}

			if(empty($data['remove_day'])){
				$data['remove_day'] = array(
					'public' => array(),
					'user' => array()
				);
			}else{
				$data['remove_day'] = unserialize($data['remove_day']);
			}

            $sign_batch_model = D('Sign/SignBatch');
            foreach ($schedule_everyday_detail as &$sed) {

                foreach ($sed as $k => &$v) {
                    if ($v['type'] == SignScheduleModel::REST_WORK_STATUS) {
						$v=null;
                        continue;
                    }
                    //查询班次名称
                    $sb = $sign_batch_model->get($v['id']);
                    $v['name'] = $sb[0]['name'];
					$v['work_begin'] = $sb[0]['work_begin'];
					$v['work_end'] = $sb[0]['work_end'];
                }
            }
			//Log::record('$schedule_everyday_detail..........'.var_export($schedule_everyday_detail,true));
            $data['schedule_everyday_detail'] = $schedule_everyday_detail;

            // 获取部门缓存数据
            $cache = &Cache::instance();
            $cache_departments = $cache->get('Common.department');
            $cd_id = $data['cd_id'];
            $cd_array = array(
                'id' => $cd_id,
                'isChecked' => true
            );
            foreach ($cache_departments as $cd) {
                if ($cd_id == $cd['cd_id']) {
                    $cd_array['name'] = $cd['cd_name'];
                }
            }

            $data['cd_info'][] = $cd_array;
        }

        return $data;
    }

    /**
     * 验证该班次是否被排班
     * @param $params
     * @param $page_option
     * @param $order_option
     */
    public function list_batch_in_schedule($sbid, $ssid, $flag)
    {
        return $this->_d->list_batch_in_schedule($sbid, $ssid, $flag);
    }

    /**
     * 修改排班
     * @param $params
     * @return bool
     */
    public function modify_schedule($params)
    {


        //将要修改的排班原始记录
        $old_data = $this->get($params['id']);

        //如果是启用，不能修改
        if ($old_data['enabled'] == SignScheduleModel::SCHEDULE_ENABLED) {
            E('_ERR_SIGN_SCHEDULE_NOTEDIT_FAILD');
            return false;
        }

        $type = $params['type'];
        //如果修改全公司记录
        if ($type == SignScheduleModel::SCHEDULE_ALL) {
            $this->__update_all_company($old_data, $params);
            return true;
        }

        //原始部门ID
        $old_cdid = $old_data['cd_id'];
        //新选择的部门ID数组
        $new_cdid_arr = $params['cd_id'];
        //原始部门ID是否还存在
        $old_cdid_exits_bool = true;
        //是否增加新的部门
        $new_cdid_bool = false;

        $old_cdid_arr_index = array_search($old_cdid, $new_cdid_arr);

        if ($old_cdid_arr_index == 0 || !empty($old_cdid_arr_index)) {
            //去掉保存的原始部门ID
            unset($new_cdid_arr[$old_cdid_arr_index]);
        } else {
            $old_cdid_exits_bool = false;
        }

        //去掉原始部门Id后，数组为空，没有新增部门
        if (!empty($new_cdid_arr)) {
            $new_cdid_bool = true;
        }

        //排班数组
        $schedule_array = $params['schedule_array'];
        //节假日排除日期
        $remove_day_arr = $params['remove_day'];
        //节假日增加日期
        $add_work_day_arr = $params['add_work_day'];

        //验证全公司是否已启用
        //$this->__check_all_company_enabled();

        //验证部门是否已存在排班
        if ($new_cdid_bool) {
            $this->__check_cdid_scheduled($new_cdid_arr);
        }

        //验证排班 班次之间时间不得冲突，且总时间跨度不得超过二十四小时
        $sbids = $this->__validate_schedule($schedule_array);

		$this->__validate_schedule_sign_range($schedule_array);

        //验证假期增加工作日期
        $this->__validate_add_work_day($add_work_day_arr);

        //验证假期排除日期
        $this->__validate_remove_work_day($remove_day_arr);

        //验证假期排除日期与增加日期是否冲突
        if (!empty($remove_day_arr) && !empty($add_work_day_arr)) {
            $this->__validate_work_day($remove_day_arr, $add_work_day_arr);
        }

        //封装数据
        $data = array();
        //默认禁用
        $data['enabled'] = SignScheduleModel::SCHEDULE_DISABLE;
        //每天排班详情
        $data['schedule_everyday_detail'] = serialize($schedule_array);
        //所有班次Id,逗号相隔
        $data['sbid'] = $sbids;
        //排班开始时间
        $data['schedule_begin_time'] = rstrtotime($params['schedule_begin_time'] . '00:00:00');
        //排班结束时间
        $data['schedule_end_time'] = $params['schedule_end_time'] ? rstrtotime($params['schedule_end_time'] . '00:00:00') : '';
        //周期单位
        $data['cycle_unit'] = $params['cycle_unit'];
        //周期数
        $data['cycle_num'] = $params['cycle_num'];
        //排除节假日
        $data['remove_day'] = $remove_day_arr ? serialize($remove_day_arr) : '';
        //增加上班日期
        $data['add_work_day'] = $add_work_day_arr ? serialize($add_work_day_arr) : '';
        //开启考勤范围
        $data['range_on'] = $params['range_on'];
        if ($params['range_on'] == SignScheduleModel::SIGN_RANGE_ON) {
            //地点
            $data['address'] = $params['address'];
            //考勤范围
            $data['address_range'] = $params['address_range'];
            //精度
            $data['longitude'] = $params['longitude'];
            //纬度
            $data['latitude'] = $params['latitude'];
        }

        try {
            //开启事务
            $this->start_trans();

            if ($old_cdid_exits_bool) {
                //更新原始数据
                $this->_d->update($params['id'], $data);

            } else {
                //删除原始数据
                $this->_d->delete($params['id']);
            }

			//插入排班历史变更表
			$this->_insert_schedule_log($old_cdid, $params['id'], $old_data['schedule_begin_time'], $old_data['cycle_unit'], $old_data['cycle_num'], $old_data['schedule_everyday_detail'], $old_data['add_work_day'], $old_data['remove_day']);

            //有新增部门,插入新的数据
            if ($new_cdid_bool) {
                foreach ($new_cdid_arr as $_v) {
                    $data['cd_id'] = $_v;
                    $insert_sign_schedule_data[] = $data;
                }
                //插入排班表
                $this->__batch_insert_schedule($insert_sign_schedule_data);
            }


            //提交事务
            $this->commit();

        } catch (\Exception $e) {
            Log::record('更新排班（非全公司）异常：');
            Log::record($e->getMessage());
            $this->rollback();
            E('_ERR_SIGN_SYSTEM_BUSY');
            return false;
        }

        return true;
    }

    /**
     * 验证全公司是否已启用
     * @return bool
     */
    private function __check_all_company_enabled()
    {

        $conds = array(
            'cd_id' => self::ALL_COMPANY
        );
        $data = $this->_d->get_by_conds($conds);

        if (!empty($data)) {
            if ($data['enabled'] == SignScheduleModel::SCHEDULE_ENABLED || $data['enabled'] == SignScheduleModel::SCHEDULE_DISABLING) {
                E('_ERR_SIGN_SCHEDULE_ALLDEPT_ENABLED_FAILD');
                return true;
            }
        }

        return true;
    }

    /**
     * 验证选择的部门是否已有排班
     * @param array $cd_id_arr
     * @return bool
     */
    private function __check_cdid_scheduled($cd_id_arr = array())
    {

        //验证部门是否已存在排班
        $conds = array();
        foreach ($cd_id_arr as $_v) {
            $conds['cd_id'] = $_v;
            $result = $this->_d->get_by_conds($conds);
            if (!empty($result)) {
                E('_ERR_SIGN_SCHEDULE_DEPT_REPEAT_FAILD');
                return false;
            }
        }

        return true;
    }

    /**
     * 更新全公司排班记录
     * @param $params
     * @return bool
     */
    private function __update_all_company($old_data, $params)
    {

        if (!is_array($params['cd_id'])) {
            E('_ERR_SIGN_SCHEDULE_EDIT_TYPE_FAILD');
            return false;
        }
        $cdid_array = $params['cd_id'];
        if ($cdid_array[0]['cd_id'] != self::ALL_COMPANY) {
            E('_ERR_SIGN_SCHEDULE_EDIT_TYPE_FAILD');
            return false;
        }

        //排班数组
        $schedule_array = $params['schedule_array'];
        //节假日排除日期
        $remove_day_arr = $params['remove_day'];
        //节假日增加日期
        $add_work_day_arr = $params['add_work_day'];

        //验证排班 班次之间时间不得冲突，且总时间跨度不得超过二十四小时
        $sbids = $this->__validate_schedule($schedule_array);

        //验证假期增加工作日期
        $this->__validate_add_work_day($add_work_day_arr);

        //验证假期排除日期
        $this->__validate_remove_work_day($add_work_day_arr);

        //验证假期排除日期与增加日期是否冲突
        if (!empty($remove_day_arr) && !empty($add_work_day_arr)) {
            $this->__validate_work_day($remove_day_arr, $add_work_day_arr);
        }

        //封装数据
        $data = array();
        //默认禁用
        $data['enabled'] = SignScheduleModel::SCHEDULE_DISABLE;
        //每天排班详情
        $data['schedule_everyday_detail'] = serialize($schedule_array);
        //所有班次Id,逗号相隔
        $data['sbid'] = $sbids;
        //排班开始时间
        $data['schedule_begin_time'] = rstrtotime($params['schedule_begin_time'] . '00:00:00');
        //排班结束时间
        $data['schedule_end_time'] = $params['schedule_end_time'] ? rstrtotime($params['schedule_end_time'] . '00:00:00') : '';
        //周期单位
        $data['cycle_unit'] = $params['cycle_unit'];
        //周期数
        $data['cycle_num'] = $params['cycle_num'];
        //排除节假日
        $data['remove_day'] = $remove_day_arr ? serialize($remove_day_arr) : '';
        //增加上班日期
        $data['add_work_day'] = $add_work_day_arr ? serialize($add_work_day_arr) : '';
        //开启考勤范围
        $data['range_on'] = $params['range_on'];
        if ($params['range_on'] == SignScheduleModel::SIGN_RANGE_ON) {
            //地点
            $data['address'] = $params['address'];
            //考勤范围
            $data['address_range'] = $params['address_range'];
            //精度
            $data['longitude'] = $params['longitude'];
            //纬度
            $data['latitude'] = $params['latitude'];
        }

        try {
            //开启事务
            $this->start_trans();

            //更新数据
            $this->_d->update($params['id'], $data);
            //插入排班历史变更表
            $this->_insert_schedule_log($old_data['cd_id'], $params['id'], $old_data['schedule_begin_time'], $old_data['cycle_unit'], $old_data['cycle_num'], $old_data['schedule_everyday_detail'], $old_data['add_work_day'], $old_data['remove_day']);
            //提交事务
            $this->commit();

        } catch (\Exception $e) {
            Log::record('更新排班（全公司）异常：');
            Log::record($e->getMessage());
            $this->rollback();
            E('_ERR_SIGN_SYSTEM_BUSY');
            return false;
        }


        return true;
    }


    /**
     * 验证是否已禁用
     * @param $id
     * @return bool
     */
    public function check_disable($id)
    {
        $data = $this->get($id);
        if (empty($data)) {
            E('_ERR_SIGN_SCHEDULE_NOEXIST_ERROR');
            return false;
        }

        if ($data['enabled'] != SignScheduleModel::SCHEDULE_DISABLE) {
            return false;
        }

        return true;
    }

    /**
     * 逻辑删除排班
     * @param $id
     * @return bool
     */
    public function delete_schedule($id)
    {
        $data = $this->_d->get($id);
        if (empty($data)) {
            E('_ERR_SIGN_SCHEDULE_NOEXIST_ERROR');
            return false;
        }
		//默认排班不能删除
		if($data['cd_id'] == self::ALL_COMPANY){
			E('_ERR_DELETE_DEFULT_SCHEDULE_FAILD');
			return false;
		}

        //如果是禁用中，不能删除
        if ($data['enabled'] == SignScheduleModel::SCHEDULE_ENABLED) {
            E('_ERR_SIGN_SCHEDULE_DELETE_FAILD');
            return false;
        }

        try {
            $this->start_trans();

            $this->_d->delete($id);

            $this->commit();
        } catch (\Exception $e) {
            Log::record('删除排班异常：');
            Log::record($e->getMessage());
            $this->rollback();
            E('_ERR_SIGN_SYSTEM_BUSY');
            return false;
        }

        return true;
    }

    /**
     * 启用/禁用排班
     * @param $params
     * @return bool
     */
    public function enabled_schedule($params)
    {

        $data = $this->_d->get($params['id']);
		if(empty($data)){
			E('_ERR_SIGN_SCHEDULE_NOEXIST_ERROR');
			return false;
		}

        $conds = array();

        //启用
        if ($params['enabled'] == SignScheduleModel::SCHEDULE_ENABLED) {

			//验证排班 班次之间时间不得冲突，且总时间跨度不得超过二十四小时
			$this->__validate_schedule(unserialize($data['schedule_everyday_detail']));

			$this->__validate_schedule_sign_range(unserialize($data['schedule_everyday_detail']));

            $conds['enabled'] = SignScheduleModel::SCHEDULE_ENABLED;

        } elseif ($params['enabled'] == SignScheduleModel::SCHEDULE_DISABLE) {
            //禁用
            $conds['enabled'] = SignScheduleModel::SCHEDULE_DISABLE;

        }else {
            E('_ERROR_PARAM_VAL_NULL');
            return false;
        }

        try {
            $this->start_trans();
            $this->_d->update($params['id'], $conds);
            $this->commit();
        } catch (\Exception $e) {
            Log::record('启用/禁用排班异常：');
            Log::record($e->getMessage());
            $this->rollback();
            E('_ERR_SIGN_SYSTEM_BUSY');
            return false;
        }

        return true;
    }

    /**
     * 检查是否在节假日增加上班日期里
     * @param $add_work_day
     * @return bool
     */
    protected function _check_in_add_work_day($add_work_day)
    {
		if(empty($add_work_day)){
			return false;
		}

		$add_work_day = unserialize($add_work_day);

        foreach ($add_work_day as $_awd) {
            $_st = rstrtotime($_awd['startTime']) . ' 00:00:00';
            $_et = rstrtotime($_awd['endTime']) . ' 23:59:59';

            if (NOW_TIME >= $_st && NOW_TIME <= $_et) {
                return true;
            }
        }
        return false;
    }

    /**
     * 检查今天是否已签过到
     * @param $m_uid
     * @param $cd_id
     * @return bool
     */
    protected function _check_dept_signed($m_uid, $cd_id, $date)
    {
        $record_model = D('Sign/SignRecord');
        //$today_date = rgmdate(NOW_TIME, 'Y-m-d');
        $params = array(
            'm_uid' => $m_uid,
            'cd_id' => $cd_id,
            'sr_created' => $date
        );
        $sign_data = $record_model->list_sign_record_by_params($params);

        if (empty($sign_data)) {
            return false;
        }

        return true;
    }

    /**
     * 刷新禁用中的排班状态
     */
    protected function _refresh_schedule_disabling()
    {
        $conds = array(
            'enabled' => SignScheduleModel::SCHEDULE_DISABLING
        );
        $list = $this->list_by_conds($conds);

        unset($conds);

        if (empty($list)) {
            return true;
        }

        foreach ($list as $obj) {
            if (differ_days(rgmdate($obj['updated'], 'Y-m-d'), rgmdate(NOW_TIME, 'Y-m-d')) > 0) {
                $pm = array(
                    'enabled' => SignScheduleModel::SCHEDULE_DISABLE
                );
                $this->_d->update($obj['id'], $pm);
                $obj['enabled'] = SignScheduleModel::SCHEDULE_DISABLE;
                unset($pm);
                continue;
            }

            //验证排班是否过期
            if (!empty($obj['schedule_end_time'])) {
                if (NOW_TIME >= $obj['schedule_end_time']) {
                    $pm = array(
                        'enabled' => SignScheduleModel::SCHEDULE_DISABLE
                    );
                    $this->_d->update($obj['id'], $pm);
                    $obj['enabled'] = SignScheduleModel::SCHEDULE_DISABLE;
                    unset($pm);
                }
            }
        }

    }

	/**
	 * 是否启用了全公司排班
	 * @return bool true-是 false-否
	 */
	private function __if_all_commpany_on(&$all_company_data){

		$conds = array(
			'cd_id' => self::ALL_COMPANY
		);
		$all_company_data = $this->_d->get_by_conds($conds);

		if(empty($all_company_data)){
			Log::record('数据异常：默认全公司排班不存在!');
			E('_ERR_SECHEDULE_DATA_ERROR');
			return false;
		}

		//如果全公司已启用
		if ($all_company_data['enabled'] != SignScheduleModel::SCHEDULE_DISABLE) {
			return true;
		}

		return false;
	}

	/**
	 * 获取用户当前部门和所有上级部门id
	 * @param $m_uid
	 * @return array|bool
	 */
	protected function _list_user_departments_by_uid($m_uid){
		//获取用户当前部门
		$dept_service = Department::instance();
		$member_dept_array = $dept_service->list_cdid_by_uid($m_uid);

		//当前用户查不到部门
		if (empty($member_dept_array)) {
			E('_ERR_MEMBER_DEPT_NOT_EXITS_FAILD');
			return false;
		}

		return $member_dept_array;

	}

	/**
	 * 封装部门id、name
	 * @param $cdid_array
	 * @param $dept_array
	 */
	public function formate_department_name($cdid_array, &$dept_array){

		// 获取部门缓存数据
		$cache = &Cache::instance();
		$cache_departments = $cache->get('Common.department');

		foreach ($cache_departments as $_dp) {
			if (in_array($_dp['cd_id'], $cdid_array)) {
				$dept_array[$_dp['cd_id']] = $_dp['cd_name'];
			}
		}
	}

	/**
	 * 获取用户当前部门的所有上级部门
	 * @param $cd_id
	 * @return array
	 */
	public function list_parent_departments_by_cdid($cd_id){
		$result = array();
		//获取用户当前部门的所有上级部门
		$dept_service = Department::instance();
		$dept_service->list_parent_cdids($cd_id, $result);
		return $result;
	}

	/**
	 * 获取部门对应的排班
	 * @param $cdid_array
	 * @return mixed
	 */
	public function list_schedules_by_cdids($cdid_array){
		$conds = array(
			'cdid_array' => $cdid_array,
			'enabled' => SignScheduleModel::SCHEDULE_DISABLE
		);
		return $this->_d->list_schedule_by_params($conds);
	}

	/**
	 * 今天是否在节假日里
	 * @param $remove_work_day
	 * @return bool true-在，false-不在
	 */
	protected function _today_in_remove_day($remove_work_day){

		if(empty($remove_work_day)){
			return false;
		}

		//是否在节假日里
		$remove_day = unserialize($remove_work_day);
		//法定节假日
		$public_day = $remove_day['public'];
		//Log::record('$public_day------'.var_export($public_day,true));
		//用户自定义节假日
		$user_day = $remove_day['user'];
		//Log::record('$user_day------'.var_export($user_day,true));
		foreach($public_day as $pd){
			$start = strtotime($pd['startTime'].' 00:00:00');
			$end = strtotime($pd['endTime'].' 23:59:59');
			if(NOW_TIME>=$start && NOW_TIME <= $end){
				return true;
			}
		}
		foreach($user_day as $ud){
			$start = strtotime($ud['startTime'].' 00:00:00');
			$end = strtotime($ud['endTime'].' 23:59:59');
			if(NOW_TIME>=$start && NOW_TIME <= $end){
				return true;
			}
		}

		return false;
	}


    /**
     * 查询最近一次签到的排班班次数据
     * @param $m_uid
     * @param $cd_id
     * @param $result_batch
     * @return bool
     */
    protected function _list_later_batch($m_uid, $cd_id, &$result_batch, &$result_schedule_id)
    {
        $record_model = D('Sign/SignRecord');
        $params = array(
            'm_uid' => $m_uid,
            'cd_id' => $cd_id
        );
        //先查询最近一次的具体日期
        $later = $record_model->select_later_date($params);
        if (empty($later)) {
            return false;
        }
		//Log::record('最近一次签到的记录--'.var_export($later,true));
		//排班
		$_conds = array(
			'id' => $later['schedule_id'],
			'enabled' => SignScheduleModel::SCHEDULE_ENABLED
		);
		$schedule = $this->_d->get_by_conds($_conds);
		if(empty($schedule)){
			return false;
		}
		//Log::record('最近一次签到的记录的排班--'.var_export($schedule,true));
		$result_schedule_id = $later['schedule_id'];

		$which_day = $this->find_today_batch_index($schedule['cycle_unit'], $schedule['schedule_begin_time'], $schedule['cycle_num'] , $later['sr_created']);
		//Log::record('休息日上班找到哪天-------' . $which_day);

		//下标值
		$index = $which_day - 1;

		//每一天的排班班次数据
		$schedule_everyday_detail = unserialize($schedule['schedule_everyday_detail']);
		//Log::record('每天的排班班次：'.var_export($schedule_everyday_detail,true));

		//是否超过下标
		if (count($schedule_everyday_detail) < $index) {
			return false;
		}

		//取出要使用的排班班次数据
		$result_batch = $schedule_everyday_detail[$index];
		//Log::record('休息日取出要使用的排班班次数据----'.var_export($result_batch,true));
		$_sbids = array_column($result_batch, 'id');
		if(empty($_sbids)){
			$size = count($schedule_everyday_detail);
			$j = $index==0 ? $size-1 : $index-1;
			for($i=$j; $i>=0; $i--){
				if($i==0){
					$i=$size-1;
				}
				if($i==$index){
					return;
				}
				$result_batch = $schedule_everyday_detail[$i];
				$_sbids = array_column($result_batch, 'id');
				if(empty($_sbids)){
					continue;
				}

			}

		}

		if(empty($_sbids)){
			return false;
		}

		$sign_batch_model = D('Sign/SignBatch');
		foreach($result_batch as $k => &$_ba){
			$tmp = $sign_batch_model->get($_ba['id']);
			$_ba = $tmp[0];
		}

		if (empty($result_batch)) {
            return false;
        }

        //根据具体日期查询当天所有的班次
//        $params['sr_work_begin'] = rgmdate($later['sr_created'], 'Y-m-d');
//        $result_batch = $record_model->list_later_batch($params);
//        if (empty($result_batch)) {
//            return false;
//        }

        return true;
    }

    /**
     * 找到今天是第几天的班次
     * @param $type
     * @param $date 排班开始时间
     * @return int
     */
    public function find_today_batch_index($type, $date, $cycle_num, $find_date)
    {
        $result = -1;
        if ($type == SignScheduleModel::CYCLE_UNIT_DAY) {
            $result = find_schedule_day($find_date, $date, $cycle_num);
        } elseif ($type == SignScheduleModel::CYCLE_UNIT_WEEK) {
            $week_array = array(7, 1, 2, 3, 4, 5, 6);
            //$result = $week_array[date("w")];
            $result = $week_array[rgmdate($find_date, 'w')];
        } else {
            $result = rgmdate($find_date,"j");
        }

        return $result;
    }


    /**
     * 查询排班规则
     * @param $params
     * @return array
     */
    public function get_schedule_rule($params)
    {
        $batch_model = D('Sign/SignBatch');

        //今天要使用第几天的班次下标
        $batch_index = $params['batch_index'];
        //$today_batch_index = $params['batch_index'];

        /*根据部门ID、排班ID查询排班数据*/
        $conds = array(
            'id' => $params['schedule_id']
        );
        $data = $this->_d->get_by_conds($conds);

        //排班不存在
        if (empty($data)) {
            E('_ERR_SIGN_SCHEDULE_NOEXIST_ERROR');
            return false;
        }

        //如果是休息日上班
        if ($params['type'] == SignScheduleModel::REST_AND_WORK_STATUS) {
            //最近一次的签到排班所有班次id
            $batch_id_array = explode(',', $params['batch_id']);
            foreach ($batch_id_array as $bid) {
                $today_batch_array[] = array(
                    'id' => $bid
                );
            }


        } else {
            $schedule_everyday_detail = unserialize($data['schedule_everyday_detail']);
            //今天的所有班次
            $today_batch_array = $schedule_everyday_detail[$batch_index];
        }

        $result = array();
        foreach ($today_batch_array as $tba) {
            $conds = array(
                'sbid' => $tba['id']
            );
            $batch = $batch_model->get_by_conds($conds);
            if (empty($batch)) {
                E('_ERR_SIGN_SCHEDULE_NOEXIST_ERROR');
                return false;
            }


            $tmp_batch_array = array(
                'batch_id' => $batch['sbid'],
                'batch_type' => $batch['type'],
                'batch_name' => $batch['name'],
                'work_begin' => rgmdate($batch['work_begin'], 'H:i'),
                'come_late_range' => $batch['come_late_range'] / 60,//迟到范围
                'sign_on_id' => '',
                'sign_on_time' => '',
                'sign_off_id' => '',
                'sign_off_time' => '',
            );

			$this->_format_tomorrow_time($bool, $work_end, $batch['work_begin'], $batch['work_end']);
			$tmp_batch_array['work_end'] = $work_end;

            //如果是常规班次
            if ($batch['type'] == SignBatchModel::BTACH_COMMON_TYPE) {

				//打卡开始范围
				$sign_start_range = $batch['work_begin'] - $batch['sign_start_range'];

				//打卡结束范围
				$sign_end_range = $batch['work_end'] + $batch['sign_end_range'];

				//格式化次日时间
				$this->_format_tomorrow_time($result_bool, $result_end_range, $batch['work_begin'], $sign_end_range);
				$tmp_batch_array['sign_end_range'] = $result_end_range;

				//前日
				$this->_format_yesterday_time($result_start_range, $sign_start_range, $batch['work_begin']);
				$tmp_batch_array['sign_start_range'] = $result_start_range;

				//早退范围
                $tmp_batch_array['leave_early_range'] = $batch['leave_early_range'] / 60;

                $tmp_batch_array['late_range_on'] = $batch['late_range_on'];

                //如果启用了加班，计算几点签退计为加班
                if ($batch['late_range_on'] == SignBatchModel::LATE_RANGE_ON) {
                    $overtime = $batch['work_end'] + $batch['late_range'];
                    $tmp_batch_array['overtime'] = rgmdate($overtime, 'H:i');
					if($bool){
						$tmp_batch_array['overtime'] = '次日 ' . rgmdate($overtime, 'H:i');
					}else if(differ_days(rgmdate($overtime,'Y-m-d'), rgmdate($batch['work_end'], 'Y-m-d')) > 0){
						$tmp_batch_array['overtime'] = '次日 ' . rgmdate($overtime, 'H:i');
					}
                }

            } elseif ($batch['type'] == SignBatchModel::BTACH_ELASTIC_TYPE) {//如果是弹性班次
                $tmp_batch_array['min_work_hours'] = $batch['min_work_hours'];
                $tmp_batch_array['late_work_time_on'] = $batch['late_work_time_on'];
                $tmp_batch_array['late_work_time'] = rgmdate($batch['late_work_time'], 'H:i');
                $tmp_batch_array['absenteeism_range_on'] = $batch['absenteeism_range_on'];
                $tmp_batch_array['absenteeism_range'] = $batch['absenteeism_range'];
                //弹性的考勤时间范围就是上下班时间
				$sign_start_range = rgmdate($batch['work_begin'], 'H:i');
                $tmp_batch_array['sign_start_range'] = $sign_start_range;
                //格式化次日时间
                $this->_format_tomorrow_time($bool, $sign_end_range, $batch['work_begin'], $batch['work_end']);
                $tmp_batch_array['sign_end_range'] = $sign_end_range;
            }

            $batch_array[] = $tmp_batch_array;

            unset($tmp_batch_array);

        }

        $result['batch_array'] = $batch_array;
        $result['range_on'] = $data['range_on'];
        $result['address'] = $data['address'];
        $result['address_range'] = $data['address_range'];

        return $result;
    }

    /**
     * 格式化次日时间
     * @param $work_begin
     * @param $work_end
     * @return string
     */
    protected function _format_tomorrow_time(&$result_bool, &$result_time, $work_begin, $work_end)
    {

        $work_begin_time = rgmdate($work_begin, 'Y-m-d');
        $work_end_time = rgmdate($work_end, 'Y-m-d');

        // 次日
        if (differ_days($work_begin_time, $work_end_time) > 0 ) {
			$result_time = '次日 ' . rgmdate($work_end, 'H:i');
			$result_bool = true;
        }else{
			$result_time = rgmdate($work_end, 'H:i');
			$result_bool = false;
		}

    }

	/**
     * 格式化前日时间
     * @param $sign_start_range
     * @return string
     */
    protected function _format_yesterday_time(&$result_time, $sign_start_range, $work_begin)
    {
		$sub_day = differ_days(rgmdate($sign_start_range, 'Y-m-d'), rgmdate($work_begin, 'Y-m-d'));

        if ($sub_day == 2) {
            $result_time = '前日 ' . rgmdate($sign_start_range, 'H:i');
			return true;
        }elseif ($sub_day == 1){
			$result_time = '昨日 ' . rgmdate($sign_start_range, 'H:i');
			return true;
		}else {
            $result_time = rgmdate($sign_start_range, 'H:i');
			return true;
        }

		return false;
    }

    /**
     * 插入排班历史变更表
     * @param $cd_id
     * @param $scheule_id
     * @param $init_time
     * @param $cycle_unit 周期单位 1-天,2-周,3-月
     * @param $cycle_num 周期数 1-7天
     * @param $schedule_array
     * @return bool
     */
    protected function _insert_schedule_log($cd_id, $scheule_id, $init_time, $cycle_unit, $cycle_num, $schedule_array, $add_work_day, $remove_day)
    {

        $data = array(
            'schedule_everyday_detail' => $schedule_array,
            'add_work_day' => $add_work_day,
            'remove_day' => $remove_day,
            'cd_id' => $cd_id,
            'schedule_id' => $scheule_id,
            'cycle_unit' => $cycle_unit,
            'cycle_num' => $cycle_num,
            'init_time' => $init_time
        );

        $schedule_log_model = D('Sign/SignScheduleLog');
        //查询最近一条排班变更记录
        $_params = array(
            'cd_id' => $cd_id,
            'schedule_id' => $scheule_id
        );
        $later_data = $schedule_log_model->get_later_schedule_log($_params);

        //没有记录
        if (empty($later_data)) {
            $data['begin_time'] = rstrtotime(rgmdate(NOW_TIME, 'Y-m-01 H:i:s')); //当月第一天

            //结束日期=今天减去一天
            $end_timestamp = NOW_TIME-86400;
            $data['end_time'] = $end_timestamp;

            $schedule_log_model->insert($data);
            return true;
        }

        //如果有记录，判断是不是今天的记录，如果是直接更新
        $created = differ_days(rgmdate($later_data['created'], 'Y-m-d'), rgmdate(NOW_TIME, 'Y-m-d'));
        if ($created == 0) {
//            $data['id'] = $later_data['id'];
//            $schedule_log_model->update($later_data['id'], $data);
            return true;
        }

        //如果不是今天的记录，计算开始、结束时间
        $begin_timestamp = $later_data['end_time']+86400;

        //结束日期=今天减去一天
        $end_timestamp =NOW_TIME-86400;

        $data['begin_time'] = $begin_timestamp;
        $data['end_time'] = $end_timestamp;
        $schedule_log_model->insert($data);

        return true;

    }

    /**
     * 根据部门查询排班规则(指定部门or全公司)
     * @param array $params 传入参数
     */
    public function get_schedule_for_dep($params)
    {

        $record = $this->_d->get_schedule_for_dep($params);
        return $record;
    }

    /**
     * 根据部门查询排班规则(指定部门)
     * @param array $params 传入参数
     */
    public function get_schedule_for_deps($params)
    {

        $record = $this->_d->get_schedule_for_deps($params);
        return $record;
    }


    /**
     * 根据部门id获取对应的排班信息
     * @param $params
     */
    public function list_schedule($params){
        $data = $this->_d->list_schedule($params);
        return $data;
    }


	/**
	 * 验证班次下班最晚签退时间是否是跨天
	 * @param $batch_obj
	 * @return bool true-是 false-否
	 */
	protected function _validate_sign_time_next_day($batch_obj){

		$sign_end_range = $batch_obj['work_end'];

		//如果是常规班次
		if($batch_obj['type'] == SignBatchModel::BTACH_COMMON_TYPE){
			$sign_end_range = $sign_end_range + $batch_obj['sign_end_range'];
		}

		//次日
		if(differ_days(rgmdate($batch_obj['work_begin'], 'Y-m-d'), rgmdate($sign_end_range, 'Y-m-d')) > 0){
			return true;
		}

		return false;
	}
	/**
	 * 查询排班班次信息
	 * @param $params
	 * @return array
	 */
	public function dept_schedule_by_params($params)
	{

		//Log::record('参数---'.var_export($params,true));
		$batch_model = D('Sign/SignBatch');
		$record_model = D('Sign/SignRecord');
		$record_detail_model = D('Sign/SignDetail');

		//今天要使用第几天的班次下标
		$batch_index = $params['batch_index'];
		$conds = array(
			'id' => $params['schedule_id']
		);
		$data = $this->_d->get_by_conds($conds);

		if (empty($data)) {
			E('_ERR_SIGN_SCHEDULE_NOEXIST_ERROR');
			return false;
		}


		$result = array();
		$result['cd_id'] = $params['cd_id'];

		$yesterday_bool = false;
		/*构造查询签到/签退记录的条件*/
		$today_date = rgmdate(NOW_TIME, 'Y-m-d');
		if($params['schedule_type'] == SignScheduleModel::YESTERDAY){
			$today_date = rgmdate(NOW_TIME - 86400, 'Y-m-d');
			$yesterday_bool = true;
		}

		//如果是休息日上班
		if ($params['type'] == SignScheduleModel::REST_AND_WORK_STATUS) {
			if (empty($params['batch_id'])) {
				E('_ERR_TODAY_REST_FAILD');
				return false;
			}
			$batch_id_array = explode(',', $params['batch_id']);
			foreach ($batch_id_array as $bid) {
				$today_batch_array[] = array(
					'id' => $bid
				);
			}
		} else {

			if ($params['type'] == SignScheduleModel::REST_WORK_STATUS) {
				E('_ERR_TODAY_REST_FAILD');
				return false;
			}

			$schedule_everyday_detail = unserialize($data['schedule_everyday_detail']);
			//今天的所有班次
			$today_batch_array = $schedule_everyday_detail[$batch_index];
		}


		//Log::record('是昨天还是今天的排班......' . $params['schedule_type']);
		//Log::record('今天的班次......' . var_export($today_batch_array,true));



		$record_params = array(
			'm_uid' => $params['m_uid'],
			'sr_created' => $today_date
		);

		//Log::record('$today_batch_array-----'.var_export($today_batch_array,true));
		$batch_array = array();
		foreach ($today_batch_array as $tba) {

			$batch = $batch_model->get($tba['id']);
			if (empty($batch)) {
				E('_ERR_SIGN_SCHEDULE_NOEXIST_ERROR');
				return false;
			}


			$tmp_batch_array = array(
				'batch_id' => $batch[0]['sbid'],
				'batch_type' => $batch[0]['type'],
				'batch_name' => $batch[0]['name'],
				'work_begin' => rgmdate($batch[0]['work_begin'], 'H:i'),
				'sign_on_id' => '',
				'sign_on_time' => '',
				'sign_off_id' => '',
				'sign_off_time' => '',
			);
			$this->_format_tomorrow_time($bool, $work_end, $batch[0]['work_begin'], $batch[0]['work_end']);
			$tmp_batch_array['work_end'] = $work_end;

			$tmp_batch_array['flag'] = SignScheduleModel::TODAY;
			if($yesterday_bool){
				if($this->_validate_sign_time_next_day($batch[0])){
					$tmp_batch_array['flag'] = SignScheduleModel::YESTERDAY;
				}
			}

			/*查询当前班次签到/签退情况*/
			$record_params['sr_batch'] = $tba['id'];
			$sign_data = $record_model->list_sign_record_by_params($record_params);
			Log::record('查询签到记录,$params.........'.var_export($params,true));
			//Log::record('$sign_data.........'.var_export($sign_data,true));
			foreach ($sign_data as $sd) {
				//是否有变更部门情况，上午打卡在A部门，下午打卡在B部门
				if($sd['cd_id'] != $params['cd_id']){
					Log::record('一天内不能再多个部门签到,签到记录对应的部门ID：-----' .$sd['cd_id'] . '接口参数部门ID'.$params['cd_id']);
					E('_ERR_SIGN_DEPT_ERROR');
					return false;
				}
				//查询签到备注信息
				$cd = array(
					'sr_id' => $sd['sr_id'],
//                    'start_time' => $batch[0]['work_begin'],
//                    'end_time' => $batch[0]['work_end']
				);
				$detail = $record_detail_model->list_by_params($cd);
				foreach ($detail as &$dt) {
					$dt['_created'] = rgmdate($dt['sd_created'], 'H:i:s');
				}
				if ($sd['sr_type'] == SignRecordModel::SIGN_TYPE_ON) {//签到
					$tmp_batch_array['sign_on_id'] = $sd['sr_id'];

					$tmp_batch_array['sign_on_detail_array'] = $detail;
					$tmp_batch_array['sign_on_address'] = $sd['sr_address'];
					$tmp_batch_array['sign_on_time'] = rgmdate($sd['sr_signtime'], 'H:i');

					continue;
				}

				//签退
				$tmp_batch_array['sign_off_id'] = $sd['sr_id'];
				$tmp_batch_array['sign_off_detail_array'] = $detail;
				$tmp_batch_array['sign_off_address'] = $sd['sr_address'];
				$tmp_batch_array['sign_off_time'] = rgmdate($sd['sr_signtime'], 'H:i');

			}

			$batch_array[] = $tmp_batch_array;
			unset($tmp_batch_array);

		}
		//Log::record('排班详情-----'.var_export($batch_array,true));
		$result['batch_array'] = $batch_array;
		return $result;

	}

	/**
	 * 根据m_uid获取所属全部部门和班次信息
	 * @param $m_uid
	 * @return bool
	 */
	public function get_batch_by_muid($m_uid)
	{

		//Log::record('查询部门和班次开始');

		$result = array();

		//获取用户部门排班，所在部门没有排班，找上级部门，一直找到默认排班
		$this->_get_user_schedule($m_uid, $schedule_data, $dept_array);

		//Log::record('用户部门排班数：-------'.var_export($schedule_data,true));
		//Log::record('用户有可用排班的部门数：-------'.var_export($dept_array,true));

		//这部门没有任何排班，也是醉了
		if (empty($schedule_data)) {
			E('_ERR_SIGN_SCHEDULE_NOEXIST_ERROR');
			return false;
		}

		//是否开启休息日允许考勤
		$rest_day_sign_bool = $this->__if_rest_day_sign_on();

		$yesterday_timestamp = NOW_TIME-86400;

		foreach($schedule_data as $_sd){
			unset($res);
			unset($name);
			unset($sbid);
			//排班还没开始
			$schedule_begin_time = rstrtotime(rgmdate($_sd['schedule_begin_time'],'Y-m-d') . ' 00:00:00');
			if(NOW_TIME < $schedule_begin_time){
				continue;
			}
			//排班已过期
			if(!empty($_sd['schedule_end_time'])){
				$schedule_end_time = rstrtotime(rgmdate($_sd['schedule_end_time'],'Y-m-d') . ' 23:59:59');
				if(NOW_TIME > $schedule_end_time){
					continue;
				}
			}

			$res['cd_name'] = $dept_array[$_sd['cd_id']];
			$res['cd_id'] = $_sd['cd_id'];
			$res['schedule_id'] = $_sd['id'];

			//找到昨天的排班班次
			$this->find_schedule_batch_by_date($yesterday_index, $yesterday_batch_array, $yesterday_timestamp, $_sd);
			$yesterday_work_bool = $this->check_department_really_work($result_yesterday_batch,$yesterday_bool, $result_yesterday_schedule_id,  $yesterday_batch_array, $_sd, $rest_day_sign_bool, $m_uid);
//			Log::record('$yesterday_batch_array---------'.var_export($yesterday_batch_array,true));
//			Log::record('$yesterday_work_bool------'.var_export($yesterday_work_bool,true));
//			Log::record('昨天的班次------'.var_export($result_yesterday_batch,true));

			//找到今天的排班班次
			$this->find_schedule_batch_by_date($today_index, $today_batch_array, NOW_TIME, $_sd);
			$today_work_bool = $this->check_department_really_work($result_today_batch,$today_bool, $result_today_schedule_id,  $today_batch_array, $_sd, $rest_day_sign_bool, $m_uid);
//			Log::record('$today_batch_array---------'.var_export($today_batch_array,true));
//			Log::record('$today_work_bool------'.var_export($today_work_bool,true));
//			Log::record('今天的班次------'.var_export($result_today_batch,true));

			if($yesterday_work_bool){
				Log::record('昨天上班', Log::ALERT);
				//昨天上班，先判断昨天的最后一个班次的最晚打卡时间是否已过期
				$last_batch = $result_yesterday_batch[count($result_yesterday_batch)-1];
				$end_bool = $this->check_last_sign_time_end($last_batch);
				if(!$end_bool){
					Log::record('昨天的夜班还没结束', Log::ALERT);
					//如果今天也上班
					if($today_work_bool){
						Log::record('今天也上班，判断昨天最晚签退时间是否与今天最早签到时间是否冲突', Log::ALERT);
						//判断昨天最晚签退时间是否与今天最早签到时间是否冲突
						if($this->_validate_batch_range($last_batch, $result_today_batch[0])){
							Log::record('昨天最晚签退时间与今天最早签到时间冲突,使用今天的排班', Log::ALERT);
							$res['type'] = $today_bool ? SignScheduleModel::REST_AND_WORK_STATUS : SignScheduleModel::SCHEDULE_WORK_STATUS;
							$res['later_schedule_id'] = $today_bool ? $result_today_schedule_id : '';
							foreach ($result_today_batch as $rb) {
								//$res['type'] = $today_bool ? SignScheduleModel::REST_AND_WORK_STATUS : $rb['type'];
								$name[] = $rb['name'];
								$sbid[] = $rb['sbid'];
							}

							$res['schedule_type'] = SignScheduleModel::TODAY;
							$res['batch_index'] = $today_index;
							$res['batch_id'] = implode(',', $sbid);
							$res['name'] = implode('、', $name);

							$this->__if_today_signed($today_signed_bool, $signed_cdid, $_sd['cd_id'], $m_uid, rgmdate(NOW_TIME,'Y-m-d'));
							if($today_signed_bool){
								Log::record('今天有签到记录,部门----'.$dept_array[$signed_cdid], Log::ALERT);
								unset($result);
								$result[] = $res;
								return $result;
							}

							$result[] = $res;
							continue;
						}
					}

					$res['type'] = $yesterday_bool ? SignScheduleModel::REST_AND_WORK_STATUS : SignScheduleModel::SCHEDULE_WORK_STATUS;
					$res['later_schedule_id'] = $yesterday_bool ? $result_yesterday_schedule_id : '';
					foreach ($result_yesterday_batch as $rb) {
						$name[] = $rb['name'];
						$sbid[] = $rb['sbid'];
					}
					$res['batch_id'] = implode(',', $sbid);
					$res['name'] = implode('、', $name);
					$res['batch_index'] = $yesterday_index;
					$res['schedule_type'] = SignScheduleModel::YESTERDAY;

					$this->__if_today_signed($today_signed_bool, $signed_cdid, $_sd['cd_id'], $m_uid, rgmdate($yesterday_timestamp,'Y-m-d'));
					if($today_signed_bool){
						Log::record('昨天有签到记录,部门----'.$dept_array[$signed_cdid], Log::ALERT);
						unset($result);
						$result[] = $res;
						return $result;
					}

					$result[] = $res;
					continue;
				}
			}


			if(!$today_work_bool){
				Log::record('今天不上班', Log::ALERT);
				$res['type'] = SignScheduleModel::REST_WORK_STATUS;
				$res['name'] = '休息';
				$res['batch_index'] = $today_index;
				$res['batch_id'] = '';
				$res['later_schedule_id'] = '';
				$res['schedule_type'] = SignScheduleModel::TODAY;
				$result[] = $res;
				continue;
			}

			$res['type'] = $today_bool ? SignScheduleModel::REST_AND_WORK_STATUS : SignScheduleModel::SCHEDULE_WORK_STATUS;
			$res['later_schedule_id'] = $today_bool ? $result_today_schedule_id : '';
			foreach ($result_today_batch as $rb) {
				$name[] = $rb['name'];
				$sbid[] = $rb['sbid'];
			}

			$res['schedule_type'] = SignScheduleModel::TODAY;
			$res['batch_index'] = $today_index;
			$res['batch_id'] = implode(',', $sbid);
			$res['name'] = implode('、', $name);

			$this->__if_today_signed($today_signed_bool, $signed_cdid, $_sd['cd_id'], $m_uid, rgmdate(NOW_TIME,'Y-m-d'));
			if($today_signed_bool){
				Log::record('今天有签到记录,部门----'.$dept_array[$signed_cdid], Log::ALERT);
				unset($result);
				$result[] = $res;
				return $result;
			}

			$result[] = $res;

		}

		//Log::record('查询部门和班次结束');
		//Log::record('$result----'.var_export($result,true));
		return $result;
	}

	/**
	 * 获取用户部门排班，所在部门没有排班，找上级部门，一直找到默认排班
	 * @param $m_uid
	 * @param $schedule_data
	 * @param $dept_array
	 */
	protected function _get_user_schedule($m_uid, &$schedule_data, &$dept_array){

		//获取用户当前所属部门
		$departments = $this->_list_user_departments_by_uid($m_uid);

		$schedule_data = array();
		$dept_array = array();
		foreach($departments as $dept_id){
			$cdid_array = array($dept_id);
			//当前部门有没有排班
			$schedule = $this->list_schedules_by_cdids($dept_id);
			if(!empty($schedule)){
				$this->formate_department_name($cdid_array, $dept_array);
				$schedule[0]['cd_id'] = $dept_id;
				$schedule[0]['cd_name'] = $dept_array[$dept_id];
				$schedule_data[] = $schedule[0];
				continue;
			}

			//当前部门的所有上级部门
			$parent_dept_ids = $this->list_parent_departments_by_cdid($dept_id);
			//查询所有上级部门的排班
			$schedule_list = $this->list_schedules_by_cdids($parent_dept_ids);
			if(!empty($schedule_list)){

				$this->formate_department_name($cdid_array, $dept_array);
				$schedule_list[0]['cd_id'] = $dept_id;
				$schedule_list[0]['cd_name'] = $dept_array[$dept_id];
				$schedule_data[] = $schedule_list[0];
				continue;
			}

			//获取默认的全公司排班
			if($this->__if_all_commpany_on($defult_schedule)){
				$this->formate_department_name($cdid_array, $dept_array);
				$defult_schedule['cd_id'] = $dept_id;
				$defult_schedule['cd_name'] = $dept_array[$dept_id];
				$schedule_data[] = $defult_schedule;

			}

		}

	}

	/**
	 * 是否开启休息日允许考勤
	 * @return bool true-开启 false-关闭
	 */
	private function __if_rest_day_sign_on(){

		$cache = &Cache::instance();
		$cache_setting = $cache->get('Sign.setting');
		$rest_day_sign = $cache_setting['rest_day_sign'];

		if(empty($rest_day_sign)){
			return false;
		}

		//开启
		if($rest_day_sign ==  SignSettingModel::REST_DAY_SIGN){
			return true;
		}

		return false;
	}

	/**
	 * 根据具体时间找到当天的排班数据
	 * @param $timestamp 要找的当天时间戳
	 * @param $schedule 排班数据
	 */
	public function find_schedule_batch_by_date(&$index, &$batch_array, $timestamp, $schedule){

		//Log::record('根据具体时间找到当天的排班数据--------'.rgmdate($timestamp, 'Y-m-d H:i:s'));

		//找到具体第几天 参数：排班循环周期类型 天、周、月; 排班开始时间; 周期数（1-7，只有周期类型为天时才有效）; 要找具体天的时间戳
		$which_day = $this->find_today_batch_index($schedule['cycle_unit'], $schedule['schedule_begin_time'], $schedule['cycle_num'] , $timestamp);
		//Log::record('找到哪天-------' . $which_day);

		//下标值
		$index = $which_day - 1;

		//每一天的排班班次数据
		$schedule_everyday_detail = unserialize($schedule['schedule_everyday_detail']);
		//Log::record('每天的排班班次：'.var_export($schedule_everyday_detail,true));

		//是否超过下标
		if (count($schedule_everyday_detail) < $index) {
			Log::record('数组越界');
			E('_ERR_SIGN_SCHEDULE_NOEXIST_ERROR');
			return false;
		}

		//取出要使用的排班班次数据
		$batch_array = $schedule_everyday_detail[$index];
		//Log::record('取出要使用的排班班次数据----'.var_export($batch_array,true));
		$sign_batch_model = D('Sign/SignBatch');
		foreach($batch_array as &$_ba){
			$tmp = $sign_batch_model->get($_ba['id']);
			$_ba = $tmp[0];
		}

	}

	/**
	 * 检查是否真的要上班
	 * @param $schedule_obj 排班数据
	 * @param $rest_day_sign_bool 休息日是否允许考勤
	 * @param $m_uid 用户id
	 * @return bool true-上班，false-不用上班
	 */
	public function check_department_really_work(&$result_batch, &$bool=false, &$result_schedule_id, $batch_array, $schedule_obj, $rest_day_sign_bool, $m_uid){

		$batch_id_array = array_column($batch_array, 'sbid');
		//Log::record('检查是否真的要上班batch_id_array-----'.var_export($batch_id_array,true));
		//如果今天上班
		if (!empty($batch_id_array)) {
			//不在增加休息日里
			if(!$this->_today_in_remove_day($schedule_obj['remove_day'])){
				$result_batch = $batch_array;
				//Log::record('不在增加休息日里----'.var_export($result_batch,true));
				return true;
			}

			//在增加休息日里， 但是如果开了休息日允许签到，并且能够查询到最近一次签到的班次, 你还是要上班逃不了的孩子
			if ($rest_day_sign_bool && $this->_list_later_batch($m_uid, $schedule_obj['cd_id'], $result_batch,$result_schedule_id)) {
				$bool = true;
				return true;
			}

			return false;
		}


		//不上班，但在增加上班日里，或者开启了休息日允许考勤
		if ($this->_check_in_add_work_day($schedule_obj['add_work_day']) || $rest_day_sign_bool) {
			Log::record('不上班，但在增加上班日里，或者开启了休息日允许考勤----');
			//能够查询到最近一次签到的班次
			if ($this->_list_later_batch($m_uid, $schedule_obj['cd_id'], $result_batch, $result_schedule_id)) {
				$bool = true;
				return true;
			}
		}

		return false;
	}

	/**
	 * 昨天最后一个班次的最晚签退时间是否已过期
	 * @param $batch_obj
	 * @return bool true-已过期 false-没过期
	 */
	public function check_last_sign_time_end($batch_obj){

		$sign_end_range = $batch_obj['work_end'];

		//如果是常规班次
		if($batch_obj['type'] == SignBatchModel::BTACH_COMMON_TYPE){
			$sign_end_range = $sign_end_range + $batch_obj['sign_end_range'];
		}

		//Log::record('今天最后一个班次最早打卡时间：'.rgmdate($batch_obj['work_begin'], 'Y-m-d H:i:s'));
		//Log::record('今天最后一个班次最晚打卡时间：'.rgmdate($sign_end_range, 'Y-m-d H:i:s'));

		//次日
		if(differ_days(rgmdate($batch_obj['work_begin'], 'Y-m-d'), rgmdate($sign_end_range, 'Y-m-d')) > 0){
			$hi = rgmdate($sign_end_range, 'H:i');
			$now_ymd = rgmdate(NOW_TIME, 'Y-m-d');
			$sign_end_range = rstrtotime($now_ymd . ' ' . $hi);
			if($sign_end_range < NOW_TIME){
				return true;
			}else{
				return false;
			}
		}

		return true;
	}

	/**
	 * 验证昨天最晚签退时间是否与今天最早签到时间冲突
	 * @param $yesterday_batch
	 * @param $today_batch
	 * @return bool
	 */
	protected function _validate_batch_range($yesterday_batch, $today_batch){

		$yesterday_end_range = $yesterday_batch['work_end'];
		$today_start_ragne = $today_batch['work_begin'];
		//如果是常规班次
		if($yesterday_batch['type'] == SignBatchModel::BTACH_COMMON_TYPE){
			$yesterday_end_range = $yesterday_end_range + $yesterday_batch['sign_end_range'];
		}
		if($today_batch['type'] == SignBatchModel::BTACH_COMMON_TYPE){
			$today_start_ragne = $today_start_ragne - $today_batch['sign_start_range'];
		}

		$ymd = rgmdate(NOW_TIME, 'Y-m-d');
		$yesterday_end_range = rstrtotime($ymd . ' ' . rgmdate($yesterday_end_range, 'H:i'));
		$today_start_ragne = rstrtotime($ymd . ' ' . rgmdate($today_start_ragne, 'H:i'));

		//时间没有冲突
		if($yesterday_end_range < $today_start_ragne){
			return false;
		}

		return true;

	}

	/**
	 * 今天是否已有签过记录
	 * @param $today_signed_bool
	 * @param $cdid_array
	 * @param $member_dept_array
	 * @param $m_uid
	 */
	private function __if_today_signed(&$today_signed_bool,&$cdid, $member_cdid, $m_uid, $date){

		$today_signed_bool = false;

		Log::record('查询用户部门是否有签到记录-----'.$member_cdid);
		Log::record('具体日期------------'.$date);

		//今天已经签过到
		if ($this->_check_dept_signed($m_uid, $member_cdid, $date)) {
			$cdid = $member_cdid;
			$today_signed_bool = true;
			Log::record('有签到记录cd_id---'.$cdid);
		}

		//如果今天没有签过到
		if (empty($cdid)) {
			$cdid = $member_cdid;
			Log::record('没有签到记录');
		}

	}
}