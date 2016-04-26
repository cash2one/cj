<?php

/**
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/10/21
 * Time: 10:22
 */
class voa_uda_frontend_community_view extends voa_uda_frontend_community_abstract {

	/** service 类 */
	private $__service = null;

	public function __construct() {

		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_community();
		}
	}

	public function execute($request, &$result) {

		$this->_params = $request;
		// 提取用户提交的数据
		$fields = array(
			'cid' => array('a.cid', self::VAR_INT, null, null, true),
		);
		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}

		$result = $this->__service->get_share_by_conds($data);
		return true;
	}
}
