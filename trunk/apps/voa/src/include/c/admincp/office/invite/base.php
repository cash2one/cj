<?php
/**
 * voa_c_admincp_office_invite_base
 * 邀请人员/后台/基类
 * Created by zhoutao.
 * Created Time: 2015/7/9  18:03
 */

class voa_c_admincp_office_invite_base extends voa_c_admincp_office_base {
	//是否需要审批
	protected  $_approval_state = array(
		voa_d_oa_invite_personnel::CHECK_NOTHING => '不限',
		voa_d_oa_invite_personnel::CHECK_ING => '审批邀请',
		voa_d_oa_invite_personnel::NO_CHECK => '直接邀请'
	);
	protected  $_look_array = array(
			voa_d_oa_invite_personnel::STATUS_YES => '已关注',
			voa_d_oa_invite_personnel::STATUS_NON => '已冻结',
			voa_d_oa_invite_personnel::STATUS_NONO => '未关注'
	);
	// 数据库配置数据
	protected $_invite_setting = array();

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->_invite_setting = voa_h_cache::get_instance()->get('plugin.invite.setting', 'oa');

		return true;
	}
}
