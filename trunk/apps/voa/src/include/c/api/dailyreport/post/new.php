<?php
/**
 * voa_c_api_dailyreport_post_new
 * 新建日报
 * $Author$
 * $Id$
 */
class voa_c_api_dailyreport_post_new extends voa_c_api_dailyreport_base {

	public function execute() {

        $daily_type = $this->_params['daily_type'];
        $reporttime_key = sprintf('reporttime%s',$daily_type);
		if (!empty($this->_params['reporttime'])) {
			$this->_params[$reporttime_key] = $this->_params['reporttime'];
		}
		// 需要的参数
		$fields = array(
            //日报类型
            'daily_type'=>array(
                'type' => 'number',
                'required' => true
            ),
			// 日报内容
			'message' => array(
				'type' => 'string_trim',
				'required' => true
			),
			// 日报时间
            $reporttime_key => array(
				'type' => 'string_trim',
				'required' => true
			),
			// 接收人员uid
			'approveuid' => array(
				'type' => 'string_trim',
				'required' => true
			),
			// 抄送人员uid
			'carboncopyuids' => array(
				'type' => 'string_trim',
				'required' => false
			),
			// 附件id
			'at_ids' => array(
				'type' => 'string_trim',
				'required' => false
			)
		);

		// 基本验证检查
		if (! $this->_check_params($fields)) {
			return false;
		}

		// 日报内容检查
		if (empty($this->_params['message'])) {
			return $this->_set_errcode(voa_errcode_api_dailreport::NEW_MESSAGE_NULL);
		}

		// 日报时间检查
		if (empty($this->_params[$reporttime_key])) {
			return $this->_set_errcode(voa_errcode_api_dailreport::NEW_REPORTTIME_NULL);
		}
		if (is_numeric($this->_params[$reporttime_key])) {
			$reporttime = (int) $this->_params[$reporttime_key];
		} else {
			$reporttime = rstrtotime($this->_params[$reporttime_key]);
		}

		// 接收人员检查
		if (empty($this->_params['approveuid'])) {
			return $this->_set_errcode(voa_errcode_api_dailreport::NEW_APPRAVEUID_NULL);
		}

		// 接收人号不能是自己 检查
		if ($this->_params['approveuid'] == $this->_member['m_uid']) {
			return $this->_set_errcode(voa_errcode_api_dailreport::NEW_APPRAVEUID_SET_NULL);
		}

		// 入库操作
		if (! $this->_add()) {
			return false;
		}

		$this->_result = array(
			'id' => $this->_return['dr_id']
		);
		return true;
	}

	/*
	 * 入库
	 * @return boolen 新增成功
	 */
	protected function _add() {
		$uda = &uda::factory('voa_uda_frontend_dailyreport_insert');
		// 日报信息
		$dailyreport = array();
		// 日报详情信息
		$post = array();
		// 目标人信息
		$mem = array();
		// 抄送人信息
		$cculist = array();
		if (! $uda->dailyreport_new($dailyreport, $post, $mem, $cculist)) {
			// $this->_error_message($uda->error);
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}
		$this->_return = $dailyreport;

		// 发送消息通知
		$uda->send_wxqymsg_news($this->session, $dailyreport, 'new'
				, $this->_member['m_uid'], $mem);

		return true;
	}
}
