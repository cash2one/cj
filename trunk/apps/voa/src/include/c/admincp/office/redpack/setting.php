<?php
/**
 * voa_c_admincp_office_redpack_setting
 * 红包-配置
 * Date: 15/3/9
 * Time: 上午10:42
 */


class voa_c_admincp_office_redpack_setting extends voa_c_admincp_office_redpack_base {

	public function execute() {

		// 如果是 post 提交
		if ($this->_is_post()) {
			$this->_update();
			return $this->_success_message('红包配置更新成功', '', '', false, $this->_self_url);
		}

		$default_users = array();
		if (!empty($this->_redpack_settings['privilege_uids'])) {
			$serv_m = &service::factory('voa_s_oa_member');
			$users = $serv_m->fetch_all_by_ids($this->_redpack_settings['privilege_uids']);
			foreach ($users as $_u) {
				$default_users[] = array(
					'id' => $_u['m_uid'],
					'name' => $_u['m_username'],
					'input_name' => 'm_uids[]'
				);
			}
		}

		$p_sets = $this->_redpack_settings;
		$p_sets['_redpack_min'] = number_format($p_sets['redpack_min'] / 100, 2);
		$p_sets['_redpack_max'] = number_format($p_sets['redpack_max'] / 100, 2);
		$this->view->set('p_sets', $p_sets);
		$this->view->set('default_users', $default_users);
		$this->output('office/redpack/setting');
	}

	// 更新配置
	protected function _update() {

		$default_sender_avatar = $this->request->get('default_sender_avatar');
		$default_sender_name = $this->request->get('default_sender_name');
		$redpack_min = $this->request->get('redpack_min') * 100;
		$redpack_max = $this->request->get('redpack_max') * 100;
		// 如果最小值 > 最大值, 则对调
		if ($redpack_min > $redpack_max) {
			list($redpack_min, $redpack_max) = array($redpack_max, $redpack_min);
		}

		$serv_set = &service::factory('voa_s_oa_redpack_setting');
		try {
			$serv_set->begin();
			// 红包范围入库
			if (100 <= $redpack_min && 20000 >= $redpack_min && 100 <= $redpack_max && 20000 >= $redpack_max) {
				$serv_set->update_setting(array(
					'redpack_min' => $redpack_min,
					'redpack_max' => $redpack_max
				));
			}

			// 发送者信息
			$serv_set->update_setting(array(
				'default_sender_avatar' => $default_sender_avatar,
				'default_sender_name' => $default_sender_name
			));

			// 更新权限用户
			$uids = $this->request->get('m_uids');
			$uids = empty($uids) ? array() : $uids;
			$serv_set->update_setting(array(
				'privilege_uids' => serialize($uids)
			));

			$redpack_id = $this->_redpack_settings['sign_redpack_id'];
			// 新创建签到红包
			$new_rp = (int)$this->request->get('new_rp');
			if (0 < $new_rp) {
				$uda_special = &uda::factory('voa_uda_frontend_redpack_addspecial');
				$redpack = array();
				$params = array(
					'min' => $redpack_min,
					'max' => $redpack_max,
					'uid' => $this->_user['ca_id'],
					'username' => $this->_setting['sitename']
				);
				if (!$uda_special->doit($params, $redpack)) {
					return $this->_error_message($uda_special->errmsg, '', '', false, $this->_self_url);
				}

				// 更新签到红包配置
				$redpack_id = $redpack['id'];
				$serv_set->update_setting(array('sign_redpack_id' => $redpack['id']));
				// 把之前的红包结束掉
				$serv_rp = &service::factory('voa_s_oa_redpack');
				// 设置过期时间
				$serv_rp->update($this->_redpack_settings['sign_redpack_id'], array('endtime' => 1));
			}

			$serv_rp = &service::factory('voa_s_oa_redpack');
			$serv_rp->update($redpack_id, array('min' => $redpack_min, 'max' => $redpack_max));
			$serv_set->commit();
		} catch (help_exception $e) {
			$serv_set->rollback();
			return $this->_error_message($e->getMessage(), '', '', false, $this->_self_url);
		} catch (Exception $e) {
			$serv_set->rollback();
			return $this->_error_message('服务器繁忙, 请稍候再试', '', '', false, $this->_self_url);
		}

		// 更新缓存
		voa_h_cache::get_instance()->get('plugin.redpack.setting', 'oa', true);
		return true;
	}

}
