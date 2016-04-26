<?php
/**
 * SignCrontabService.class.php
 * $author$
 */

namespace UcRpc\Service;
use Common\Common\Cache;
use Common\Common\Wxqy\Service;
use Common\Common\Department;
use Common\Common\WxqyMsg;
use Sign\Model\SignScheduleModel;
use Think\Log;

class SignCrontabService extends AbstractService {


    //发送范围：全公司
    const ALL_COMPANY = 0;

    //考勤类型：上班
    const SIGN_TYPE_ON = 1;

    //考勤类型：下班
    const SIGN_TYPE_OFF = 0;

    //考勤签到消息标题
    const SING_MSG_TITLE_ON = '签到提醒';
    //考勤签退消息标题
    const SING_MSG_TITLE_OFF = '签退提醒';

    private $common_setting_cache = array();

    private $batch_model = null;

    private $now_timestamp = 0;

    private $title = '';

    private $content = '';

    private $sign_type = 1;

	private $batch_name = '';

	// 构造方法
	public function __construct() {

		parent::__construct();

        $this->now_timestamp = NOW_TIME;

        $cache = &Cache::instance();
        $setting = $cache->get('Common.setting');
        $this->common_setting_cache = $setting;
        $this->batch_model = D('Sign/SignBatch');

	}

	/**
	 * 考勤提醒
	 * @param $params
	 */
    public function send_sign($params){

        Log::record('send_sign_msg begin');

        // 读取插件信息
        $model_plugin = D('Common/CommonPlugin');
        $plugin = $model_plugin->get_by_identifier('sign');
        // 如果 agentid 为空
        if (empty($plugin['cp_agentid'])) {
            return true;
        }

        // 更新 pluginid, agentid 配置
        cfg('PLUGIN_ID', $plugin['cp_pluginid']);
        cfg('AGENT_ID', $plugin['cp_agentid']);

		if(empty($params['batch_id'])){
			Log::record('签到/签退消息推送错误：班次ID参数为空：-------' . var_export($params, true));
			return false;
		}

		//查询班次详情
		$batch_data = $this->batch_model->get($params['batch_id']);
		if(empty($batch_data)){
			Log::record('签到/签退消息推送错误：查询不到班次记录：-------' . var_export($params, true));
			return false;
		}
		$this->batch_name = $batch_data[0]['name'];

        $schedule_model = D('Sign/SignSchedule');

        $conds = array(
            'enabled' => SignScheduleModel::SCHEDULE_DISABLE
        );

        //所有非禁用的排班记录(已启用、禁用中)
        $schedule_data = $schedule_model->list_schedule($conds);
        if(empty($schedule_data)){
            return true;
        }
        Log::record('$schedule_data-------' . var_export($schedule_data, true));

        //计划任务类型 1-上班；0-下班
        if('sign_on' == $params['type']){
            $this->sign_type = self::SIGN_TYPE_ON;
            $this->title = self::SING_MSG_TITLE_ON;
        }elseif('sign_off' == $params['type']){
            $this->sign_type = self::SIGN_TYPE_OFF;
            $this->title = self::SING_MSG_TITLE_OFF;
        }else{
            Log::record('签到/签退消息推送错误：考勤任务类型错误：-------' . var_export($params, true));
            return false;
        }

        //签到/签退消息内容
        $this->content = $params['content'];

        // 在提醒记录表里面获取该天已经发送的消息提醒
        $sent_today = $this->get_sent_today();

        //发送范围 true-全公司/false-其它部门
        $receive_all_bool = false;

		//全部门的排班数据
		$defult_schedule_data = array();

        //今天要发送的数据
        $send_today = array();

        foreach($schedule_data as $_val){

			//排班还没开始
			$schedule_begin_time = rstrtotime(rgmdate($_val['schedule_begin_time'],'Y-m-d') . ' 00:00:00');
			if(NOW_TIME < $schedule_begin_time){
				continue;
			}
			//排班已过期
			if(!empty($dt['schedule_end_time'])){
				$schedule_end_time = rstrtotime(rgmdate($_val['schedule_end_time'],'Y-m-d') . ' 23:59:59');
				if(NOW_TIME > $schedule_end_time){
					continue;
				}
			}

            //排班状态: 1-已排班; 2-休息
            $schedule_status = SignScheduleModel::SCHEDULE_WORK_STATUS;

            //部门ID
            $cd_id = $_val['cd_id'];

            //计算今天使用第几天的排班班次
            $today = $this->__find_today_batch_index($_val['cycle_unit'], $_val['schedule_begin_time'],$_val['cycle_num']);

            if($today < 0){
                Log::record('签到/签退消息推送错误：没有找到今天的排班班次,今天使用第(?)天的排班：-------' . $today);
                continue;
            }

            //减去1得到下标值
            $today_index = $today-1;

            //每天的排班班次信息
            $schedule_everyday_detail = unserialize($_val['schedule_everyday_detail']);
            if(count($schedule_everyday_detail) < $today_index){
                Log::record('签到/签退消息推送错误：排班班次数组越界，数组下标：-------' . $today);
                continue;
            }

            //取出今天的排班班次数据
            $today_batch_array = $schedule_everyday_detail[$today_index];
            $today_batch_id_array = array_column($today_batch_array,'id');

            //今天休息
            if(empty($today_batch_id_array)){
                $schedule_status = SignScheduleModel::REST_WORK_STATUS;
                //检查是否在节假日增加上班日期里
                if(!$this->__check_in_add_work_day($_val['add_work_day'])){
                    continue;
                }
            }else{
                //排班没有排这个班次
                if(!in_array($params['batch_id'], $today_batch_id_array)){
                    //Log::record('今天排班没有排这个班次：-------batch_id：' . $params['batch_id'] . ',今天排班：' . var_export($today_batch_array, true));
                    continue;
                }
				//在节假日里，休息
				if($this->__today_in_remove_day($_val['remove_day'])){
					Log::record('今天在节假日里');
					continue;
				}
            }

			//如果是全公司
			if($cd_id == self::ALL_COMPANY ){
				$defult_schedule_data[] = array(
					'cd_id' => $cd_id,
					'cd_name' => $_val['cd_name'],
					'schedule_id' => $_val['id'],
					'schedule_type' => $schedule_status,
					'batch_id' => $params['batch_id'],
					'batch_index' => $today_index
				);

				continue;
			}

            $send_today[] = array(
                'cd_id' => $cd_id,
                'cd_name' => $_val['cd_name'],
                'schedule_id' => $_val['id'],
                'schedule_type' => $schedule_status,
                'batch_id' => $params['batch_id'],
                'batch_index' => $today_index
            );

        }

		//如果默认全公司排班启用着
		if(!empty($defult_schedule_data)){
			Log::record('$defult_schedule_data.................'.var_export($defult_schedule_data,true));

			//过滤今天已经发送过的班次
			$this->__filter_scheduel_batch_sent($defult_schedule_data, $sent_today);

			if(!empty($defult_schedule_data)){

				//获取所有的部门ID
				$this->__list_cdid_by_cache($defult_cdids_array);

				//已有排班的部门
				$haved_schedule_cdis_array = array_column($schedule_data,'cd_id');

				Log::record('$haved_schedule_cdis_array'.var_export($haved_schedule_cdis_array,true));

				Log::record('$defult_cdids_array----' . var_export($defult_cdids_array, true));

				//过滤掉已有排班的部门
				$this->__filter_schedule_department($defult_cdids_array, $haved_schedule_cdis_array);

				Log::record('filter $_defult_cdids_array----' . var_export($defult_cdids_array, true));

				if(empty($defult_cdids_array)){
					Log::record('$defult_cdids_array is null');
					return true;
				}

				//推送消息
				$this->__send_msg($defult_cdids_array);

				//消息提醒记录入库
				$this->_insert_sign_alert($defult_schedule_data, $this->sign_type);
			}

		}else{

			//过滤今天已经发送过的班次
			$this->__filter_scheduel_batch_sent($send_today, $sent_today);

			if(empty($send_today)){
				Log::record('$send_today is null');
				return true;
			}

			$cdis_array = array_column($send_today, 'cd_id');

			//推送消息
			$this->__send_msg($cdis_array);

			//消息提醒记录入库
			$this->_insert_sign_alert($send_today, $this->sign_type);
		}

        Log::record('send_sign_msg end');

    }



	/**
	 * 获取部门缓存数据
	 * @param $defult_dept_array
	 */
	private function __list_cdid_by_cache(&$defult_dept_array){
		// 获取部门缓存数据
		$cache = &Cache::instance();
		$cache_departments = $cache->get('Common.department');

		foreach ($cache_departments as $_dp) {
			$defult_dept_array[] = $_dp['cd_id'];
		}
	}

	/**
	 * 过滤部门已经有排班的
	 * @param $defult_dept_array
	 * @param $send_today_array
	 */
	private function __filter_schedule_department(&$defult_dept_array, $send_today_array){
		// 获取部门缓存数据
		$cache = &Cache::instance();
		$cache_departments = $cache->get('Common.department');
		$dept_service = Department::instance();
		$all_child_array = array();

		foreach ($cache_departments as $_k => $_dp) {
			if($_dp['cd_upid'] == 0){
				unset($cache_departments[$_k]);
				continue;
			}
			if (in_array($_dp['cd_id'], $send_today_array)) {
				$child_array = $dept_service->list_childrens_by_cdid($_dp['cd_id'], true);
				$all_child_array = array_merge($all_child_array, $child_array);
			}

		}

		$cache_cdids = array_column($cache_departments, 'cd_id');

		$defult_dept_array = array_diff($cache_cdids,$all_child_array);


	}

	/**
	 * 今天是否在节假日里
	 * @param $remove_work_day
	 * @return bool
	 */
	private function __today_in_remove_day($remove_work_day){

		if(empty($remove_work_day)){
			return false;
		}

		//是否在节假日里
		$remove_day = unserialize($remove_work_day);
		//法定节假日
		$public_day = $remove_day['public'];
		//用户自定义节假日
		$user_day = $remove_day['user'];
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
     * 过滤重复发送数据
     * @param $send_today_array 今天需要发送的数据
     * @param $sent_today_array 今天已经发送过的数据
     */
    private function __filter_scheduel_batch_sent(&$send_today_array, $sent_today_array){
		//Log::record('$send_today_array--------------'.var_export($send_today_array,true));
		//Log::record('$sent_today_array--------------'.var_export($sent_today_array,true));
        foreach($sent_today_array as $_sent){
            foreach($send_today_array as $_key => $_send){
                //排班ID
//                if($_send['schedule_id'] != $_sent['schedule_id']){
//                    continue;
//                }
                //班次ID、类型(上班/下班)
                if(($_send['batch_id'] == $_sent['batch_id']) && ($this->sign_type == $_sent['type'])){
                    unset($send_today_array[$_key]);
                }
            }
        }
    }

    /**
     * 检查是否在节假日增加上班日期里
     * @param $add_work_day
     * @return bool
     */
    private function __check_in_add_work_day($add_work_day){
		if(empty($add_work_day)){
			return false;
		}
		$add_work_day = unserialize($add_work_day);
        foreach($add_work_day as $_awd){
            $_st = rstrtotime($_awd['startTime']) . ' 00:00:00';
            $_et = rstrtotime($_awd['endTime']) . ' 23:59:59';

            if($this->now_timestamp >= $_st && $this->now_timestamp <= $_et){
                return true;
            }
        }

        return false;
    }

    /**
     * 获取今天已经发过的签到的数据
     * @return mixed
     */
    public function get_sent_today() {

        $alert = D('Sign/SignAlert');

        return $alert->list_by_on($this->sign_type);
    }

    /**
     * 找到今天是第几天的班次
     * @param $type 1-天；2-周；3-月
     * @param $date 排班开始时间
     * @param $cycle_num $type为1时，代表周期数1-7天
     * @return int|string
     */
    private function __find_today_batch_index($type, $date, $cycle_num){
        $result = -1;
        if($type == SignScheduleModel::CYCLE_UNIT_DAY){
            //排班轮询第一天的日期
            $start_date = rgmdate($date, 'Ymd');
            //当前签到日期
            $now_date = rgmdate(NOW_TIME, 'Ymd');
            $result = 1;
            if($start_date != $now_date){
                $i = ((NOW_TIME - $date) / 86400 % $cycle_num) + 1;
                $result = $i;
            }
        }elseif($type == SignScheduleModel::CYCLE_UNIT_WEEK){
			$week_array=array(7,1,2,3,4,5,6);
			//$result = $week_array[date("w")];
			$result = $week_array[rgmdate(NOW_TIME, 'w')];
        }else{
            //$result = intval(date("d"));
			$result = rgmdate(NOW_TIME,"j");
        }

        return $result;
    }

	/**
	 * 推送消息
	 * @param $dept_array
	 * @return bool
	 */
	private function __send_msg($dept_array) {

		//生成公司考勤URL
		$url = str_ireplace(
			array('{domain_url}', '{pluginid}'),
			array(cfg('PROTOCAL').$this->common_setting_cache['domain'], cfg('PLUGIN_ID') ? cfg('PLUGIN_ID') : ''),
			'{domain_url}/frontend/sign/index/?pluginid={pluginid}'
		);

		$wxqyMsg = WxqyMsg::instance();

		//消息内容
		$desc = "\n" . $this->content;

		$result = $wxqyMsg->send_news($this->title . ' ' . $this->batch_name, $desc, $url, '', $dept_array, '', cfg('AGENT_ID'), cfg('PLUGIN_ID'));

		Log::record($this->title . '推送结束---推送结果：' . var_export($result, true));

		return true;
	}

    /**
     * 插入考勤消息提醒记录表
     * @param $data
     * @param $sign_type  1-上班；0-下班
     * @return bool
     */
    protected function _insert_sign_alert($data,$sign_type){
		if(empty($data)){
			return true;
		}
        $sign_alert = D('Sign/SignAlert');
        foreach($data as $_dt){
            $tmp_data[] = array(
                'batch_id' => $_dt['batch_id'],
                'schedule_id' => $_dt['schedule_id'],
                'alert_time' => NOW_TIME,
                'type' => $sign_type,
                'status' => 1,
                'created' => NOW_TIME,
            );
        }
		Log::record('insert alert data:' . var_export($tmp_data, true));
        $sign_alert->insert_all($tmp_data);
        unset($tmp_data);
        return true;
    }

}
