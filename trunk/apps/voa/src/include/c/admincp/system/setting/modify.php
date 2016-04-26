<?php
/**
 * voa_c_admincp_system_setting_modify
 * 企业后台 - 系统设置 - 全局系统环境设置 - 修改
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_c_admincp_system_setting_modify extends voa_c_admincp_system_setting_base {

	/** 公司简称最大长度 */
	private $__shortname_max_length = 12;

	public function execute() {

		/** 定义变量设置数组 */
		/**$this->_current_keys_setting = array(

			'sitename' => array(
				'type' => 'string',
				'id' => 'sitename',
				'title' => '公司名称',
				//'comment' => '公司的完整名称，暂不可修改'
			),
			'domain' => array(
				'type' => 'string',
				'id' => 'domain',
				'title' => '域名',
				//'comment' => '网页版本访问地址'
			),
			'shortname' => array(
				'type' => 'text',
				'id' => 'shortname',
				'name' => 'shortname',
				'placeholder' => '公司简称',
				'comment' => '公司简称，不能超过'.$this->__shortname_max_length.'个字符',
				'title' => '公司简称'
			)
		);*/

		// 更新配置
		if ($this->_is_post()) {
			$sitename = $this->request->post('sitename');
			$shortname = $this->request->post('shortname');

			// 判断公司名称长度
			$sitename = trim($sitename);
			if (50 < strlen($sitename)) {
				$this->message('error', '公司名称字数不能大于 25 个汉字');
				return true;
			}

			// 判断公司简称长度
			$shortname = trim($shortname);
			if (36 < strlen($shortname)) {
				$this->message('error', '公司检查字数不能大于 12 个汉字');
				return true;
			}

			// 如果 0 < ep_id
			if (!empty($this->_setting['ep_id'])) {
				// 通知 ucenter
				$rpc = voa_h_rpc::phprpc(config::get('voa.uc_url').'OaRpc/Rpc/Enterprise');
				if (!$rpc->update_by_ep_id($this->_setting['ep_id'], array('ep_name' => $sitename))) {
					logger::error('uc=>ep_id:'.$this->_setting['ep_id'].'; ep_name:'.$sitename);
				}

				// 通知总后台
				$rpc = voa_h_rpc::phprpc(config::get('voa.cyadmin_url').'OaRpc/Rpc/Enterprise');
				if (!$rpc->update_by_ep_id($this->_setting['ep_id'], array('ep_name' => $sitename))) {
					logger::error('cyadmin=>ep_id:'.$this->_setting['ep_id'].'; ep_name:'.$sitename);
				}
			}

			// 更新配置信息
			$serv = &service::factory('voa_s_oa_common_setting');
			$serv->update_setting(array(
				'sitename' => $sitename,
				'appname' => $sitename,
				'shortname' => $shortname
			));

			// 更新缓存
			voa_h_cache::get_instance()->get('setting', 'oa', true);

			$this->message('success', '配置更新成功', $this->cpurl($this->_module, $this->_operation, $this->_subop, ''), false);
			return true;
		}

		$this->view->set('setting', $this->_setting);
		$this->output('system/setting/modify');
	}

	/**
	 * 检查common变量值
	 */
	protected function _validator_setting_value(){

		if (isset($this->_current_change_data['dateformat'])) {
			$this->_current_change_data['dateformat'] = trim($this->_current_change_data['dateformat']);
			if (!preg_match('/^[dmnjy\-]+$/i', $this->_current_change_data['dateformat'])) {
				$this->message('error', '请正确设置日期格式，如：Y-m-d');
			}
			if ($this->_current_change_data['dateformat'] != rhtmlspecialchars($this->_current_change_data['dateformat'])) {
				$this->message('error', '日期格式内禁止包含特殊字符');
			}
			$this->_current_change_data['dateformat'] =
				str_ireplace(array('mm', 'dd', 'yyyy', 'yy'), array('n', 'j', 'Y', 'y'), $this->_current_change_data['dateformat']);
		}
		if (isset($this->_current_change_data['timeformat'])) {
			$this->_current_change_data['timeformat'] = trim($this->_current_change_data['timeformat']);
			if (!preg_match('/^[his\:]+$/i', $this->_current_change_data['timeformat'])) {
				$this->message('error', '请正确设置时间格式，如：H:i');
			}
			$this->_current_change_data['timeformat'] = str_ireplace(
					array('hh', 'ii', 'ss', 'HH'), array('H', 'i', 's', 'H'), $this->_current_change_data['timeformat']);
		}
		/*
		if (isset($this->_current_change_data['sitename'])) {
			$this->_current_change_data['sitename'] = trim($this->_current_change_data['sitename']);
			if (!validator::is_len_in_range($this->_current_change_data['sitename'], 2, 100)) {
				$this->message('error', '网站名称长度应该介于2到100字节之间');
			}
		}
		*/

		if (isset($this->_current_change_data['shortname'])) {
			$this->_current_change_data['shortname'] = trim($this->_current_change_data['shortname']);
			if (!validator::is_string_count_in_range($this->_current_change_data['shortname'], 0, $this->__shortname_max_length)) {
				$this->message('error', '公司简称长度不能超过 '.$this->__shortname_max_length.'个字符');
			}
		}

		// 不可修改这些配置
		unset($this->_current_change_data['sitename'], $this->_current_change_data['domain']);

		/*
		if (isset($this->_current_change_data['ep_wxqy'])) {
			$this->_current_change_data['ep_wxqy'] = $this->_current_change_data['ep_wxqy'] ? 1 : 0;
		}

		if ((isset($this->_current_change_data['ep_wxqy']) && $this->_current_change_data['ep_wxqy']) || !empty($this->_current_keys_setting['ep_wxqy']['value'])) {
			// 当前提交设置启用了企业微信号 或 历史设置已经启用了企业号
			// 则检查用户是否填写 corp_id 和  corp_secret

			if (empty($this->_current_keys_setting['corp_id']['value']) && empty($this->_current_change_data['corp_id'])) {
				$this->message('error', '如果启用企业微信，则微信 corp_id 必须填写');
			}
			if (empty($this->_current_keys_setting['corp_secret']['value']) && empty($this->_current_change_data['corp_secret'])) {
				$this->message('error', '如果启用企业微信，则微信 corp_secret 必须填写');
			}
		}
		*/
	}

}
