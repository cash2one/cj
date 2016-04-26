<?php
/**
 * 编辑报名信息页的数据
 * $Author$
 * $Id$
 */
class voa_c_api_campaign_get_custom extends voa_c_api_campaign_base {


	protected function _before_action($action) {

		$this->_require_login = false;
		return parent::_before_action($action);
	}

	public function execute() {

		// 需要的参数
		$fields = array(
			//活动id
			'id' => array('type' => 'int', 'required' => true)
		);
		if (!$this->_check_params($fields)) {
			// 检查参数
			return false;
		}

		$id = $this->_params['id'];
		$saleid = $this->_member['m_uid'];

		$d = new voa_d_oa_campaign_campaign();
		$data = $d->get($id);
		if(!$data) {
			$this->_set_errcode('活动不存在');
			return false;
		}

		//获取自字义字段
		if($data['is_custom'] && $saleid) {
			$uda = new voa_uda_frontend_campaign_campaign();
			$data['custom'] = $uda->get_custom($id, $saleid);
		}

		$data['_created'] = rgmdate($data['created']);

		/*输出结果*/
		$this->_result = $data;

		return true;
	}
}
