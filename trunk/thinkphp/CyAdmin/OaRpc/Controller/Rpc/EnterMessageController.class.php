<?php
/**
 * Created by PhpStorm.
 * User: ppker
 * Date: 2015/10/26
 * Time: 19:30
 */

namespace OaRpc\Controller\Rpc;

class EnterMessageController extends AbstractController {

	protected $_pay_status = array(); // 套件状态数组
	protected $_msg_log_ser = null; // 消息记录
	protected $_msg_read = null; // 已读已读
	protected $_app_set = null; // 应用设置service类
	protected $_app_set_data = array(); // 应用设置数据
	protected $_tao_data = array(); // 企业套件数据
	protected $_company_ser = null; // 企业付费设置

	protected $_enterprise_message = null; // 消息提醒模板ser
	protected $_message_att = null; // 消息附件ser

	// 初始化
	public function _initialize() {

		$this->_pay_status = array(
			1 => '已付费',
			2 => '已付费-即将到期',
			3 => '已付费-已到期',
			5 => '试用期-即将到期',
			6 => '试用期-已到期',
			7 => '试用期'
		);

		$this->_msg_log_ser = D('EnterpriseMessage', 'Service'); // 消息记录
		$this->_msg_read = D('EnterMessageRead', 'Service'); // 已读消息
		$this->_app_set = D('EnterpriseAppset', 'Service'); // 应用设置
		$this->_app_set_data = $this->_app_set->list_all(); // 应用设置数据
		$this->_company_ser = D('CompanyPaysetting', 'Service'); // 企业付费设置ser
	}

	public function get_mes_data($ep_id, $tao, $uid) {

		// 获取已读的消息数据
		$read_data = $this->_msg_read->list_by_conds($ep_id, $uid);

		$this->_tao_data = $tao;
		if ($read_data) {
			$re_array = array_column( $read_data, 'logid' ); // 消息id
		} else $re_array = array();

		// 获取真正的总数
		$real_count = $this->_get_real_count($ep_id, $re_array);

		// 企业付费设置信息
		$compay_data = $this->_company_ser->list_by_epid($ep_id);
		$status_pay = array(); // 当前企业的信息
		foreach ($compay_data as $k => $v) {
			$status_pay[$k][] = $this->_tao_data[$v['cpg_id']]['cpg_name'];
			$status_pay[$k][] = $this->_pay_status[$v['pay_status']];
		}
		return array(
			'total' => $real_count,
			'status_pay' => $status_pay,
			'appset_pub' => $this->_app_set_data
		);
	}


	/**
	 * @description 获取真正的总数
	 * @param $ep_id
	 * @param $re_array
	 * @return mixed
	 */
	protected function _get_real_count($ep_id, $re_array) {

		return $this->_msg_log_ser->get_real_count($ep_id, $re_array);
	}


	/**
	 * 通过rpc进行标记
	 * @param $logid
	 * @return mixed
	 */
	public function mark_read($logid, $uid) {

		// 通过rpc进行标记
		return $this->_msg_read->mark_insert($logid, $uid);
	}

	/**
	 * 得到消息模板的详情，发送时间要对应好
	 * @param $meid
	 * @param $logid
	 * @param $uid
	 * @return bool
	 */
	public function get_view($meid, $logid, $uid, $yd) {


		if (null == $this->_enterprise_message) {
			$this->_enterprise_message = D('EnterpriseMessageCy', 'Service');
		}
		if (null == $this->_message_att) {
			// $this->_message_att = D('EnterpriseAttachment', 'Service');
		}
		// 获取发送时间
		$creat_time = $this->_msg_log_ser->get((int)$logid);
		$creat_time = rgmdate( $creat_time['created'], 'Y-m-d H:i:s' );
		$notice = $this->_enterprise_message->get((int)$meid);
		$notice['author'] = '畅移云工作';
		$notice['_created'] = $creat_time;
		// 进行标记
		if (!$yd) $this->mark_read(array($logid), $uid);

		return $notice;

	}

}