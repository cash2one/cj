<?php
/**
 * 名片夹
 * Author: Arice
 * $Id$
 */

class voa_c_frontend_namecard_base extends voa_c_frontend_base {

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}

		$this->view->set('navtitle', '名片夹');

		return true;
	}

	// 获取插件信息
	protected function _get_plugin() {

		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.namecard.setting', 'oa');

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

	/** 获取公司信息 */
	protected function _get_company_id($name) {
		if (empty($name)) {
			return 0;
		}

		$serv = &service::factory('voa_s_oa_namecard_company', array('pluginid' => startup_env::get('pluginid')));
		$company = $serv->fetch_by_name($name);
		if (empty($company)) {
			$company = array(
				'm_uid' => startup_env::get('wbs_uid'),
				'ncc_name' => $name
			);
			$ncc_id = $serv->insert($company, true);
			$company['ncc_id'] = $ncc_id;
		}

		return $company['ncc_id'];
	}

	/** 获取职位信息 */
	protected function _get_job_id($name) {
		if (empty($name)) {
			return 0;
		}

		$serv = &service::factory('voa_s_oa_namecard_job', array('pluginid' => startup_env::get('pluginid')));
		$job = $serv->fetch_by_name($name);
		if (empty($job)) {
			$job = array(
				'm_uid' => startup_env::get('wbs_uid'),
				'ncj_name' => $name
			);
			$ncj_id = $serv->insert($job, true);
			$job['ncj_id'] = $ncj_id;
		}

		return $job['ncj_id'];
	}

	/**
	 * 获取名片夹配置
	 */
	public static function fetch_cache_namecard_setting() {

		$serv = &service::factory('voa_s_oa_namecard_setting', array('pluginid' => startup_env::get('pluginid')));
		$data = $serv->fetch_all();
		$arr = array();
		foreach ($data as $v) {
			if (voa_d_oa_common_setting::TYPE_ARRAY == $v['ncs_type']) {
				$arr[$v['ncs_key']] = unserialize($v['ncs_value']);
			} else {
				$arr[$v['ncs_key']] = $v['ncs_value'];
			}
		}

		self::_check_agentid($arr, 'namecard');
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

		/** 搜索条件 */
		$conditions = array(
			'm_uid' => $member['m_uid'],
			'ncso_message' => array('%'.$data['content'].'%', 'like')
		);
		/** 根据用户名搜索名片信息 */
		$serv_ncso = &service::factory('voa_s_oa_namecard_search', array('pluginid' => startup_env::get('pluginid')));
		/** 读取总数 */
		$count = $serv_ncso->count_by_conditions($conditions);

		$viewurl = $serv->oauth_url_base(voa_h_func::get_agent_url('/namecard/list?sotext='.$data['content'], $plugin['cp_pluginid']));
		if (0 == $count) {
			return '没有找到相关名片';
		} elseif (1 < $count) {
			return '找到'.$count."张名片\n <a href=\"".$viewurl."\">点击查看详情</a>";
		}

		/** 从搜索表取名片数据 */
		$list = $serv_ncso->fetch_by_conditions($conditions);
		$cur_nc = current($list);

		/** 取名片信息 */
		$serv_nc = &service::factory('voa_s_oa_namecard', array('pluginid' => startup_env::get('pluginid')));
		$namecard = $serv_nc->fetch_by_id($cur_nc['nc_id']);

		/** 如果填了公司, 则 */
		$serv_ncc = &service::factory('voa_s_oa_namecard_company', array('pluginid' => startup_env::get('pluginid')));
		$company = $serv_ncc->fetch_by_id($namecard['ncc_id']);

		$viewurl = $serv->oauth_url_base(voa_h_func::get_agent_url('/namecard/view/'.$namecard['nc_id'], $plugin['cp_pluginid']));
		$ret = $namecard['nc_realname']." 的名片\n"
			 . '手机：'.$namecard['nc_mobilephone']."\n"
			 . '座机：'.$namecard['nc_telephone']."\n"
			 . '邮箱：'.$namecard['nc_email']."\n"
			 . '公司：'.$company['ncc_name']."\n"
			 . '地址：'.$namecard['nc_address']."\n"
			 . ' <a href="'.$viewurl.'">点击查看详情</a>';

		return $ret;
	}

	/**
	 * 附件处理
	 * @param array $attachment 附件信息
	 * @param array $data 消息信息
	 * @param array $plugin 插件信息
	 */
	public static function wx_attach($attachment, $data, $plugin) {
		/** 取站点配置信息 */
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');

		/** 名片信息入库 */
		$serv_nc = &service::factory('voa_s_oa_namecard', array('pluginid' => startup_env::get('pluginid')));
		$nc_id = $serv_nc->insert(array(
			'at_id' => $attachment['at_id'],
			'nc_status' => voa_d_oa_namecard::STATUS_REMOVE
		), true);

		/** 微信消息 */
		$ret = "名片识别中, 请稍等";

		return $ret;
	}

}

