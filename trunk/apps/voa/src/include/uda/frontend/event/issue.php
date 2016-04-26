<?php
/**
 * voa_uda_frontend_event_issue
 * 社群活动设置
 * @date: 2015年11月15日
 * @author: gaosong
 * @version:
 */

class voa_uda_frontend_event_issue extends voa_uda_frontend_event_base {
	/** service 类 */
	private $__service = null;
	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_event_setting();
		}
	}

	/**
	 *	判断当前用户的发布权限
	 * @param array $request
	 * @param array $result | bool
	 */
	public function issue(array $request, &$result) {
		$news_right = $this->__service->get_html5_issue($request['m_uid']);
		if (!$news_right) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_event::NO_ISSUE_ERROR);
		}
		return $result = true;
	}

	/**
	 *
	 * @param array $request
	 * @return boolean
	 */
	public function add(array $request) {

		// 定义参数请求规则
		$fields = array(
			// 签到用户ID
			'm_uids' => array(
				'm_uids', parent::VAR_ARR,
				array($this->__service, 'validator_uids'),
				null, false
			),
			// 签到部门ID
			'cd_ids' => array(
				'cd_ids', parent::VAR_ARR,
				array($this->__service, 'validator_cdids'),
				null, false
			),
			// 发起活动用户ID
			'add_uids' => array(
				'add_uids', parent::VAR_ARR,
				array($this->__service, 'validator_uids'),
				null, false
			),
			// 发起活动部门ID
			'add_ids' => array(
				'add_ids', parent::VAR_ARR,
				array($this->__service, 'validator_cdids'),
				null, false
			),
			// 默认部门
			'dept' => array(
				'dept', parent::VAR_ARR,
				array($this->__service, 'validator_cdids'),
				null, false
			)
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}

		// 签到权限
		$m_uids = !empty($this->__request['m_uids']) ? $this->__request['m_uids'] : '';
		$cd_ids = !empty($this->__request['cd_ids']) ? $this->__request['cd_ids'] : '';
		$all = (int)$request['is_all'];

		// 发起活动权限
		$add_all = (int)$request['add_is_all'];
		$add_uids = !empty($this->__request['add_uids']) ? $this->__request['add_uids'] : '';
		$add_ids = !empty($this->__request['add_ids']) ? $this->__request['add_ids'] : '';

		//默认部门
		$deptment = !empty($this->__request['dept']) ? $this->__request['dept'] : '';

		// all  0:指定对象 1:全公司  2:仅发起人
		$data = array(
			'm_uids' => $all == 2 ? 0:($all == 1? 0: $m_uids),
			'cd_ids' => $all == 2 ? 0: ($all == 1? 0: $cd_ids),
			'all' => $all == 2 ? 0: ($all == 1 ? 1 : 0),
			'only_author'=> $all == 2 ? 1:0,
			'add_all' => $add_all,
			'add_uids' => $add_all == 1 ? 0: $add_uids,
			'add_ids' => $add_all == 1 ? 0:  $add_ids,
			'deptment' => $deptment
		);

		$this->__service->update_settings($data);

		voa_h_cache::get_instance()->get('plugin.event.setting', 'oa', true);

		return true;
	}

}
