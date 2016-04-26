	<?php
/**
 * voa_uda_frontend_nvote_issue
 * 用户H5发布权限
 * @date: 
 * @author: 
 * @version:
 */

class voa_uda_frontend_nvote_issue extends voa_uda_frontend_nvote_abstract {
/** service 类 */
	private $__service = null;
	
	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_nvote_setting();
		}
	}

	/**
	 * 判断当前用户的发布权限
	 * @param $uid
	 * @return bool|false
	 */
	public function issue($uid) {

		// 判断当前用户是否在权限数据里
		$news_right = $this->__service->get_html5_issue($uid);

		// 判断正确
		if (!$news_right) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_nvote::NO_ISSUE_ERROR);
		}

		return true;
	}

	/**
	 * 新增投票
	 * @param array $request
	 * @return boolean
	 */
	public function add(array $request) {

	// 定义参数请求规则
		$fields = array(
			// 用户ID
			'm_uids' => array(
				'm_uids', parent::VAR_ARR,
				array($this->__service, 'validator_uids'),
				null, false
			),
			// 部门ID
			'cd_ids' => array(
				'cd_ids', parent::VAR_ARR,
				array($this->__service, 'validator_cdids'),
				null, false
			)
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}

		// 判断当前是否是全部可操作
		if ($request['is_all'] == 1) {
			$m_uids = '';
			$cd_ids = '';
		} else {
			// 添加权限
			$m_uids = !empty($this->__request['m_uids']) ? $this->__request['m_uids'] : '';
			$cd_ids = !empty($this->__request['cd_ids']) ? $this->__request['cd_ids'] : '';
		}

		$data = array('m_uids' => $m_uids, 'cd_ids' => $cd_ids, 'all' => $request['is_all']);

		// 更新setting
		$this->__service->update_settings($data);

		voa_h_cache::get_instance()->get('plugin.nvote.setting', 'oa', true);

		return true;
	}
}
