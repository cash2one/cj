<?php
/**
 * 名片列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_namecard_list extends voa_c_frontend_namecard_base {

	public function execute() {
		$uda = uda::factory('voa_uda_frontend_namecard_format');

		/** 取搜索条件 */
		$sotext = (string)$this->request->get('sotext');

		/** 读取名片列表 */
		$serv_n = &service::factory('voa_s_oa_namecard', array('pluginid' => startup_env::get('pluginid')));
		if (empty($sotext)) {
			$list = $serv_n->fetch_mine_by_uid(startup_env::get('wbs_uid'), $sotext);
		} else {
			$serv_ncso = &service::factory('voa_s_oa_namecard_search', array('pluginid' => startup_env::get('pluginid')));

			/** 搜索条件 */
			$conditions = array(
				'm_uid' => startup_env::get('wbs_uid'),
				'ncso_message' => array('%'.$sotext.'%', 'like')
			);

			$list_so = $serv_ncso->fetch_by_conditions($conditions);
			/** 取出 nc_id */
			$nc_ids = array();
			foreach ($list_so as $v) {
				$nc_ids[] = $v['nc_id'];
			}

			$list = $serv_n->fetch_by_ids($nc_ids);
		}

		if (!$uda->namecard_list($list)) {
			$this->_error_message($uda->error);
			return false;
		}

		/** 读取群组信息 */
		$serv_f = &service::factory('voa_s_oa_namecard_folder', array('pluginid' => startup_env::get('pluginid')));
		$ncfs = $serv_f->fetch_by_uid(startup_env::get('wbs_uid'));
		$ncfs[0] = array('ncf_id' => 0, 'ncf_name' => '默认分组', 'ncf_created' => 0);
		if (!$uda->folder_list($ncfs)) {
			$this->_error_message($uda->error);
			return false;
		}

		/** 读取公司信息 */
		$serv_c = &service::factory('voa_s_oa_namecard_company', array('pluginid' => startup_env::get('pluginid')));
		$nccs = $serv_c->fetch_by_uid(startup_env::get('wbs_uid'));
		if (!$uda->company_list($nccs)) {
			$this->_error_message($uda->error);
			return false;
		}

		/** 读取职位信息 */
		$serv_j = &service::factory('voa_s_oa_namecard_job', array('pluginid' => startup_env::get('pluginid')));
		$ncjs = $serv_j->fetch_by_uid(startup_env::get('wbs_uid'));
		if (!$uda->job_list($ncjs)) {
			$this->_error_message($uda->error);
			return false;
		}

		/** 根据条件重新组织数据 */
		$acs = array('created', 'folder', 'ascii');
		$ac = (string)$this->request->get('ac');
		if (!in_array($ac, $acs)) {
			$ac = 'folder';
		}

		$func = '_order_by_'.$ac;
		if (!method_exists($this, $func)) {
			$this->_error_message('undefine_action');
			return false;
		}

		$this->$func($list, $ncfs);

		$this->_set_dept_job();
		$this->view->set('ncfs', $ncfs);
		$this->view->set('ncjs', $ncjs);
		$this->view->set('nccs', $nccs);
		$this->view->set('list', $list);
		$this->view->set('ac', $ac);
		$this->view->set('sotext', $sotext);
		$this->view->set('ct_folder', $serv_f->count_by_uid(startup_env::get('wbs_uid')));
		$this->view->set('ct_namecard', $serv_n->count_by_uid(startup_env::get('wbs_uid')));

		$tpl = 'namecard/list_'.$ac;
		$this->_output($tpl);
	}

	/**
	 * 按创建时间显示
	 * @param array $list 列表
	 * @param array $ncfs 分组数组
	 * @return boolean
	 */
	protected function _order_by_created(&$list, $ncfs) {
		foreach ($list as $k => &$v) {
			if (!array_key_exists($v['ncf_id'], $ncfs)) {
				$v['ncf_id'] = 0;
			}
		}

		return true;
	}

	/**
	 * 按分组显示
	 * @param array $list 列表
	 * @param array $ncfs 分组数组
	 * @return boolean
	 */
	protected function _order_by_folder(&$list, $ncfs) {
		$arr = $list;
		/** 按群组id整理数据 */
		$list = array();
		foreach ($arr as $v) {
			if (empty($list[$v['ncf_id']])) {
				$list[$v['ncf_id']] = array();
			}

			if (!array_key_exists($v['ncf_id'], $ncfs)) {
				$v['ncf_id'] = 0;
			}

			$list[$v['ncf_id']][] = $v;
		}

		return true;
	}

	/**
	 * 按字母排序显示
	 * @param array $list 列表
	 * @param array $ncfs 分组数组
	 * @return boolean
	 */
	protected function _order_by_ascii(&$list, $ncfs) {
		$arr = $list;
		$asciis = array();
		$list = array();
		foreach ($arr as $k => $v) {
			if (!array_key_exists($v['ncf_id'], $ncfs)) {
				$v['ncf_id'] = 0;
			}

			$first = empty($v['nc_pinyin']) ? '0' : rstrtoupper($v['nc_pinyin']{0});
			if (empty($list[$first])) {
				$asciis[] = $first;
				$list[$first] = array();
			}

			$list[$first][] = $v;
		}

		$this->view->set('ascii_range', implode(',', $asciis));
		return true;
	}
}


