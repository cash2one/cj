<?php
/**
 * voa_uda_frontend_file_update
 * 更新文件状态
 * $Author$
 * $Id$
 */	
class voa_uda_frontend_file_update extends voa_uda_frontend_file_base {

	/**
	 * 更新指定文件
	 * @param mixed  $return
	 * @param number $id 
	 * @param char $rename 
	 * @param number $uid 
	 * @return void
	 */
	public function rename(&$return, $id, $rename, $uid) {
		//取附件ID
		$attr = new voa_d_oa_file_attr();
		$attr_arr = $attr->get($id);
		//判断 空参数及 权限 
		if (empty($attr_arr) || $attr_arr['m_uid'] != $uid) {
			$this->errmsg(90001, voa_errcode_api_file::USER_NOT_PERMISSION);
			return false;
		}

		//操作文件表重命名
		$return = $attr->update($id, array('fla_alias' => $rename));
		return true;
	}

	/**
	 * 删除指定文件
	 * @param mixed  $return 
	 * @param number $id 
	 * @param number $uid 
	 * @return void
	 */
	public function del(&$return, $id, $uid) {
		$attr = new voa_d_oa_file_attr();
		$attr_arr = $attr->get($id);
		if (empty($attr_arr) || $attr_arr['m_uid'] != $uid) {
			$this->errmsg(90001, voa_errcode_api_file::USER_NOT_PERMISSION);
			return false;
		}
		//查是否被使用
		$_is_use = $attr->check_by_at_id($attr_arr['at_id']);
		if (!$_is_use) {
			return false;
		}
		//更新文件附件状态及时间
		$attach = &service::factory('voa_s_oa_common_attachment', array('pluginid' => startup_env::get('pluginid')));
		//更新 删除状态及时间
		$return = $attach->delete_by_ids($attr_arr['at_id']);
		//更新文件状态及时间
		$attr->del_by_fla_id($id);
		return true;
	}
}
