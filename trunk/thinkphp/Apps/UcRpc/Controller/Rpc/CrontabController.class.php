<?php
/**
 * @Author: ppker
 * @Date:   2015-10-09 14:47:37
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-10-14 16:21:19
 */
namespace UcRpc\Controller\Rpc;

class CrontabController extends AbstractController {

	// 发送签到的提醒消息内容 [上班]
	protected $remind_data = array();
	// 发送签到的提醒消息内容 [下班]
	protected $remind_data_off = array();
	// 初始的某班次下的部门id
	protected $half_depart = array();

	protected $cache_department = array(); // 部门的缓存信息
	protected $send_list = array();

	public function Index() {

		return true;
	}

	/**
	 * 执行计划任务脚本
	 * @param array $types 任务类型
	 * @param array $params 请求的计划任务参数
	 */
	public function run($types, $params) {

		\Think\Log::record('types: ' . var_export($types, true));
		\Think\Log::record('params: ' . var_export($params, true));
		// 遍历所有任务
		foreach ($types as $_type) {
			switch ($_type) {
				case 'sign_on': // 签到
				case 'sign_off': // 签退
					$serv = D('UcRpc/SignCrontab', 'Service');
					$serv->send_sign($params);
					//$this->test();
					break;
				case 'subscribe':
					$serv = D('UcRpc/Subscribe', 'Service');
					$serv->update_subscribe();
					break;
				case 'blessRedpack':
					$serv = D('UcRpc/BlessingRedpack', 'Service');
					$serv->send_msg();
					break;
				case 'exam_start': // 考试开始提醒
					$serv = D('UcRpc/ExamCrontab', 'Service');
					$serv->notify_start($params);
					break;
				case 'exam_stop': // 结束提醒
					$serv = D('UcRpc/ExamCrontab', 'Service');
					$serv->notify_stop($params);
					break;
				case 'exam_over': // 结束交卷提醒
					$serv = D('UcRpc/ExamCrontab', 'Service');
					$serv->notify_over($params);
					break;
				default: break;
			}
		}

		return true;
	}

	public function test() {

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

		// 获取部门的缓存信息
		$cache = &\Common\Common\Cache::instance();
		$this->cache_department = $cache->get('Common.department');

		// 首先拿到可行的班次 数组
		$true_sbids = $this->get_true_bc('on'); // 上班
		$true_sbids_off = $this->get_true_bc('off'); // 下班

		// 在提醒记录表里面获取该天已经发送的消息提醒
		$send_on_today = $this->get_send_on('on');
		$send_off_today = $this->get_send_on('off');

		// 进行过滤
		$true_sbids = $this->strip_sbid($true_sbids, $send_on_today);
		$true_sbids_off = $this->strip_sbid($true_sbids_off, $send_off_today);

		if (empty($true_sbids) && empty($true_sbids_off)) {
			return;
		}

		//获取班次绑定的部门
		$serv_department = D('Sign/SignDepartment', 'Service');
		$sign_dep_on = array();
		$sign_dep_off = array();
		if(!empty($true_sbids)){
			$sign_dep_on = $serv_department->list_by_sbid($true_sbids);
		}
		if(!empty($true_sbids_off)){
			$sign_dep_off = $serv_department->list_by_sbid($true_sbids_off);
		}
		//获取班次信息
		$serv_batch = D('Sign/SignBatch', 'Service');
		$info_on = array();
		if(!empty($true_sbids)){
			$conds_bat_on['sbid IN (?)'] = $true_sbids;
			$info_on = $serv_batch->list_by_conds($conds_bat_on);
		}
		if(!empty($true_sbids_off)){
			$conds_bat_off['sbid IN (?)'] = $true_sbids_off;
			$info_off = $serv_batch->list_by_conds($conds_bat_off);
		}
		//格式部门数据
		if(!empty($sign_dep_on)){
			foreach($sign_dep_on as $val_on){
				$dep_list_on[$val_on['sbid']][] = $val_on['department'];
			}
		}
		if(!empty($sign_dep_off)){
			foreach($sign_dep_off as $val_off){
				$dep_list_off[$val_off['sbid']][] = $val_off['department'];
			}
		}

		//发消息
		if(!empty($dep_list_on)){
			$this->send_msg($dep_list_on, 'remind_on');
		}elseif(!empty($dep_list_off)){
			$this->send_msg($dep_list_off, 'remind_off');
		}
		\Think\Log::record(var_export($dep_list_on, true));
		/**
		// 在提醒记录表里面获取该天已经发送的消息提醒
		$send_on_today = $this->get_send_on('on');
		$send_off_today = $this->get_send_on('off');

		// 进行过滤
		$true_sbids = $this->strip_sbid($true_sbids, $send_on_today);
		$true_sbids_off = $this->strip_sbid($true_sbids_off, $send_off_today);

		if (empty($true_sbids) && empty($true_sbids_off)) {
			return;
		}

		if (!empty($true_sbids)) {
			$true_m_openids = $this->goto_run($true_sbids); // 上班
		} else {
			$true_m_openids = null;
		}
		if (!empty($true_sbids_off)) {
			$true_m_openids_off = $this->goto_run($true_sbids_off); // 下班
		} else {
			$true_m_openids_off = null;
		}
		// 获取此时上班已经签到的muid 还是以班次为基准的
		$over_qd_muid = $this->get_over_qd_muid($true_sbids);

		// 获取此时下班已经签到的muid 还是以班次为基准的
		$over_qd_muid_off = $this->get_over_qd_muid_off($true_sbids_off);

		// 对数据进行封装 过滤 [上班]
		foreach ($true_m_openids as $k => &$v) {
			foreach ($over_qd_muid[$k] as $ko => $vo) {
				foreach ($v as $auto_key => &$arr_uid) {
					if ($arr_uid['m_uid'] == $vo) {
						unset($true_m_openids[$k][$auto_key]);
					}
				}
			}
		}

		// 对数据进行封装 过滤 [下班]
		foreach ($true_m_openids_off as $k_off => &$v_al) {
			foreach ($over_qd_muid_off[$k_off] as $ko => $vo_val) {
				foreach ($v_al as $auto_key_off => &$a_uid) {
					if ($a_uid['m_uid'] == $vo_val) {
						unset($true_m_openids_off[$k_off][$auto_key_off]);
					}
				}
			}
		}

		// 进行发送消息提醒
		if ($true_m_openids) {
			$ok_send_on = $this->ok_send($true_m_openids, 'remind_on');
		} // 上班
		if ($true_m_openids_off) {
			$ok_send_off = $this->ok_send($true_m_openids_off, 'remind_off');
		} // 下班
		 */

	}

	/**
	 * 新发消息方法
	 * @param $send_dep 班次为键的部门数组
	 * @param $type 提醒类型
	 * @return bool
	 */
	public function send_msg($send_dep, $type){

		$sign_alert = D('Sign/SignAlert');
		$wxmsg = &\Common\Common\WxqyMsg::instance();
		//给每个部门发消息
		foreach($send_dep as $key => $val){
			if ($type == 'remind_off') {
				$content = $this->remind_data_off[$key][$type];
			} else {
				$content = $this->remind_data[$key][$type];
			}
			\Think\Log::record(var_export($content, true));
			\Think\Log::record(var_export($val, true));
			//所有要发消息的部门
			foreach($val as $department){
				$last_department[] = $department;
			}
			// 提醒记录表注入数据
			$a_type = $type == 'remind_on' ? 1 : 0;
			$tmp_data = array();
			$tmp_data = array(
				'batch_id' => $key,
				'alert_time' => NOW_TIME,
				'type' => $a_type,
				'status' => 1,
				'created' => NOW_TIME,
			);
			$alert_data[] = $tmp_data;

		}
		\Think\Log::record(var_export($last_department, true));
		//发消息
		$wxmsg->send_text($content, array(), $last_department, cfg('AGENT_ID'));
		//入库操作
		$sa_return = $sign_alert->insert_all($alert_data);

		return true;
	}
	/**
	 * 拿到初始化的可执行班次信息
	 * @param string 上班或下班
	 * @return array  返回的数据 班次数组
	 */
	public function get_true_bc($type) {
		$batch = D('Sign/SignBatch', 'Service');

		//取出所有班次信息
		$batch_list = $batch->list_all();
		$batchlist = array();
		foreach ($batch_list as &$_batch) {
			$batchlist[$_batch['sbid']] = $_batch;
		}
		$nowday = rgmdate(NOW_TIME, 'w');

		if ($type == "off") {
			$true_batch = $batch->get_true_bc_off();
			// 布局数据 签到消息的提醒
			foreach ($true_batch as $k => $v) {
				$this->remind_data_off[$v['sbid']]['remind_on'] = $v['remind_on'];
				$this->remind_data_off[$v['sbid']]['remind_off'] = $v['remind_off'];
			}
			// 需要发消息的上班的班次
			$true_sbids = array_column($true_batch, 'sbid');
			//非工作日不发消息
			if (!empty($true_sbids)) {
				foreach ($true_sbids as $_key => &$_work_day) {
					$work_arr = array();
					$work_arr = $batchlist[$_work_day]['work_days'];
					$workday = unserialize($work_arr);
					if (!in_array($nowday, $workday)) {
						unset($true_sbids[$_key]);
					}
				}
			}

			return $true_sbids;
		}

		$true_batch = $batch->get_true_bc();

		// 布局数据 签到消息的提醒
		foreach ($true_batch as $k => $v) {
			$this->remind_data[$v['sbid']]['remind_on'] = $v['remind_on'];
			$this->remind_data[$v['sbid']]['remind_off'] = $v['remind_off'];
		}

		// 需要发消息的上班的班次
		$true_sbids = array_column($true_batch, 'sbid');

		//非工作日不发消息
		if (!empty($true_sbids)) {
			foreach ($true_sbids as $_key => &$_work_day) {
				$work_arr = array();
				$work_arr = $batchlist[$_work_day]['work_days'];
				$workday = unserialize($work_arr);
				if (!in_array($nowday, $workday)) {
					unset($true_sbids[$_key]);
				}
			}
		}

		return $true_sbids;
	}

	/**
	 * [get_true_cdids 接着拿出各个班次符合的部门id]
	 * @param  [array] $true_sbids [传递的班次数据]
	 * @return [array]             [返回的数据]
	 */
	public function get_true_cdids($true_sbids) {

		$out_data = array();
		$sign_depart = D('Sign/SignDepartment');

		// 获取签到 班次部门表里面的所有数据
		$sign_batch_department = $sign_depart->list_all();

		foreach ($true_sbids as $k => $v) { // 循环班次
			foreach ($sign_batch_department as $key_sign => $value_sign) {
				if ($value_sign['sbid'] == $v) {
					$out_data[$v][] = $value_sign['department'];
				}
			}

			$this->half_depart = $out_data[$v];
			// 由于部门是分层级的 所有要进行一次递归处理

			foreach ($this->half_depart as $kh => $vh) { // 部门id 单个
				$this->digui($vh);  // 单个部门id
			}
			$out_data[$v] = $this->half_depart;
			$this->half_depart = array();
		}

		return $out_data;
	}

	/**
	 * 表里拿到所有的m_uid
	 * @param array $cdids 传递的部门数据
	 * @return array 返回的数据
	 */
	public function get_true_muids($cdids) {

		$serv_mdp = D('Common/MemberDepartment', 'Service');
		$departments = $serv_mdp->list_by_cdid($cdids);

		return array_column($departments, 'm_uid');
	}

	/**
	 * 班次为基准从member表里面再拿到那个openid
	 * @param array $uids 传递的数据 班次为基底的数据
	 * @return array 返回的数据
	 */
	public function get_true_openids($uids) {

		$serv_mem = D('Common/Member', 'Service');
		$members = $serv_mem->list_by_pks($uids);
		$cd_mem = array();
		if (!empty($members)) {
			foreach ($members as $_mem) {
				$tmp = array();
				$tmp['m_openid'] = $_mem['m_openid'];
				$tmp['m_uid'] = $_mem['m_uid'];
				$cd_mem[$_mem['cd_id']][] = $tmp;
			}
		}

		return $cd_mem;
	}

	/**
	 * [get_over_qd_muid 获取各个班次 上班已签过到的muid数据]
	 * @param  [array] $true_sbids [传递的班次数据]
	 * @return [array]             [返回的数据]
	 */
	public function get_over_qd_muid($true_sbids) {

		if (null == $true_sbids) {
			return;
		}
		$SignRecord = D('Sign/SignRecord', 'Service');
		$qd_muid = $SignRecord->get_over_qd_muid($true_sbids);

		return $qd_muid;
	}

	/**
	 * [get_over_qd_muid_off 获取各个班次 下班已签过到的muid数据]
	 * @param  [array] $true_sbids [传递的班次数据]
	 * @return [array]             [返回的数据]
	 */
	public function get_over_qd_muid_off($true_sbids) {

		if (null == $true_sbids) {
			return;
		}
		$SignRecord = D('Sign/SignRecord', 'Service');
		$qd_muid = $SignRecord->get_over_qd_muid_off($true_sbids);

		return $qd_muid;
	}

	/**
	 * [ok_send 进行发送消息的操作]
	 * @param  [array] $true_m_openids [待发送消息的openids]
	 * @param  [string] $type           [上下班类型]
	 * @return [array]                 [返回的值]
	 */
	public function ok_send($true_m_openids, $type) {

		$sign_alert = D('Sign/SignAlert');
		// 进行微信发送消息
		$serv_wxqy = &\Common\Common\Wxqy\Service::instance();
		foreach ($true_m_openids as $k => $v) {
			// 发送的消息内容
			if ($type == 'remind_off') {
				$content = $this->remind_data_off[$k][$type];
			} else {
				$content = $this->remind_data[$k][$type];
			}

			// 提醒记录表注入数据
			$a_type = $type == 'remind_on' ? 1 : 0;
			$tmp_data = array();
			$tmp_data = array(
				'batch_id' => $k,
				'alert_time' => NOW_TIME,
				'type' => $a_type,
				'status' => 1,
				'created' => NOW_TIME,
			);
			$alert_data[] = $tmp_data;

			foreach ($v as $uid) {
				$user_id = $uid['m_uid'];
				//已经发送过的人不发消息
				if (!in_array($user_id, $this->send_list)) {
					$serv_wxqy->post_text($content, cfg('AGENT_ID'), $uid['m_openid']);
					$this->send_list[] = $user_id;
				}
				\Think\Log::record(var_export($content, true));
				\Think\Log::record(var_export($uid['m_uid'], true));
			}
		}
		$sa_return = $sign_alert->insert_all($alert_data);

	}

	/**
	 * 继续执行主体函数流程
	 * @param array $sbids 传递的班次信息
	 * @return array 返回的数据
	 */
	public function goto_run($sbids) {

		// 接着拿出各个班次对应的部门id数据
		$cdids = $this->get_true_cdids($sbids);
		foreach ($cdids as $val) {
			foreach ($val as $cdid) {
				$cdid_list[] = $cdid;
			}
		}

		// 各个班次从member_department 表里拿到所有的m_uid
		$uids = $this->get_true_muids($cdid_list);
		if (empty($uids)) {
			return false;
		}

		// 已班次为基准 分别从member表里拿出openid 并组装数据
		$openids = $this->get_true_openids($uids);

		//匹配班次
		foreach ($cdids as $_key_sbid => $_cddep) {
			$tmp_uid = array();
			foreach ($openids as $key_cdid => $uid) {
				foreach ($uid as $m_uid) {
					if (in_array($key_cdid, $_cddep)) {
						$tmp_uid[] = $m_uid;
					}
				}
			}
			$openids_batch[$_key_sbid] = $tmp_uid;
		}

		return $openids_batch;
	}

	/**
	 * [get_send_on 获取已经发过提醒的数据]
	 * @param  [string] $type [传递的类型]
	 * @return [array]       [返回的数据]
	 */
	public function get_send_on($type) {

		$type_v = 'on' == $type ? 1 : 0;
		$alert = D('Sign/SignAlert');

		return $alert->list_by_on($type_v);
	}

	/**
	 * [strip_sbid 过滤多余的消息提醒的班次]
	 * @param  [array] $true_data [时间符合的班次数据]
	 * @param  [array] $send_data [记录表里面的班次数据]
	 * @return [array]            [返回真正的班次数据]
	 */
	public function strip_sbid($true_data, $send_data) {

		// 过滤多余的数据
		$send_sbid = array_column($send_data, 'batch_id');

		foreach ($true_data as $k => $v) {
			foreach ($send_sbid as $ks => $vs) {
				if ($v == $vs) {
					unset($true_data[$k]);
				}
			}
		}

		return $true_data;
	}

	/**
	 * [digui 获取的初始的部门IDs 数组, 进行递归操作]
	 * @param  [string] $upid [传递的部门id]
	 * @return [bool]       [真/假]
	 */
	public function digui($upid) {

		$sign_depart = D('Sign/SignDepartment');
		$department_data = $this->cache_department; // 部门数据 缓存
		$ext_upid = array();
		foreach ($department_data as $kde => $vde) {
			if ($vde['cd_upid'] == $upid) {
				$ext_upid[] = $vde['cd_id'];
			}
		}

		$has_upid = $ext_upid;
		if (!$has_upid) {
			return false;
		}

		// 拿到sign_department表里面的所有数据
		$data_sign_department = $sign_depart->list_all();

		foreach ($has_upid as $kh1 => $vh1) {

			// sign_department 表中通过 department_id 获取数据
			$re = 0;
			foreach ($data_sign_department as $key_data => $value_data) {
				if ($value_data['department'] == $vh1) {
					$re = 1;
				}
			}

			if (!$re) { // 没有班次
				// 过滤重复
				if (!in_array($vh1, $this->half_depart)) {
					// 堆栈到循环数组的后面，一级 一级的会继续循环，直到无下级为止
					array_push($this->half_depart, $vh1);
					$this->digui($vh1);
				}
			}
		}

		return true;
	}

}
