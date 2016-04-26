<?php
/**
 * voa_uda_frontend_plan_format
 * 统一数据访问/日程应用/数据格式化
 * $Author$
 * $Id$
 */
class voa_uda_frontend_plan_format extends voa_uda_frontend_plan_base {
	/**
	 * 格式化日报主表数据
	 * @param  array   $plan
	 * @param  array   $member
	 * @return boolean
	 */
	public function main(&$plan, $member = array()) {
		$plan['_begin_at'] = rgmdate($plan['pl_begin_at'], 'Y-m-d');
		$plan['_finish_at'] = rgmdate($plan['pl_finish_at'], 'Y-m-d');

		// $plan['_js_begin_at'] = rgmdate($plan['pl_begin_at']-(30*24*3600), 'Y/m/d');
		// $plan['_js_finish_at'] = rgmdate($plan['pl_finish_at']-(30*24*3600), 'Y/m/d');

		$plan['_js_alarm_at'] = rgmdate($plan['pl_alarm_at'], 'Y-m-d H:i:s');

		/** 发起时间 */
		$plan['_created'] = rgmdate($plan['pl_created'], 'Y-m-d H:i');
		/** 个性化发起时间 */
		$plan['_created_u'] = rgmdate($plan['pl_created'], 'u');
		/** 标题 */
		$plan['_subject'] = rhtmlspecialchars($plan['pl_subject']);

		if (isset($plan['plp_message'])) {
			$plan['_message'] = bbcode::instance()->bbcode2html($plan['plp_message']);
		}

		return true;
	}

	/**
	 * 格式化日报
	 * @param array $data
	 */
	public function planPost(&$data) {
		$data['_subject'] = rhtmlspecialchars($data['plp_subject']);
		$data['_message'] = bbcode::instance()->bbcode2html($data['plp_message']);
		$data['_created_u'] = rgmdate($data['plp_created'], 'u');

		return true;
	}

	public function inList(&$columns) {
		$columns['_subject'] = rhtmlspecialchars($columns['pl_subject']);
		$columns['_address'] = rhtmlspecialchars($columns['pl_address']);
		$columns['_type'] = (int) $columns['pl_type'];
		$columns['_alarm_at'] = rgmdate($columns['pl_alarm_at'], 'm月d日 H:i');
		$columns['_created'] = rgmdate($columns['pl_created'], 'Y-m-d H:i');
		$columns['_updated'] = rgmdate($columns['pl_updated'], 'Y-m-d H:i');

		return true;
	}

	public function my(&$columns) {
		$columns['_subject'] = rhtmlspecialchars($columns['pl_subject']);
		$columns['_address'] = rhtmlspecialchars($columns['pl_address']);
		$columns['_type'] = (int) $columns['pl_type'];
		$columns['_begin_at'] = rgmdate($columns['pl_begin_at'], "Y-m-d");
		list($columns['_begin_at_y'], $columns['_begin_at_m'], $columns['_begin_at_d']) = explode('-', $columns['_begin_at']);
		$columns['_begin_at_t'] = rgmdate($columns['pl_begin_at'], 't');
		$columns['_finish_at'] = rgmdate($columns['pl_finish_at'], 'Y-m-d');
		$columns['_finish_at_t'] = rgmdate($columns['pl_finish_at'], 't');
		$columns['_alarm_at'] = rgmdate($columns['pl_alarm_at'], 'm月d日 H:i');
		$columns['_created'] = rgmdate($columns['pl_created'], 'Y-m-d H:i');
		$columns['_updated'] = rgmdate($columns['pl_updated'], 'Y-m-d H:i');

		return true;
	}
}
