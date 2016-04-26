<?php
/**
 * SignRecordService.class.php
 * $author$
 */

namespace Sign\Service;
use Common\Common\Cache;
use Think\Log;

class SignBatchService extends AbstractService {

	const ALLOW_SIGN = 1; // 允许签到
	const NOT_ALLOW_SIGN = 2; // 不允许签到
	const WORK_ON_AND_OFF = 3; // 上班和下班
	const WORK_ON = 1; // 上班
	const WORK_OFF = 2; // 下班

	// 构造方法
	public function __construct() {
		$this->_d = D("Sign/SignBatch");
		parent::__construct();

	}

	/**
	 * 根据班次id获取班次信息
	 * @param unknown $sbid
	 * @return Ambigous
	 */
	public function get($sbid) {
		return $this->_d->get($sbid);
	}

	/**
	 * 获取关联的班次信息
	 * @param array $upbalist
	 * @return mixed
	 */
	public function list_by_conds($upbalist) {
		
		return $this->_d->list_by_condition($upbalist);
	}
	/**
	 * 根据条件查询
	 * @param unknown $condition
	 */
	public function list_by_conditions($condition){
		
		return $this->_d->list_by_conds($condition);
	}
	/**
	 * 获取开启的班次
	 * @return mixed
	 */
	public function list_by_enable_cond() {
		return $this->_d->list_by_enable_cond();
	}
	
	/**
	 * 获取当前班次的班次名称
	 * @param $sbid 班次列表
	 * @return mixed
	 */
	public function batch_user_in_department(&$sbid) {

		// 颠倒 班次ID 和 部门ID
		$new = array_flip($sbid);
		// 所有班次信息
		$batchlist = $this->list_by_enable_cond();
		// 获取班次名称 替换掉部门ID 成班次名称
		foreach ($new as $_k => &$_v) {
			foreach ($batchlist as $key => $val) {
				if ($val['sbid'] == $_k) {
					$_v = $val['name'];
				}
			}
		}
		$sbid = $new;

		return true;
	}

	/**
	 * 获取班次信息
	 * @return mixed
	 */
	public function get_batch_info_for_index() {

		// 班次ID
		$batchid = I('get.batchid');
		if (empty($batchid)) {
			E('_ERR_MISS_PARAMETER_BATCHID');
		}

		//用户选择了班次
		$info = $this->list_by_conds($batchid);

		return $info[0];
	}

	/**
	 * 计算其他数据属性
	 * @param $info 班次信息
	 * @param $btime 起始时间
	 * @param $etime 结束时间
	 * @param $allow_sign 是否允许签到
	 * @param $sb_set 当前签到的类别
	 * @return bool
	 */
	public function first_property($info, &$btime, &$etime, &$allow_sign, &$sb_set) {

		$wo_days = unserialize($info['work_days']);
		$current_week = rgmdate(NOW_TIME, 'w');
		$allow_sign = in_array($current_week, $wo_days) ? self::ALLOW_SIGN : self::NOT_ALLOW_SIGN;

		// 起始时间和结束时间
		$ymd = rgmdate(NOW_TIME, 'Y-m-d');
		// 开始时间为工作时间前*小时
		$btime = rstrtotime($ymd . ' ' . $this->__formattime($info['work_begin'])) - 3600 * cfg('add_begin_time');
		// 结束时间为工作时间后*小时
		$work_end = $this->__formattime($info['work_end']);
		$work_e = substr($work_end, 0, 2);

		if ($work_e - 24 > 0) {
			$etime = $this->__totime($ymd, $work_end) + 3600 * cfg('etime');
		} else {
			$etime = rstrtotime($ymd . ' ' . $this->__formattime($info['work_end'])) + 3600 * cfg('etime');
		}

		// 判断打卡设置
		$sb_set = $info['sb_set'];

		return true;
	}

	/**
	 * 格式时间
	 * @param unknown $ymd
	 * @param unknown $num
	 * @return number
	 */
	private function __totime($ymd, $num) {

		$time = $num;
		$h = substr($time, 0, 2);
		//2015-08-15 25:23;
		if ($h - 24 > 0) {
			$diff = $h - 24;
			$m = substr($time, 3, 2);
			$formattime = strtotime('+1 day', strtotime($ymd)) + $diff * 3600 + $m * 60 - 8 * 3600;

		}

		return $formattime;
	}

	/**
	 * 格式数字为时间
	 * @param $num
	 * @return string
	 */
	private function __formattime($num) {

		if (strlen($num) == 0) {
			$time = '00:00';

			return $time;
		} elseif (strlen($num) == 1) {
			$time = '00:0' . $num;

			return $time;
		} elseif (strlen($num) == 2) {
			$time = '00:' . $num;

			return $time;
		} elseif (strlen($num) == 3) {
			$hour = substr($num, 0, 1);
			$min = substr($num, 1, 2);
			$time = '0' . $hour . ':' . $min;

			return $time;
		} else {
			$hour = substr($num, 0, 2);
			$min = substr($num, 2, 2);
			$time = $hour . ':' . $min;

			return $time;
		}
	}

	/**
	 * 获取上下班卡
	 * @param $records
	 * @param $sb_set
	 * @return mixed
	 */
	public function up_down_work($records, $sb_set) {

		foreach ($records as $r) {
			if (self::WORK_ON == $r ['sr_type'] && in_array($sb_set, array(1, 3))) {
				$on_signtime_hi = $r ['_signtime_hi'];
				$work_on = $r;
			} elseif (self::WORK_OFF == $r ['sr_type'] && in_array($sb_set, array(2, 3))) {
				$off_signtime_hi = $r ['_signtime_hi'];
				$work_off = $r;
			}
		}

		$up_down_data['on_signtime_hi'] = isset($on_signtime_hi) ? $on_signtime_hi : array();
		$up_down_data['work_on'] = isset($work_on) ? $work_on : array();
		$up_down_data['off_signtime_hi'] = isset($off_signtime_hi) ? $off_signtime_hi : array();
		$up_down_data['work_off'] = isset($work_off) ? $work_off : array();

		return $up_down_data;
	}

	/**
	 * 判断那种卡未打, 设置对应的时间
	 * @param $work_on 签到记录
	 * @param $sb_set 当前签到还是签退
	 * @param $info 班次信息
	 * @param $on_signtime_hi 班次开始时间
	 * @param $off_signtime_hi 班次结束时间
	 * @return mixed
	 */
	public function is_no_get($work_on, $sb_set, $info, $on_signtime_hi, $off_signtime_hi) {

		// 获取 班次 开始或者结束时间
		if (empty ($work_on) && in_array($sb_set, array(1, 3))) {
			$on_signtime_hi = rgmdate(NOW_TIME, 'H:i:s');
		} elseif (empty ($work_off) && in_array($sb_set, array(2, 3))) {
			$off_signtime_hi = rgmdate(NOW_TIME, 'H:i:s');
		}

		// 取当前时间的月/日/周
		$p_set = array();
		$p_set ['work_begin_hi'] = $this->__formattime($info ['work_begin']);
		$tmp_end = $this->__formattime($info['work_end']);

		if (substr($tmp_end, 0, 2) >= 24) {
			$stime = (substr($tmp_end, 0, 2) - 24) . substr($tmp_end, 3, 2);
			$tmp_end = '次日' . $this->__formattime($stime);
		}
		$p_set['work_end_hi'] = $tmp_end;

		$is_no_get['on_signtime_hi'] = $on_signtime_hi;
		$is_no_get['off_signtime_hi'] = $off_signtime_hi;
		$is_no_get['p_set'] = $p_set;

		return $is_no_get;
	}

	/**
	 * 判断签到次数计算传过去的sr_id
	 * @param $work_on 签到记录
	 * @param $work_off 签退记录
	 * @return mixed
	 */
	public function qian_pass($work_on, $work_off) {

		if (!empty($work_on)) {
			if (empty($work_off)) {
				$detail = $work_on['sr_id'];
				$sr_id[] = $work_on['sr_id'];
			} elseif (!empty($work_off)) {
				$detail = $work_off['sr_id'];
				$sr_id = array($work_on['sr_id'], $work_off['sr_id']);
			}

		} elseif (!empty($work_off)) {
			$detail = $work_off['sr_id'];
			$sr_id[] = $work_off['sr_id'];
		} else {
			$detail = '';
			$sr_id = array();
		}

		$re_data['detail'] = $detail;
		$re_data['sr_id'] = $sr_id;

		return $re_data;
	}

	/**
	 * 分类备注
	 * @param       $sign_detail 备注记录
	 * @param array $work_on_detail 签到记录
	 * @param array $work_off_detail 签退记录
	 * @return bool
	 */
	public function remark_classify($sign_detail, &$work_on_detail = array(), &$work_off_detail = array()) {

		// 分类备注 上班和下班
		if (!empty($sign_detail)) {
			foreach ($sign_detail as $k => &$v) {
				$v['sd_created'] = rgmdate($v['sd_created'], 'H:i:s');
				if ($v['type'] == self::WORK_ON) {
					$work_on_detail[] = $sign_detail[$k];
				} elseif ($v['type'] == self::WORK_OFF) {
					$work_off_detail[] = $sign_detail[$k];
				}
			}
		}

		return true;
	}

	/**
	 * 备注方法
	 * @param $info 当前的班次信息
	 * @param $work_on 上班的签到记录
	 * @return array
	 */
	public function beizhu($info, $work_on) {

		//判断当前该打的卡
		if ($info['sb_set'] == self::WORK_ON) {
			$sign_type = self::WORK_ON; // dd
		} elseif ($info['sb_set'] == self::WORK_OFF) {
			$sign_type = self::WORK_OFF;
		} elseif ($info['sb_set'] == self::WORK_ON_AND_OFF) {
			if (empty($work_on)) {
				$sign_type = self::WORK_ON;
			} else {
				$sign_type = self::WORK_OFF;
			}
		}

		$re_data = array(
			'sign_type' => $sign_type
		);

		return $re_data;
	}

	/**
	 * 获取当前班次的 部门名称
	 * @param $all_department
	 * @param $department
	 * @return mixed
	 */
	public function get_cdname_by_batchid($all_department, &$department) {

		// 获取部门对应的名称
		foreach ($all_department as $_k => $_v) {
			if ($_v['cd_id'] == $department) {
				$department_name = $_v['cd_name'];
			}
		}

		return $department_name;
	}


	/**
	 * [get_true_bc 返回可行的班次数据]
	 * @return [type] [返回可行的班次数据]
	 */
	public function get_true_bc() {
		
		return $this->_d->list_by_true_bc();
	}

	/**
	 * [get_true_bc 返回可行的班次数据 下班]
	 * @return [type] [返回可行的班次数据 下班]
	 */
	public function get_true_bc_off() {
		
		return $this->_d->list_by_true_bc_off();
	}

    /**
     * 模糊查询
     * @return
     */
    public function list_by_conds_for_like($conds, $page_option = null, $order_option = array()){
        return $this->_d->list_by_conds_for_like($conds, $page_option, $order_option);
    }

    public function count_by_conds_for_like($conds){
        return $this->_d->count_by_conds_for_like($conds);
    }

    /**
     * 新增计划任务
     * @param $taskid 计划任务ID
     * @param $runtime 执行时间点
     * @param $type 任务类型
     */
    public function add_task($taskid, $runtime, $type, $batch_id){

        Log::record("新增签到/签退计划任务开始" . $type, Log::ALERT);

        $cache = &Cache::instance();
        $setting = $cache->get('Common.setting');

        $client = &\Com\Rpc::phprpc(cfg('UCENTER_RPC_HOST') . '/OaRpc/Rpc/Crontab');

        //封装数据
        $params = array(
            'taskid' => $taskid,
            'domain' => $setting['domain'],
            'type' => $type,
            'ip' => '',
            'runtime' => $runtime,
            'endtime' => 0,
            'looptime' => 86400,
            'times' => 0,
            'runs' => 0,
            'params' => $batch_id
        );
        $result = $client->Add($params);

        Log::record("新增签到/签退计划任务结束，result-----" . $result, Log::ALERT);
    }

    /**
     * 更新计划任务
     * @param $taskid 计划任务ID
     * @param $runtime 执行时间点
     * @param $type 任务类型
     */
    public function update_task($taskid, $runtime, $type, $params){

        Log::record("更新签到/签退计划任务开始" . $type, Log::INFO);

        $cache = &Cache::instance();
        $setting = $cache->get('Common.setting');

        $client = &\Com\Rpc::phprpc(cfg('UCENTER_RPC_HOST') . '/OaRpc/Rpc/Crontab');

        //封装数据
        $params = array(
            'taskid' => $taskid,
            'domain' => $setting['domain'],
            'type' => $type,
            'ip' => '',
            'runtime' => $runtime,
            'endtime' => 0,
            'looptime' => 86400,
            'params' => $params
        );
        $result = $client->Update($params);

        Log::record("更新签到/签退计划任务结束，result-----" . $result, Log::INFO);
    }

    /**
     * 删除计划任务
     * @param $taskid 任务id
     * @param $type 任务类型
     * @return mixed
     */
    public function del_task($taskid, $type) {

        Log::record("删除签到/签退计划任务" . $type, Log::INFO);

        $cache = &Cache::instance();
        $setting = $cache->get('Common.setting');

        $client = &\Com\Rpc::phprpc(cfg('UCENTER_RPC_HOST') . '/OaRpc/Rpc/Crontab');

        return $client->Del_by_taskid_domain_type($taskid, $setting['domain'], $type);
    }
}
