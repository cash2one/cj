<?php

/**
 * Created by PhpStorm.
 * User: xbs
 * Date: 15/11/17
 * Time: 17:29
 */
class voa_uda_frontend_banner_get extends voa_uda_frontend_banner_base {

	public function __construct() {

		parent::__construct();
		$this->__service = new voa_s_oa_banner();
	}

	/**
	 * 获取单条
	 * @param $in
	 * @param $out
	 * @param object $session
	 * @return bool
	 */
	public function get_bid($in, &$out) {

		$data = (int)$in;

		// 获取banner
		$result = $this->__service->get($data);
		$this->_bid_format($result, $out);

		return true;
	}

	/**
	 * 格式化数据
	 * @param $in
	 * @param $out
	 * @return bool
	 */
	protected function _bid_format($in, &$out) {

		if (empty($in)) {
			$out = array();

			return true;
		}
		$out = $in;
		$out['att_url'] = voa_h_attach::attachment_url($in['attid'], 0);

		return true;
	}

	public function update($in, &$out) {

		$bid = $in['bid'];
		$fields = array(
			'cid' => array('cid', parent::VAR_INT, null, null, false),
			'cname' => array('cname', parent::VAR_STR, null, null, false),
			'lid' => array('lid', parent::VAR_INT, null, null, false),
			'attid' => array('attid', parent::VAR_INT, null, null, false)
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $in)) {
			return false;
		}

		$data = $this->__request;
		if (empty($data['cname'])) {
			$this->errmsg(10001, '类型选择不能为空');

			return false;
		}
		if (empty($data['lid'])) {
			$this->errmsg(10002, '具体内容不能为空');

			return false;
		}
		if (empty($data['attid'])) {
			$this->errmsg(10003, '封面图片不能为空');

			return false;
		}
		$out = $this->__service->update_by_conds($bid, $data);

		return true;
	}
}
