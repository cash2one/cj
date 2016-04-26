<?php
/**
 * send.php
 * 发送红包
 * $Author$
 * $Id$
 */

class voa_uda_frontend_redpack_send extends voa_uda_frontend_redpack_abstract {
	// 请求的参数
	private $__in = array();
	// 返回的结果集
	private $__out = array();
	// 其他配置项
	private $__options = array();
	// 当前红包信息
	private $__redpack = array();
	// 当前发送记录
	private $__rplog = array();

	/**
	 * 领取红包
	 *
	 * @param array $in 请求的参数
	 *        + redpack_id 红包活动ID
	 *        + uid 红包接受者uid（可以为0，此时此人非企业号内部成员）
	 *        + openid 红包接受者的微信openid
	 * @param array $out 返回的结果集
	 *        + redpack_id
	 *        + uid
	 *        + openid
	 *        + ip
	 *        + redpack 红包信息
	 *        + money 当前接受者获得的金额，单位：分
	 *        + result 微信方返回的发放结果集
	 *        + mch_billno 订单号
	 *        + mch_id 商户号
	 *        + wxappid 公众账号appid
	 *        + re_openid 用户openid
	 *        + total_amount 付款金额，单位：分
	 * @param unknown $options 其他用于扩展的额外参数
	 */
	public function doit(array $in, array &$out = array(), $options = array()) {

		// 请求规则定义
		$fields = array(
			'redpack_id' => array('redpack_id', parent::VAR_INT, null, null, false),
			'uid' => array('uid', parent::VAR_INT, null, null, false),
			'openid' => array('openid', parent::VAR_STR, null, null, false)
		);
		if (! $this->extract_field($this->__in, $fields, $in)) {
			return false;
		}

		// 红包信息
		if (!$this->__redpack = $this->_serv_rp->get($this->__in['redpack_id'])) {
			return voa_h_func::throw_errmsg('400:红包信息部存在');
		}

		// 读取待发送记录
		if (!$this->__rplog = $this->_serv_rplog->fetch_by_openid_redpackid($this->__in['openid'], $this->__in['redpack_id'])) {
			return voa_h_func::throw_errmsg('400:红包记录不存在');
		}

		// 判断是否领取
		if (voa_d_oa_redpack_log::SEND_ST_YES == $this->__rplog['sendst']) {
			return voa_h_func::throw_errmsg('400:该红包已发送');
		}

		// 微信发放红包
		$send_result = array();
		if (! $this->__push_wxpay($send_result)) {
			return false;
		}

		// 本地发放红包
		if (! $this->__handed_out_redpack($send_result)) {
			return false;
		}

		$out = $this->_serv_rplog->format($this->__rplog);
		$out['result'] = $send_result; // 微信红包发放返回的结果集
		$out['redpack'] = $this->__redpack; // 红包信息

		return true;
	}

	/**
	 * 本地发放红包
	 *
	 * @return boolean
	 */
	private function __handed_out_redpack($send_result) {

		$data = array(
			'result' => serialize($send_result),
			'sendst' => voa_d_oa_redpack_log::SEND_ST_YES
		);
		$this->_serv_rplog->update($this->__rplog['id'], $data);
		return true;
	}

	/**
	 * 微信发放红包并写入本地发放结果
	 *
	 * @param array $send_result (引用结果)红包发放微信返回的结果
	 * @return boolean
	 */
	private function __push_wxpay(&$send_result) {

		// 载入微信支付红包类
		$redpack = new voa_wepay_redpack();
		// 设置参数
		// $openid = 'o06msuFDO7_xZOdSNAHZq6fe_zJ0'; // 测试用openid
		// $money = 100; // 红包金额，单位分
		$options = array(
			'nick_name' => empty($this->__redpack['nickname']) ? $this->_p_sets['default_sender_name'] : $this->__redpack['nickname'], // 提供方名称
			'send_name' => empty($this->__redpack['sendname']) ? $this->_p_sets['default_sender_name'] : $this->__redpack['sendname'],  // 红包发送者名称
			're_openid' => $this->__in['openid'],  // 接收者
			'total_amount' => $this->__rplog['money'],  // 付款金额，单位分
			'min_value' => $this->__rplog['money'],
			'max_value' => $this->__rplog['money'],
			'total_num' => 1,  // 红包収放总人数
			'wishing' => $this->__redpack['wishing'],
			'client_ip' => controller_request::get_instance()->get_client_ip(),
			'act_name' => empty($this->__redpack['actname']) ? '红包' : $this->__redpack['actname'],  // 活劢名称
			'remark' => empty($this->__redpack['remark']) ? '红包' : $this->__redpack['remark'],
			'mch_id' => $this->_sets['mchid'],
			'mch_billno' => $this->__rplog['mch_billno']
		); // 备注信息

		// 推送微信发放红包
		if (! $redpack->send($options, $send_result)) {
			$this->errcode = $redpack->errcode;
			$this->errmsg = $redpack->errmsg;
			return voa_h_func::throw_errmsg($this->errcode . ':' . $this->errmsg);
		}

		return true;
	}

}
