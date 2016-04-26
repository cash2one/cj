<?php
/**
 * xinge.php
 * 企业信息表数据访问操作
 * Create By mojianyuan
 * $Author$
 * $Id$
 */
class voa_uda_uc_xinge extends voa_uda_uc_base {


	public function __construct() {
		parent::__construct();

		$this->errcode = 1;
		$this->errmsg = 'uda error';
	}

	/**
	 * 推送信鸽
	 * @param string $message
	 * @param int $uid
	 * @param string $message
	 * @param string $title
	 * @param int $itemid    消息的id
	 * @param int $pluginid  应用id 0 = 系统
	 * @param int $msgtype  消息类型: 1＝notification , 2 = message
	 * @return boolean
	 */
	public function push($uid = 0, $message='', $title = '', $fromuser = "畅移", $itemid = 0, $pluginid = 0,  $msgtype = 1) {

		// 检查更新消息数目 start
		$serv_memberfield = &service::factory('voa_s_oa_member_field');
		$mfield = $serv_memberfield->fetch_by_id($uid);
		$num = 1;
		if (!empty($mfield)) {
			$num = $mfield['mf_notificationtotal'] + $num;
			$serv_memberfield->update(array('mf_notificationtotal'=>$num), array('m_uid'=>$uid));
		} else {
			$serv_memberfield->insert(array('mf_notificationtotal'=>$num, 'm_uid'=>$uid));
		}
		// 检查更新消息数目 end

		$md5 = strtoupper(md5(startup_env::get('domain').$uid));
		$member = $this->s('voa_s_oa_member_field')->fetch_by_id($uid);
		$data = array();
		$data['xgq_touser'] = $md5;
		$data['xgq_message'] = $message;
		$data['xgq_title'] = $title;
		$data['xgq_itemid'] = $itemid;
		$data['xgq_pluginid'] = $pluginid;
		$data['xgq_fromuser'] = $fromuser;
		$data['xgq_devicetype'] = $member['mf_devicetype'];
		$data['xgq_notificationtotal'] = $num;

		$data['xgq_msgtype'] = ($msgtype != 1 ? 2 : 1);
		if ($this->s('voa_s_oa_xinge_queue')->insert($data)) {
			return true;
		}

		return false;
	}

	public function get_unsend_list($time, $perpage) {

		return $this->s('voa_s_oa_xinge_queue')->fetch_unsend_by_sendtime($time, $perpage);
	}

}
