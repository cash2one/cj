<?php
/**
 * 通讯录
 * Author: Arice
 * $Id$
 */

class voa_c_frontend_addressbook_base extends voa_c_frontend_base {

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}

		$this->view->set('navtitle', '通讯录');
		return true;
	}

	// 获取插件信息
	protected function _get_plugin() {

		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.addressbook.setting', 'oa');

		/** 取应用插件信息 */
		$pluginid = $this->_p_sets['pluginid'];
		startup_env::set('pluginid', $pluginid);
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		// 如果应用信息不存在
		if (!array_key_exists($pluginid, $plugins)) {
			$this->_error_message('应用信息丢失，请重新开启');
			return true;
		}

		// 获取应用信息
		$this->_plugin = $plugins[$pluginid];

		// 判断应用是否关闭
		if ($this->_plugin['cp_available'] != voa_d_oa_common_plugin::AVAILABLE_OPEN) {
			$this->_error_message('本应用尚未开启 或 已关闭，请联系管理员启用后使用');
			return true;
		}

		startup_env::set('agentid', $this->_plugin['cp_agentid']);
		/** 加载提示语言 */
		language::load_lang($this->_plugin['cp_identifier']);

		return true;
	}

	/**
	 * 获取请假配置
	 */
	public static function fetch_cache_addressbook_setting() {

		$serv = &service::factory('voa_s_oa_addressbook_setting', array('pluginid' => startup_env::get('pluginid')));
		$data = $serv->fetch_all();
		$arr = array();
		foreach ($data as $v) {
			if (voa_d_oa_common_setting::TYPE_ARRAY == $v['abs_type']) {
				$arr[$v['abs_key']] = unserialize($v['abs_value']);
			} else {
				$arr[$v['abs_key']] = $v['abs_value'];
			}
		}

		self::_check_agentid($arr, 'addressbook');
		return $arr;
	}

	/**
	 * 显示操作菜单
	 * @param array $data 数据数组
	 * @param array $plugin 插件信息
	 */
	public static function show_menu($data, $plugin) {

		$serv = voa_wxqy_service::instance();

		/** 先读取用户信息 */
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$member = $serv_m->fetch_by_openid($data['from_user_name']);
		if (empty($member)) {
			return '无此权限';
		}

		/** 搜索条件 */
		$conditions = array(
			'ms_message' => array('%'.$data['content'].'%', 'like')
		);
		/** 根据用户名搜索通讯录信息 */
		$serv_addrso = &service::factory('voa_s_oa_member_search', array('pluginid' => 0));
		/** 读取总数 */
		$count = $serv_addrso->count_by_conditions($conditions);

		$viewurl = $serv->oauth_url_base(voa_h_func::get_agent_url('/addressbook/list?sotext='.$data['content'], $plugin['cp_pluginid']));
		if (0 == $count) {
			return '没有找到相关联系人';
		} elseif (1 < $count) {
			return '找到'.$count."个联系人\n <a href=\"".$viewurl."\">点击查看详情</a>";
		}

		/** 从搜索表取通讯录数据 */
		$list = $serv_addrso->fetch_by_conditions($conditions);
		$cur_addr = current($list);

		/** 取用户信息 */
		$serv_addr = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$addr = $serv_addr->fetch($cur_addr['m_uid']);

		/** 取用户其他信息 */
		$serv_mf = &service::factory('voa_s_oa_member_field');
		$mem_mf = $serv_mf->fetch_by_id($cur_addr['m_uid']);

		/** 获取部门信息 */
		$departments = voa_h_cache::get_instance()->get('department', 'oa');
		/** 获取职位信息 */
		$jobs = voa_h_cache::get_instance()->get('job', 'oa');

		$viewurl = $serv->oauth_url_base(voa_h_func::get_agent_url('/addressbook/show/'.$addr['m_uid'], $plugin['cp_pluginid']));
		$ret = '姓名：'.$addr['m_username']."\n"
			 . '部门：'.$departments[$addr['cd_id']]['cd_name']."\n"
			 . '职位：'.$jobs[$addr['cj_id']]['cj_name']."\n"
			 . '手机：'.$addr['m_mobilephone']."\n"
			 //. '座机：'.$mem_mf['mf_telephone']."\n"
			 . '邮箱：'.$addr['m_email']."\n\n"
			 . '<a href="'.$viewurl.'">点击查看详情</a>';

		return $ret;
	}
}
