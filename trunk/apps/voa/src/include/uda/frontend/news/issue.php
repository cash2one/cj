<?php
/**
 * voa_uda_frontend_news_issue
 * 用户H5发布权限
 * @date: 2015年5月8日
 * @author: kk
 * @version:
 */

class voa_uda_frontend_news_issue extends voa_uda_frontend_news_abstract {
/** service 类 */
	private $__service = null;
	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_news_setting();
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
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::NO_ISSUE_ERROR);
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

		// 添加前台发布权限
		$m_uids = !empty($this->__request['m_uids']) ? $this->__request['m_uids'] : '';
		$cd_ids = !empty($this->__request['cd_ids']) ? $this->__request['cd_ids'] : '';
		$data = array('m_uids' => $m_uids, 'cd_ids' => $cd_ids);
		$this->__service->update_setting($data);


		voa_h_cache::get_instance()->get('plugin.news.setting', 'oa', true);

		return true;
	}

}
