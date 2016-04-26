<?php
/**
 * addspecial.php
 * 新增特殊红包信息
 * $Author$
 * $Id$
 */
class voa_uda_frontend_redpack_addspecial extends voa_uda_frontend_redpack_abstract {
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
			'total' => array('total', parent::VAR_INT, null, null, false),
			'redpacks' => array('redpacks', parent::VAR_INT, null, null, false),
			'wishing' => array('wishing', parent::VAR_STR, 'chk_wishing', null, false),
			'sub_ac' => array('sub_ac', parent::VAR_STR, null, null, false),
			'remark' => array('remark', parent::VAR_STR, null, null, false),
			'uid' => array('uid', parent::VAR_STR, null, null, false),
			'username' => array('username', parent::VAR_STR, null, null, false),
			'actname' => array('actname', parent::VAR_STR, null, null, false),
			'starttime' => array('starttime', parent::VAR_STR, null, null, false),
			'endtime' => array('endtime', parent::VAR_STR, null, null, false),
			'min' => array('min', parent::VAR_INT, null, null, false),
			'max' => array('max', parent::VAR_INT, null, null, false)
		);
		if (! $this->extract_field($this->__in, $fields, $in)) {
			return false;
		}

		// 红包类型
		$type = voa_d_oa_redpack::TYPE_RAND;

		// 最大最小金额
		$min = empty($this->__in['redpack_min']) ? $this->_p_sets['redpack_min'] : $this->__in['redpack_min'];
		$max = empty($this->__in['redpack_max']) ? $this->_p_sets['redpack_max'] : $this->__in['redpack_max'];

		// 红包消息入库
		$redpack = $this->_serv_rp->insert(array(
			'm_uid' => $this->__in['uid'],
			'm_username' => $this->__in['username'],
			'wishing' => empty($this->__in['wishing']) ? '恭喜发财, 大吉大利' : $this->__in['wishing'],
			'actname' => $this->__in['actname'],
			'total' => $this->__in['total'],
			'left' => 0,
			'redpacks' => $this->__in['redpacks'],
			'remark' => $this->__in['remark'],
			'type' => $type,
			'min' => $min,
			'max' => $max,
			'highest' => 0,
			'rule' => ''
		));

		$out = $redpack;
		return true;
	}

	// 检查祝福语
	public function chk_wishing(&$wishing, $err = '') {

		if (0 > strlen($wishing)) {
			$wishing = '恭喜发财, 大吉大利';
		}

		return true;
	}

}
