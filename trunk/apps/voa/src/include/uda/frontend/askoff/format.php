<?php
/**
 * 请假数据格式化
 * $Author$
 * $Id$
 */

class voa_uda_frontend_askoff_format extends voa_uda_frontend_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 格式化请假数据数组
	 * @param array $data 数据数组
	 */
	public function askoff_list(&$list) {
		foreach ($list as &$data) {
			$this->askoff($data);
		}

		return true;
	}

	/**
	 * 格式化请假数据
	 * @param array $data 请假数据
	 */
	public function askoff(&$data) {
		$data['_subject'] = !empty($data['ao_subject']) ? rhtmlspecialchars($data['ao_subject']) : '';
		$mdw = rgmdate($data['ao_begintime'], 'm d w');
		$data['_begintime'] = explode(' ', $mdw);
		$mdw = rgmdate($data['ao_endtime'], 'm d w');
		$data['_endtime'] = explode(' ', $mdw);
		$data['_created'] = !empty($data['ao_created']) ? rgmdate($data['ao_created'], 'Y-m-d H:i') : '';
		$data['_created_u'] = !empty($data['ao_created']) ? rgmdate($data['ao_created'], 'u') : '';
		/** 月/日格式, 显示用 */
		$data['_begintime_md'] = rgmdate($data['ao_begintime'], 'm月d日');
		$data['_endtime_md'] = rgmdate($data['ao_endtime'], 'm月d日');
		$data['_begintime_ymdhi'] = rgmdate($data['ao_begintime'], 'Y-m-d H:i');
		$data['_endtime_ymdhi'] = rgmdate($data['ao_endtime'], 'Y-m-d H:i');
		/** 请假的天数 */
		$b_ymd = rgmdate($data['ao_begintime'], 'Y-m-d 00:00:00');
		$e_ymd = rgmdate($data['ao_endtime'], 'Y-m-d 23:59:59');
		$data['_days'] = ceil(($data['ao_endtime'] - $data['ao_begintime']) / 86400);

		// 请假时长，不到1天则显示小时，否则显示天数 By Deepseath@20141226#332
		$data['_timespace'] = '';
		// 起始结束时间秒数
		$abs_timespace_second = abs($data['ao_endtime'] - $data['ao_begintime']);
		list($_day, $_hour, $_minute) = voa_h_func::get_dhi($abs_timespace_second);
		// 起始时间跨日期，不在同一天，且起始时间超过1天则按天来计算
		if (rgmdate($data['ao_begintime'], 'ymd') != rgmdate($data['ao_endtime'], 'ymd') && $_day > 0) {
			$data['_timespace'] = $_day.' 天';
			if ($_hour > 0) {
				$data['_timespace'] = ($_day + 1).' 天';
			}
		}
		// 请假时长不超过24小时，则考虑按小时来显示
		if (empty($data['_timespace'])) {
			$__hour = ceil($abs_timespace_second/3600);
			$data['_timespace'] = $__hour.' 小时';
			if ($__hour == 12) {
				$data['_timespace'] = '半天';
			} elseif ($__hour > 12) {
				$data['_timespace'] = '1 天';
			}
		}
		//$data['_timespace'] = $abs_timespace_second;
		// End

		/** 状态显示 */
		$data['_status_class'] = '';
		if (voa_d_oa_askoff::STATUS_APPROVE == $data['ao_status']) {
			$data['_status_tip'] = '已通过';
		} else if (voa_d_oa_askoff::STATUS_REFUSE == $data['ao_status']) {
			$data['_status_class'] = 'fail';
			$data['_status_tip'] = '未通过';
		} else if ($data['ao_begintime'] < time()) {
			$data['_status_class'] = 'fail';
			$data['_status_tip'] = '已过期';
		} else {
			$data['_status_tip'] = '审批中';
		}
		$data['_created'] = !empty($data['ao_created']) ? rgmdate($data['ao_created'], 'Y-m-d H:i') : '';

		return true;
	}

	/**
	 * 格式化请假/回复详情
	 * @param array $data
	 */
	public function askoff_post(&$data) {
		$data['_subject'] = rhtmlspecialchars($data['aopt_subject']);
		$data['_message'] = bbcode::instance()->bbcode2html($data['aopt_message']);
		$data['_created_u'] = rgmdate($data['aopt_created'], 'u');
		return true;
	}

	/**
	 * 格式化进度信息
	 * @param array $data
	 * @return boolean
	 */
	public function askoff_proc(&$data) {
		$data['_remark'] = bbcode::instance()->bbcode2html($data['aopc_remark']);
		$data['_created_u'] = rgmdate($data['aopc_created'], 'u');
		$data['_updated_u'] = rgmdate($data['aopc_updated'], 'u');
		$data['_created'] = rgmdate($data['aopc_created'], 'Y-m-d H:i');
		return true;
	}

	/**
	 * 格式化附件信息
	 * @param array $data
	 * @param number $thumb_width 缩略图尺寸
	 * @return boolean
	 */
	public function askoff_attachment(&$data, $thumb_width = 45) {
		$data['_url'] = voa_h_attach::attachment_url($data['at_id']);
		$data['_thumb_url'] = voa_h_attach::attachment_url($data['at_id'], $thumb_width);

		return true;
	}
}
