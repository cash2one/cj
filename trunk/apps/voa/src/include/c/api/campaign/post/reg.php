<?php
/**
 * 活动->报名
 * 并返回电子邀请函信息
 * $Author$
 * $Id$
 */
class voa_c_api_campaign_post_reg extends voa_c_api_campaign_base {

	protected function _before_action($action) {

		$this->_require_login = false;
		return parent::_before_action($action);
	}

	public function execute() {

		// 需要的参数
		$fields = array(
			// 活动id
			'id' => array('type' => 'int', 'required' => true),
			// 销售id
			'saleid' => array('type' => 'int', 'required' => true),
			// 每页显示数据数
			'name' => array('type' => 'string', 'required' => true),
			// 读取的活动类型
			'mobile' => array('type' => 'string', 'required' => true),
			//关键词
			'custom' => array('type' => 'array', 'required' => false)
		);
		if (!$this->_check_params($fields)) {
			// 检查参数
			return false;
		}

		$custom = isset($_POST['custom']) ? $_POST['custom'] : array();

		// 一.保存到客户表,并返回客户id
		$customer = new voa_d_oa_campaign_customer();
		$cusid = $customer->save($this->_params['name'], $this->_params['mobile']);

		// 获取自字义字段记录
		$uda = new voa_uda_frontend_campaign_campaign();
		$field_set = $uda->get_custom($this->_params['id'], $this->_params['saleid']);

		// 与值混合
		$custom = array();
		foreach ($field_set as $k => $field) {
			$custom[$field] = strip_tags($this->_params['custom'][$k]);
		}

		// 保存到报名表
		$data = array('actid' => $this->_params['id'], 'saleid' => $this->_params['saleid'], 'customerid' => $cusid, 'name' => strip_tags($this->_params['name']), 'mobile' => strip_tags($this->_params['mobile']), 'custom' => json_encode($custom), 'actid' => $this->_params['id']);

		$reg = new voa_d_oa_campaign_reg();
		$regid = $reg->save($data);
		if (! $regid) {
			$this->_set_errcode('保存报名信息失败!');
			return false;
		}

		// 统计报名数
		$total = new voa_d_oa_campaign_total();
		$total->regs($this->_params['id'], $this->_params['saleid']);

		// 返回报名id
		$this->_result = $regid;

		//发送信息
		$this->_to_queue($data);

		return true;
	}

	private function _to_queue($reg) {

		// 获取agentid
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.campaign.setting', 'oa');
		$agentid = $plugins[$this->_p_sets['pluginid']]['cp_agentid'];
		$scheme = config::get('voa.oa_http_scheme');
		$url = voa_wxqy_service::instance()->oauth_url($scheme . $this->_setting['domain'] . "/campaign/view/?id={$reg['actid']}&pluginid=" . $this->_p_sets['pluginid']);
		// 发送微信消息
		$d = new voa_d_oa_campaign_campaign();
		$act = $d->get($reg['actid']);
		$content = "您推广的活动【{$act['subject']}】\n\n" . " <a href='" . $url . "'>点我查看</a>";

		// 整理需要接收消息的用户
		$mem = new voa_d_oa_member();
		$member = $mem->fetch($this->_params['saleid']);

		$data = array(
			'mq_touser' => $member['m_openid'],
			'mq_toparty' => '',
			'mq_msgtype' => voa_h_qymsg::MSGTYPE_TEXT,
			'mq_agentid' => (int)$agentid,
			'mq_message' => $content
		);
		voa_h_qymsg::push_send_queue($data);
	}
}
