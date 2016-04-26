<?php
/**
 * voa_c_admincp_office_vote_base
 * 企业后台/应用宝/微评选/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_vote_base extends voa_c_admincp_tool_base {

	/** 投票分享状态 */
	protected $_vote_friend = array(
			voa_d_oa_vote::FRIEND_ALL => '所有人',
			voa_d_oa_vote::FRIEND_ONLY => '指定人员',
	);

	/** 是否多选 */
	protected $_vote_ismulti = array(
			voa_d_oa_vote::IS_SINGLE => '否',
			voa_d_oa_vote::IS_MULTI => '是'
	);

	/** 是否开放 */
	protected $_vote_isopen = array(
			voa_d_oa_vote::IS_CLOSE => '否',
			voa_d_oa_vote::IS_OPEN => '是'
	);

	/** 投票范围：是否对外开放 */
	protected $_vote_inout = array(
			0 => '否',
			1 => '是'
	);

	/** 微评选设置 */
	protected $_sets = array();

	/***/
	protected $_set_vote_status = array();

	/** 投票状态 */
	protected $_vote_status = array(
			voa_d_oa_vote::STATUS_NORMAL => '申请中',
			voa_d_oa_vote::STATUS_APPROVE => '已通过',
			voa_d_oa_vote::STATUS_REFUSE => '已拒绝'
	);

	/** 允许使用的功能'v_ismulti', 'v_isopen', 'v_inout','v_minchoices','v_maxchoices' */
	//TODO 此处是为了应对未来开发需要，将不需要的功能（字段）从下面数组中移除
	protected $_vote_functions = array(
			'v_ismulti', 'v_isopen', 'v_inout','v_minchoices','v_maxchoices'
	);

	protected function _before_action($action) {

		/** 获取微评选缓存配置 */
		if (!parent::_before_action($action)) {
			return false;
		}

		$this->_sets = voa_h_cache::get_instance()->get('plugin.vote.setting', 'oa');
		$this->view->set('voteFunctions', $this->_vote_functions);
		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

	/**
	 * 格式化投票信息
	 * @param array $vote
	 * @return array
	 */
	protected function _format_vote($vote) {
		if (empty($this->_set_vote_status) && !empty($this->_sets['verify'])) {
			$vote_status = array();
			foreach ($this->_vote_status AS $_id => $_name) {
				$vote_status[$_id] = array(
						'name' => $_name,
						'url' => $this->cpurl($this->_module, $this->_operation, 'verify', $this->_module_plugin_id, array('set'=> $_id, 'v_id' => ''))
				);
			}
			$this->_set_vote_status = $vote_status;
			unset($vote_status);
		}
		$set_status_urls = array();
		if (!empty($this->_sets['verify'])) {
			foreach ($this->_set_vote_status AS $_id => $_data) {
				if ($_id == $vote['v_status']) {
					continue;
				}
				$set_status_urls[] = $this->linkShow($_data['url'], $vote['v_id'], $_data['name'], '', '');
			}
		}
		$vote['_set_status_urls'] = implode(' | ', $set_status_urls);
		$vote['_begintime'] = rgmdate($vote['v_begintime'], 'Y-m-d H:i');
		$vote['_endtime'] = rgmdate($vote['v_endtime'], 'Y-m-d H:i');
		$vote['_begintime_input'] = rgmdate($vote['v_begintime'], 'Y-m-d');
		$vote['_endtime_input'] = rgmdate($vote['v_endtime'], 'Y-m-d');
		$vote['_message'] = parent::_bbcode2html($vote['v_message']);
		$vote['_friend'] = isset($this->_vote_friend[$vote['v_friend']]) ? $this->_vote_friend[$vote['v_friend']] : '';
		$vote['_ismulti'] = isset($this->_vote_ismulti[$vote['v_ismulti']]) ? $this->_vote_ismulti[$vote['v_ismulti']] : '';
		$vote['_isopen'] = isset($this->_vote_isopen[$vote['v_isopen']]) ? $this->_vote_isopen[$vote['v_isopen']] : '';
		$vote['_inout'] = isset($this->_vote_inout[$vote['v_inout']]) ? $this->_vote_inout[$vote['v_inout']] : '';
		$vote['_status'] = isset($this->_vote_status[$vote['v_status']]) ? $this->_vote_status[$vote['v_status']] : '';
		$vote['_updated'] = rgmdate($vote['v_updated'], 'Y-m-d H:i');
		return $vote;
	}

	/**
	 * 获取指定投票的信息
	 * @param number $v_id
	 * @return array
	 */
	protected function _get_vote($cp_pluginid, $v_id) {
		$vote = $this->_service_single('vote', $cp_pluginid, 'fetch_by_id', $v_id);
		if (empty($vote)) {
			return false;
		}
		return $this->_format_vote($vote);
	}

	/**
	 * 列出指定投票的所有选项列表
	 * @param number $v_id
	 * @return array
	 */
	protected function _get_vote_option($cp_pluginid, $v_id) {
		$options = array();
		/** 总票数 */
		$votes = 0;
		$tmp = $this->_service_single('vote_option', $cp_pluginid, 'fetch_by_v_id', $v_id);
		foreach ($tmp AS $_id => $_data) {
			$votes = $votes + $_data['vo_votes'];
		}
		unset($_id, $_data);
		foreach ($tmp AS $_id => $_data) {
			$_data['_updated'] = rgmdate($_data['vo_updated'], 'Y-m-d H:i');
			$_data['_vote_rate'] = $votes > 0 ? round($_data['vo_votes']/$votes, 2) * 100 : 0;
			$options[$_id] = $_data;
		}
		unset($votes, $_id, $_data, $_tmp);
		return $options;
	}

	/**
	 * 列出指定投票所有允许投票的用户
	 * @param number $v_id
	 * @return array
	 */
	protected function _get_vote_permit_user($cp_pluginid, $v_id) {
		$users = array();
		$tmp = $this->_service_single('vote_permit_user', $cp_pluginid, 'fetch_by_v_id', $v_id);
		foreach ($tmp AS $_id => $_data) {
			$_data['_updated'] = rgmdate($_data['vpu_updated'], 'Y-m-d H:i');
			$users[$_data['m_uid']] = $_data;
		}
		unset($tmp);
		return $users;
	}

	/**
	 * 列出指定投票的投票记录
	 * @param number $v_id
	 * @param number $perpage
	 * @return array(total, multi, list)
	 */
	protected function _get_vote_mem($cp_pluginid, $v_id, $perpage = 10, $vote_options = array()) {
		$total = $this->_service_single('vote_mem', $cp_pluginid, 'count_by_v_id', $v_id);
		$multi = '';
		$list = array();
		if ($total > 0) {
			if (empty($vote_options)) {
				$vote_options = self::_get_vote_option($v_id);
			}
			$pagerOptions = array(
					'total_items' => $total,
					'per_page' => $perpage,
					'current_page' => $this->request->get('page'),
					'show_total_items' => true,
			);
			$multi = pager::make_links($pagerOptions);
			pager::resolve_options($pagerOptions);
			$tmp = $this->_service_single('vote_mem', $cp_pluginid, 'fetch_by_v_id', $v_id, $pagerOptions['start'], $pagerOptions['per_page']);
			foreach ($tmp AS $_id => $_data) {
				$_data['_updated'] = rgmdate($_data['vm_updated'], 'Y-m-d H:i');
				$_data['_option'] = isset($vote_options[$_data['vo_id']]) ? $vote_options[$_data['vo_id']]['vo_option'] : '';
				$list[$_id] = $_data;
			}
			unset($tmp);
		}
		return array($total, $multi, $list);
	}

}
