<?php
/**
 * get.php
 * 获取红包活动信息
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_redpack_get extends voa_uda_frontend_redpack_abstract {
	// 请求的参数
	private $__in = array();
	// 返回的结果集
	private $__out = array();
	// 其他扩展参数
	private $__options = array();

	/**
	 * 获取指定红包的信息
	 *
	 * @param array $in 请求的参数
	 *  + redpack_id 红包id
	 * @param array $out 返回的结果集，见redpack表字段
	 * @param array $options 其他扩展参数
	 * @return boolean
	 */
	public function doit(array $in, array &$out = array(), array $options = array()) {

		// 请求规则定义
		$fields = array(
			'redpack_id' => array('redpack_id', parent::VAR_INT, null, null, false)
		);
		if (! $this->extract_field($this->__in, $fields, $in)) {
			return false;
		}

		// 获取当前红包
		$out = $this->_serv_rp->get($this->__in['redpack_id']);
		$this->_serv_rp->format($out);
		return true;
	}

}
