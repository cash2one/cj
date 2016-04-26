<?php
/**
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/11/30
 * Time: 15:02
 */

class voa_uda_frontend_event_join extends voa_uda_frontend_event_base {

	public function __construct() {

		parent::__construct();
		$this->__service = new voa_s_oa_event_partake();
	}

	/**
	 * 获取活动参与人员
	 * @param $in
	 * @param $out
	 */
	public function event_join_users($in, &$out) {
		// 判断字段规则
		if (empty($in)) {
			return false;
		}
		$data['acid'] = $in;
		$data['type'] = 0;
		$out = $this->__service->list_by_conds($data);

		return true;
	}

	/**
	 * 获取活动删除
	 * @param $in
	 * @param $out
	 * @return bool
	 */
	public function  event_finish_join_users($in, &$out) {
		if(empty($in)) {
			return false;
		}
		//获取活动信息
		$server_event = new voa_s_oa_event();
		$event_data = array(
			'acid' => $in,
			'end_time >' => time()
		);
		$edata = $server_event->list_by_conds($event_data);

		//剔除活动已结束的活动
		if (!$edata) {
			//所有活动已结束
			$out = array();
			return false;
		}
		//未结束的活动
		$ev_id = array_column($edata, 'acid');
		$ev_title = array_column($edata, 'title');
		//获取活动人员
		$data['acid'] = $ev_id;
		$data['type'] = 1;
		$result = $this->__service->list_by_conds($data);

		$res = array();
		//对数据进行格式化处理
		if (!$result) {
			$out = array();
			return true;
		}

		foreach ($result as $_k => $_v) {
			$res[$_v['acid']][] = $_v['m_uid'];
		}

		foreach ($ev_id as $k => $v) {
			$out[$v]['title'] = $ev_title[$k];
			$out[$v]['uids'] = isset($res[$v]) ? $res[$v] : array();
		}

		return true;
	}
}
