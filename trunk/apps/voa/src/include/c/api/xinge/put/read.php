<?php
/**
 * password.php
 * 密码修改
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_xinge_put_read extends voa_c_api_base {

	public function execute() {

		$uid = $this->_member['m_uid'];
		// 检查更新消息数目 start
		$serv_memberfield = &service::factory('voa_s_oa_member_field');
		$mfield = $serv_memberfield->fetch_by_id($uid);
		if (!empty($mfield)) {
			if ($mfield['mf_notificationtotal'] > 0) {
				$num = $mfield['mf_notificationtotal'] - 1;
				$serv_memberfield->update(array('mf_notificationtotal'=>$num), array('m_uid'=>$uid));
			}
		}
		// 检查更新消息数目 end


		return true;
	}

}
