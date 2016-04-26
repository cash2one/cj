<?php

/**
 * @author Burce
 */
class voa_uda_frontend_sign_out extends voa_uda_frontend_sign_base {

	/**
	 * 格式化
	 * 
	 * @param $in
	 * @return mixed
	 */
	public function format($in) {
		// 格式部门数据
		$dep_cache = $deplist = voa_h_cache::get_instance()->get('department', 'oa');
		$serv_dep_mem = &service::factory('voa_s_oa_member_department');
		$conditions['m_uid'] = $in['m_uid'];
		// 查找这个人对应所有的部门
		$dep_list = $serv_dep_mem->fetch_all_by_conditions($conditions);
		$deps = array();
		$cd_name = '';
		if (! empty($dep_list)) {
			//遍历拼接所有部门
			foreach ($dep_list as $_dep) {
				$deps[] = $dep_cache[$_dep['cd_id']]['cd_name'];
			}
			$cd_name = implode(',', $deps);
		}
		$in['cd_name'] = $cd_name;

		$in['sl_signtime'] = rgmdate($in['sl_signtime'], 'Y-m-d H:i');
		// 查询是否上传图片
		$serv_att = &Service::factory('voa_s_oa_sign_attachment');
		// $serv_comatt = &service::factory('voa_s_oa_common_attachment');
		$conds['outid'] = $in['sl_id'];
		$data = $serv_att->list_by_conds($conds);
		
		$img_list = array();
		if (! empty($data)) {
			
			foreach ($data as $_img) {
				$img_list[] = $_img['atid'];
			}
			// 记录关联图片
			$serv_at = &service::factory('voa_s_oa_common_attachment', array('pluginid' => 0));
			
			$common_attach_list = $serv_at->fetch_by_ids($img_list);
			
			foreach ($img_list as $_im) {
				if (! isset($common_attach_list[$_im])) {
					continue;
				}
				$at = $common_attach_list[$_im];
				$in['attachs'][] = array('at_id' => $_im, // 公共文件附件ID
'filename' => $at['at_filename'], // 附件名称
'filesize' => $at['at_filesize'], // 附件容量
'mediatype' => $at['at_mediatype'], // 媒体文件类型
'description' => $at['at_description'], // 附件描述
'isimage' => $at['at_isimage'] ? 1 : 0, // 是否是图片
'url' => voa_h_attach::attachment_url($_im, 0), // 附件文件url
'thumb' => $at['at_isimage'] ? voa_h_attach::attachment_url($_im, 45) : '');
			}
		} else {
			$in['attachs'] = null;
		}
		
		return $in;
	}

	/**
	 * 获取列表
	 * 
	 * @param array $request
	 * @param unknown $result
	 * @return boolean
	 */
	public function doit(array $request, &$result) {

		$serv = &Service::factory('voa_s_oa_sign_location');
		$page_option[0] = $request['start'];
		$page_option[1] = $request['limit'];
		$orderby['sl_updated'] = 'DESC';
		
		$conds = array();
		
		if (! empty($request['m_uid']) && $request['m_uid'] != - 1) {
			// 判断传过来的uid是否在权限下属里
			if (! in_array($request['m_uid'], $request['uids'])) {
				return $this->set_errmsg(voa_errcode_api_sign::NO_PERMISSIONS);
			}
			$conds['m_uid'] = $request['m_uid'];
		} else {
			// $conds ['m_uid IN (?)'] = $request ['uids'];
			$conds['m_uid'] = $request['m_uid'];
		}
		
		if (! empty($request['udate'])) {
			$conds['sl_signtime > ?'] = strtotime($request['udate']);
			$conds['sl_signtime < ?'] = strtotime($request['udate']) + 86400;
		}
		
		$result = $serv->list_by_conds($conds, $page_option, $orderby);
		$this->__doit($result);
		
		return true;
	}

	/**
	 * doit 处理图片部分
	 * 
	 * @param $result
	 * @return bool
	 */
	private function __doit(&$result) {

		if (! empty($result)) {
			// 查询上传的图片
			$serv_att = &Service::factory('voa_s_oa_sign_attachment');
			$slist = array();
			foreach ($result as $_va) {
				$slist[] = $_va['sl_id'];
			}
			
			$conds_out['outid IN (?)'] = $slist;
			$data = $serv_att->list_by_conds($conds_out);
			
			if (! empty($data)) {
				foreach ($data as $_ids) {
					$atids[] = $_ids['atid'];
				}
			}
			// 记录关联图片
			$serv_at = &service::factory('voa_s_oa_common_attachment', array('pluginid' => 0));
			
			if (! empty($data)) {
				$common_attach_list = $serv_at->fetch_by_ids($atids);
				$i = 1;
				foreach ($data as $_img) {
					if (! isset($common_attach_list[$_img['atid']])) {
						continue;
					}
					// 所有图片
					$result[$_img['outid']]['attachs'][] = voa_h_attach::attachment_url($_img['atid'], 0);
				}
			}
		}
		
		return true;
	}

	/**
	 * 格式日期
	 * 
	 * @param unknown $result
	 * @return unknown
	 */
	public function listformat($result) {
		// 格式时间
		foreach ($result as &$val) {
			$val['_sl_signtime'] = rgmdate($val['sl_signtime'], 'm-d H:i');
		}
		$data = array();
		// 返回允许的字段
		foreach ($result as $_res) {
			$able_res = array();
			$able_res['sl_id'] = $_res['sl_id'];
			$able_res['m_username'] = $_res['m_username'];
			$able_res['_sl_signtime'] = $_res['_sl_signtime'];
			$able_res['sl_address'] = $_res['sl_address'];
			// 如果有附件
			if (isset($_res['attachs'])) {
				$able_res['attachs'] = $_res['attachs'];
			} else {
				$able_res['attachs'] = array();
			}
			$data[] = $able_res;
		}
		
		return $data;
	}

}
