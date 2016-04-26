<?php

/**
 * voa_c_admincp_office_sign_edit
 * 企业后台/微办公管理/考勤签到/详情页
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_sign_detail extends voa_c_admincp_office_sign_base {
	private $_perpage = 15;

	public function execute() {
		$serv_detail = &service::factory('voa_s_oa_sign_record');

		// 处理获取的数据
		$this->__deal_data($serv_detail, $s_time, $m_uid, $date);

		$time_min = strtotime($s_time);
		$time_max = $time_min + 86400;
		$conds_tmp ['m_uid'] = $m_uid;
		$conds_tmp ['sr_signtime >= ?'] = $time_min;
		$conds_tmp ['sr_signtime <= ?'] = $time_max;

		$date_w = date('w', strtotime($date));

		$week_lang = array(
			'周日',
			'周一',
			'周二',
			'周三',
			'周四',
			'周五',
			'周六'
		);

		// 获取对应的日期
		foreach ($week_lang as $_key => $val) {
			if ($_key == $date_w) {
				$week_val = $val;
			}
		}
		$data = $serv_detail->list_by_conds($conds_tmp);

		// 获取人信息
		$serv_member = &service::factory('voa_s_oa_member');
		$member = $serv_member->fetch($m_uid);
		if (empty ($member)) {
			$this->message('error', '指定人员不存在或已退出系统[103]');
		}

		// 获取 备注信息
		$this->__get_detail_list($data, $detail_list);

		$this->view->set('detail_list', $detail_list);
		$this->view->set('m_uid', $m_uid);
		$this->view->set('date', $date);
		$this->view->set('week_val', $week_val);
		$this->view->set('data', $data);
		$this->view->set('m_username', $member ['m_username']);

		$this->output('office/sign/detial');

		return true;
	}

	/**
	 * 获取备注信息
	 * @param $data
	 * @param $detail_list
	 * @return bool
	 */
	private function __get_detail_list(&$data, &$detail_list) {
		$detail_list = array();
		$sbid_list = array();
		if (!empty ($data)) {
			foreach ($data as $_v) {
				$srid_list [] = $_v ['sr_id'];
			}
			$conds_det ['sr_id in (?)'] = $srid_list;
			// 备注信息
			$serv_det = &service::factory('voa_s_oa_sign_detail');
			$detail_list = $serv_det->list_by_conds($conds_det);
			if (!empty ($detail_list)) {
				foreach ($detail_list as &$_vdet) {
					$_vdet ['_sd_updated'] = rgmdate($_vdet ['sd_created'], 'Y-m-d H:i');
				}
			}
			$data = $this->deformat($data);
		}

		return true;
	}

	/**
	 * 处理获取的数据
	 * @param $serv_detail
	 * @param $s_time
	 * @param $m_uid
	 * @param $date
	 * @return bool
	 */
	private function __deal_data($serv_detail, &$s_time, &$m_uid, &$date) {
		if (isset ($_GET ['sr_id'])) {
			// 通过签到记录ID来浏览用户签到详情
			$id = rintval($this->request->get('sr_id'), false);
			$info_tmp = $serv_detail->get($id);
			$s_time = date('Y-m-d', $info_tmp ['sr_signtime']);
			$m_uid = $info_tmp ['m_uid'];

			$date = date('Y-m-d', $info_tmp ['sr_signtime']);
		} elseif (isset ($_GET ['m_uid'])) {
			// 通过指定用户ID和某个日期来获取此人该天的签到详情
			$m_uid = rintval($this->request->get('m_uid'), false);

			$date = $this->request->get('date');
			$s_time = ( string )$this->request->get('date');
			if (empty ($s_time)) {
				$this->message('error', '请正确输入要查看的日期，格式为：yyyy-mm-dd');
			}
		}

		return true;
	}

	/**
	 * 格式详情数据
	 * @param unknown $day_date
	 * @return unknown
	 */
	public function deformat($day_date) {
		$status = array(
			'1' => '正常',
			'2' => '迟到',
			'4' => '早退'
		);
		foreach ($day_date as &$data) {
			$data ['_sr_signtime'] = rgmdate($data ['sr_signtime'], 'Y-m-d H:i');
			// 格式状态
			$data ['_sr_status'] = $status [$data ['sr_sign']];
		}

		return $day_date;
	}
}
