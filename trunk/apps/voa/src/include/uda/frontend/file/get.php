<?php
/**
 * voa_uda_frontend_file_get
 * 搜索文件LIST
 * $Author$
 * $Id$
 */
class voa_uda_frontend_file_get extends voa_uda_frontend_file_base {
	/**
	 * 按指定条件搜索文件
	 * @param mixed $return
	 * @param number $uid
	 * @param number $fla_id
	 * @param number $perpage
	 * @param number $current_page
	 * @return array
	 */
	public function search(&$return, $uid, $fla_id, $perpage, $current_page = 0) {

		$list = array();
		$at_idarr = array();
		$result = array();
		$attr = new voa_d_oa_file_attr();
		$attach = &service::factory('voa_s_oa_common_attachment', array('pluginid' => startup_env::get('pluginid')));
		// attr count及array
		$total = $attr->count_by_uid_fla_id($uid, $fla_id);
		$attrarr = $attr->get_by_uid_fla_id($uid, $fla_id, $perpage, $current_page);
		$at_id2fla_id = array();
		// 读取系统配置信息
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$scheme = config::get('voa.oa_http_scheme');
		foreach ($attrarr as $key => $value) {
			$at_idarr[] = $value['at_id'];
			//$list[$value['fla_id']] = $value;
			$list[$value['fla_id']]['id'] = $value['fla_id'];
			$list[$value['fla_id']]['type'] = $value['fla_type'];
			$list[$value['fla_id']]['icon'] = $value['fla_icon'];
			$list[$value['fla_id']]['fla_alias'] = $value['fla_alias'];
			$list[$value['fla_id']]['shareurl'] = $scheme.$sets['domain'].'/frontend/file/showshare/fla_id/'.$value['fla_id'];
			$at_id2fla_id[$value['at_id']] = $value['fla_id'];
		}
		$attacharr = $attach->fetch_by_conditions(array('at_id' => array($at_idarr , 'in')));
		foreach ($attacharr as $key => $value) {
			$fla_id = $at_id2fla_id[$value['at_id']];
			$list[$fla_id]['filename'] = $value['at_filename'];
			$list[$fla_id]['filesize'] = $value['at_filesize'];
			$list[$fla_id]['attachment'] = voa_h_attach::attachment_url($value['at_id']);;
			$list[$fla_id]['created'] = $value['at_created'];
			$list[$fla_id]['status'] = $value['at_status'];
		}

		//-pages
		$pages = ceil($total / $perpage);

		//list format
		foreach ($list as $key => $value) {
			if ($value['fla_alias']) {
				$value['filename'] = $value['fla_alias'];

			}
			unset($value['fla_alias']);
			$result[] = $value;
		}
		$return = array($total, $pages, $result);
		return true;
	}

	/**
	 * 查看指定文件
	 * @param mixed $list
	 * @param number $fla_id
	 * @return  boolen
	 */
	public function get_by_fla_id(&$list, $fla_id) {

		$attr = new voa_d_oa_file_attr();
		$list = array();
		$attach = &service::factory('voa_s_oa_common_attachment', array('pluginid' => startup_env::get('pluginid')));
		// attr array
		$list = $attr->get($fla_id);
		// check 文件类型 （文件）
		if (empty($attrarr) || $attrarr['fla_type'] == '1' || empty($attrarr['at_id'])) {
			return false;
		}
		$attacharr = $attach->fetch_by_id($attrarr['at_id']);
		$list = array_merge($attrarr, $attacharr);
		return true;
	}
}
