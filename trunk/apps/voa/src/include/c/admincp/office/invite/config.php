<?php
/**
 * voa_c_admincp_office_invite_config
 * 邀请人员/后台/配置页面展示
 * Created by zhoutao.
 * Created Time: 2015/7/9  18:03
 */

class voa_c_admincp_office_invite_config extends voa_c_admincp_office_invite_base {

	// 通讯录自定义字段配置
	protected $_member_fields = '';

	public function execute() {
		// 通讯录自定义字段配置
		$temp = voa_h_cache::get_instance()->get('plugin.member.setting', 'oa');
		$temp = $temp['fields'];
		/**
		 * 通讯录迭代后的自定义字段兼容
		 */
		foreach ($temp['custom'] as $_key => $_rule) {
			if (substr($_key, 0, 3) == 'ext') {
				$this->_member_fields[substr($_key, 3)] = array(
					'desc' => $_rule['name'],
				);
			}
		}

		// 剩余时间数据库数据转换成具体时间
		$overdue_time = '';
		$this->__overdue_to_time($this->_invite_setting['overdue'], $overdue_time);
		$this->_invite_setting['overdue'] = $overdue_time;

		// 如果有提交的数据，处理
		$postx = $this->request->postx();
		if (!empty($postx)) {

			// 具体时间转换成剩余时间数据库数据
			$postx_overdue = '';
			if (isset($postx['overdue']) && !empty($postx['overdue'])) {
				$this->__time_to_overdue($postx['overdue'], $postx_overdue);
				$postx['overdue'] = $postx_overdue;
			}

			// 邀请设置提交
			if (isset($postx['is_invite'])) {
				if (isset($postx['short_paragraph']) && empty($postx['short_paragraph'])) {
					$this->_admincp_error_message('10000', '邀请语不能为空');
				}
				if (isset($postx['short_paragraph']) && !validator::is_string_count_in_range($postx['short_paragraph'], 1, 80)) {
					$this->_admincp_error_message('10001', '邀请语不能大于80字或者小于等于1字');
				}
				if (isset($postx['m_uid']) && empty($postx['m_uid'])) {
					$this->_admincp_error_message('10002', '可邀请人不能为空');
				}
				if (isset($postx['cd_id']) && empty($postx['cd_id'])) {
					$this->_admincp_error_message('10003', '默认部门不能为空');
				}

				$uda = &uda::factory('voa_uda_frontend_invite_updata');
				$data_out = null;
				// 整理数据
				$uda->invite_setting_data($postx, $data_out);

				// 判断提交是否有改动
				$this->__is_diff($this->_invite_setting, $data_out);
			}

			// 公司介绍提交
			if (isset($postx['is_introduction'])) {
				$data = array(
					'logo' => (string)$postx['logo']['at_id'],
					'introduction' => (string)$postx['content']
				);

				// 判断提交是否有改动
				$this->__is_diff($this->_invite_setting, $data);
			}
		}

		// 初始化编辑器
		$ueditor = new ueditor();
		$content_key = 'content';
		// 编辑器资源路径
		$ueditor->ueditor_home_url = config::get(startup_env::get('app_name') . '.ueditor.ueditor_home_url');
		// 处理上传文件路径
		$ueditor->server_url = '/admincp/ueditor/';

		$ueditor->ueditor_config = array('toolbars' => '_mobile', 'textarea' => $content_key, 'initialFrameHeight' => 300, 'initialContent' => isset($this->_invite_setting['introduction']) ? $this->_invite_setting['introduction'] : '', 'elementPathEnabled' => false);
		if (!$ueditor->create_editor('content', '')) {
			$ueditor_output = $ueditor->ueditor_error;
		} else {
			$ueditor_output = $ueditor->ueditor_html;
		}

		/*
		 * 页面显示内容赋值
		 */
		// 所有部门信息
		$all_cd = voa_h_cache::get_instance()->get('department', 'oa');
		// 页面显示默认的选中部门
		$cd_id = explode(',', $this->_invite_setting['cd_id']);
		$cd_ids = array();
		foreach ($cd_id as $k => $v) {
			if (!empty($all_cd[$v])) {
				$cd_ids[] = array(
					'id' => $v,
					'name' => $all_cd[$v]['cd_name'],
					'isChecked' => (bool)true,
				);
			}
		}
		$this->_invite_setting['cd_id'] = json_encode(array_values($cd_ids));

		// 能邀请，的人
		$setting_primary_ids = explode(',', $this->_invite_setting['primary_id']);
		// 页面显示默认的能邀请，的人
		$primary_id = array();
		foreach ($setting_primary_ids as $key => $val) {
			$user_name = voa_h_user::get($val);
			if (!empty($user_name)) {
				$primary_id[] = array(
					'm_uid' => $val,
					'm_username' => $user_name['m_username'],
					'selected' => (bool)true,
				);
			}
		}
		$this->_invite_setting['primary_id'] = json_encode(array_values($primary_id));

		// 反序列化数据库自定义字段设置数据
		$this->_invite_setting['custom'] = unserialize($this->_invite_setting['custom']);

		// 已经上传的logo图片
		if ($this->_invite_setting['logo']) {
			$logo_img = voa_h_attach::attachment_url($this->_invite_setting['logo']);
			$this->view->set('logo', $logo_img);
		}

		$this->view->set('custom', $this->_member_fields);
		$this->view->set('data', $this->_invite_setting);
		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, 'config', $this->_module_plugin_id));
		$this->view->set('ueditor_output', $ueditor_output);
		$this->output('office/invite/config');
		return true;
	}

	/**
	 * 配置时间戳转换成具体时间
	 * @param $time
	 * @param &$overdue
	 * @return bool
	 */
	private function __overdue_to_time($overdue, &$time) {
		if($overdue >= 86400){
			$time['tian'] = floor($overdue/86400);
			$overdue = ($overdue%86400);
		} else {
			$time['tian'] = 0;
		}
		if($overdue >= 3600){
			$time['shi'][0] = floor($overdue/3600);
			$overdue = ($overdue%3600);
		} else {
			$time['shi'][0] = 0;
		}
		if($overdue >= 60){
			$time['shi'][1] = floor($overdue/60);
			$overdue = ($overdue%60);
		}
		$time['seconds'] = floor($time);

		$time['shi'] = implode(':', $time['shi']);
		return true;
	}

	/**
	 * 具体时间转换成时间戳
	 * @param $overdue
	 * @param $time
	 * @return bool
	 */
	private function __time_to_overdue($time, &$overdue) {
		$time['shi'] = explode(':', $time['shi']);
		$overdue = $time['tian'] * 3600 * 24 + $time['shi'][0] * 3600 + $time['shi'][1] * 60; // 整合秒数
		return true;
	}

	/**
	 * 当是邀请设置提交
	 * 判断提交的数据和数据库数据是否有差别
	 * @param $setting_data // 缓存数据
	 * @param $post_data // 提交的数据
	 * @return bool
	 */
	private function __is_diff($setting_data, $post_data) {
		// 转换缓存的数据库时间数据为时间戳
		$time = '';
		$this->__time_to_overdue($setting_data['overdue'], $time);
		$setting_data['overdue'] = (string)$time;

		// 遍历 判断是否有更改的
		$diff_data = '';
		foreach ($post_data as $key => $val) {
			if (isset($setting_data[$key]) && $setting_data[$key] == $val) {
				continue;
			}
			$diff_data[$key] = $val;
		}

		// 如果变量为空，那么没有改动
		if (empty($diff_data)) {
			$this->_admincp_success_message('提交的数据没有改动', $this->cpurl($this->_module, $this->_operation, 'config', $this->_module_plugin_id));
		}

		// 更新数据
		$uda = &uda::factory('voa_uda_frontend_invite_updata');
		$update_invite = null;
		$uda->update_invite($diff_data, $update_invite);

		//强制更新
		if ($this->_module_plugin_id) {
			voa_h_cache::get_instance()->get('plugin.' . $this->_module_plugin['cp_identifier'] . '.setting', 'oa', true);
		} else {
			voa_h_cache::get_instance()->get('setting', 'oa', true);
		}

		$this->_admincp_success_message('更新成功！', $this->cpurl($this->_module, $this->_operation, 'config', $this->_module_plugin_id));

		return true;
	}
}
