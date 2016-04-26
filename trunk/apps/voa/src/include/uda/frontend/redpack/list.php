<?php
/**
 * list.php
 * 发送红包
 * $Author$
 * $Id$
 */

class voa_uda_frontend_redpack_list extends voa_uda_frontend_redpack_abstract {
	// 请求的参数
	private $__in = array();
	// 返回的结果集
	private $__out = array();
	// 其他配置项
	private $__options = array();

	/**
	 * 领取红包
	 *
	 * @param array $in 请求的参数
	 *        + redpack_id 红包活动ID
	 * @param array $out 返回的结果集
	 *        + redpack_id
	 *        + uid
	 *        + openid
	 *        + ip
	 *        + money 当前接受者获得的金额，单位：分
	 *        + result 微信方返回的发放结果集
	 * @param mixed $options 其他用于扩展的额外参数
	*/
	public function doit(array &$out = array(), array $in, $options = array()) {

		// 请求规则定义
		$fields = array(
			array('page', self::VAR_INT, null, null, true), // 当前页码
			array('limit', self::VAR_INT, null, null, true) // 每页记录数
		);
		if (! $this->extract_field($this->__in, $fields, $in)) {
			return false;
		}

		// 分页信息
		$page = empty($this->__in['page']) ? 1 : $this->__in['page'];
		$limit = empty($this->__in['limit']) ? 0 : $this->__in['limit'];
		list($start, $limit, $page) = voa_h_func::get_limit($page, $limit);
		$page_option = array($start, $limit);
		unset($this->__in['page'], $this->__in['limit']);

		$list = $this->_serv_rp->list_all($page_option, array('id' => 'DESC'));
		$list = empty($list) ? array() : $list;

		foreach ($list as &$_v) {
			$_v = $this->_serv_rp->format($_v);
		}

		$out = $list;
		return true;
	}

}
