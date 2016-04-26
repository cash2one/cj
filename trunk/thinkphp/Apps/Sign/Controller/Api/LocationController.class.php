<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace Sign\Controller\Api;

class LocationController extends AbstractController {

	// 发送签到的提醒消息内容 [上班]
	protected $remind_data = array();
	// 发送签到的提醒消息内容 [下班]
	protected $remind_data_off = array();


	public function run() {

		// 首先拿到可行的班次 数组
		$true_sbids = $this->get_true_bc('on'); // 上班
		$true_sbids_off = $this->get_true_bc('off'); // 下班

		if (empty($true_sbids) && empty($true_sbids_off)) return;

		if (!empty($true_sbids)) {
			$true_m_openids = $this->goto_run($true_sbids); // 上班
		}
		if (!empty($true_sbids_off)) {
			$true_m_openids_off = $this->goto_run($true_sbids_off); // 下班

		}

		// 获取此时上班已经签到的muid 还是以班次为基准的
		$over_qd_muid = $this->get_over_qd_muid($true_sbids);

		// 获取此时下班已经签到的muid 还是以班次为基准的
		$over_qd_muid_off = $this->get_over_qd_muid_off($true_sbids);

		// 对数据进行封装 过滤 [上班]
		foreach ($true_m_openids as $k => &$v) {
			foreach ($over_qd_muid[$k] as $ko => $vo) {
				unset($v[$vo]);
			}

		}

		// 对数据进行封装 过滤 [下班]
		foreach ($true_m_openids_off as $k => &$v) {
			foreach ($over_qd_muid_off[$k] as $ko => $vo) {
				unset($v[$vo]);
			}

		}

		// 进行发送消息提醒
		$ok_send_on = $this->ok_send($true_m_openids, 'remind_on'); // 上班
		$ok_send_off = $this->ok_send($true_m_openids_off, 'remind_off'); // 下班


	}


	// 首先拿到可行的班次 数组
	protected function get_true_bc($type) {
		$batch = D('Sign/SignBatch', 'Service');

		if($type == "off") {

			$true_batch =  $batch->get_true_bc_off();

			// 布局数据 签到消息的提醒
			foreach ($true_batch as $k => $v) {
				$this->remind_data_off[$v['sbid']]['remind_on'] = $v['remind_on'];
				$this->remind_data_off[$v['sbid']]['remind_off'] = $v['remind_off'];
			}
			// 需要发消息的上班的班次
			$true_sbids = array_column($true_batch, 'sbid');
			return $true_sbids;
		}


		$true_batch =  $batch->get_true_bc();

		// 布局数据 签到消息的提醒
		foreach ($true_batch as $k => $v) {
			$this->remind_data[$v['sbid']]['remind_on'] = $v['remind_on'];
			$this->remind_data[$v['sbid']]['remind_off'] = $v['remind_off'];
		}

		// 需要发消息的上班的班次
		$true_sbids = array_column($true_batch, 'sbid');

		return $true_sbids;
	}

	// 接着拿出各个班次符合的部门id
	protected function get_true_cdids($true_sbids) {

		$out_data = array();
		$department = D('Sign/SignDepartment', 'Service');
		foreach ($true_sbids as $k => $v) {
			$data = $department->get_true_cdids($v);

			$out_data[$v] = array_column($data, 'department');
		}
		return $out_data;

	}

	// 表里拿到所有的m_uid
	protected function get_true_muids($cdids) {

		$serv_mdp = D('Common/MemberDepartment', 'Service');
		$departments = $serv_mdp->list_by_cdid($cdids);
		return array_column($departments, 'm_uid');
	}

	// 班次为基准从member表里面再拿到那个opid
	protected function get_true_openids($uids) {

		$serv_mem = D('Common/Member', 'Service');
		$members = $serv_mem->list_by_pks($uids);
		return array_column($members, 'm_openid', 'm_uid');
	}

	// 获取各个班次 上班已签过到的muid数据
	protected function get_over_qd_muid($true_sbids) {

		$SignRecord = D('Sign/SignRecord', 'Service');
		$qd_muid = $SignRecord->get_over_qd_muid($true_sbids);

		return $qd_muid;
	}

	// get_over_qd_muid_off

	// 获取各个班次 下班已签过到的muid数据
	protected function get_over_qd_muid_off($true_sbids) {

		$SignRecord = D('Sign/SignRecord', 'Service');
		$qd_muid = $SignRecord->get_over_qd_muid_off($true_sbids);
		return $qd_muid;

	}

	// 进行发送消息的操作
	protected function ok_send($true_m_openids, $type) {

		// 进行微信发送消息
		$serv_wxqy = &\Common\Common\Wxqy\Service::instance();
		foreach ($true_m_openids as $k => $v) {
			// 发送的消息内容
			if ($type == 'remind_off') {
				$content = $this->remind_data_off[$k][$type];
			}else {
				$content = $this->remind_data[$k][$type];
			}
			$serv_wxqy->post_text($content, $plugin['cp_agentid'], $v);
		}

	}

	// 继续执行的操作
	protected function goto_run($sbids) {

		// 接着拿出各个班次对应的部门id数据
		$cdids = $this->get_true_cdids($sbids);

		// 各个班次从member_department 表里拿到所有的m_uid
		$uids = $this->get_true_muids($cdids);
		if (empty($uids)) return;

		// 已班次为基准 分别从member表里拿出opid 并组装数据
		$true_m_openids = $this->get_true_openids($uids);
		return $true_m_openids;
	}



}
