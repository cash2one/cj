<?php
/**
 * 备忘列表
 * $Author$
 * $Id$
 */

class voa_c_api_vnote_get_list extends voa_c_api_vnote_base {
	protected $_start;
	protected $_perpage;
	protected $_page;
	protected $_updated;
	protected $_sotext;

	public function execute() {

		// 需要的参数
		$fields = array(
			// 当前页码
			'page' => array('type' => 'int', 'required' => false),
			// 每页显示数据数
			'limit' => array('type' => 'int', 'required' => false),
		);
		if (!$this->_check_params($fields)) {
			// 检查参数
			return false;
		}

		if ($this->_params['page'] < 1) {
			// 设定当前页码的默认值
			$this->_params['page'] = 1;
		}
		if ($this->_params['limit'] < 20) {
			// 设定每页数据条数的默认值
			$this->_params['limit'] = 20;
		}

		/** 获取分页参数 */
		$page = $this->request->get('page');
		list(
			$this->_start, $this->_perpage, $this->_page
		) = voa_h_func::get_limit($this->_params['page'], $this->_params['limit']);


		/** 更新时间 */
		$this->_updated = intval($this->request->get('updated'));
		$this->_updated = empty($this->_updated) ? (startup_env::get('timestamp') + 86400) : $this->_updated;

		$this->_sotext = (string)$this->request->get('sotext');
		$this->_sotext = trim($this->_sotext);

		/** 搜索条件 */
		$conditions = array('updated' => $this->_updated);
		$this->_so_conditions($conditions);

		/** 读取备忘内容 */
		$serv = &service::factory('voa_s_oa_vnote_mem', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->fetch_by_search(startup_env::get('wbs_uid'), $conditions, $this->_start, $this->_perpage);

		/** 取出 vn_id */
		$vn_ids = array();
		foreach ($list as $v) {
			$vn_ids[] = $v['vn_id'];
		}

		$uda = &uda::factory('voa_uda_frontend_vnote_format');
		/** 读取备忘内容 */
		$serv = &service::factory('voa_s_oa_vnote', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->fetch_by_ids($vn_ids);


		/** 整理输出 */
		$temp = array();
		foreach ($list as &$v) {
			$temp = array(
				'vn_id'	=> $v['vn_id'],
				'uid'	=> $v['m_uid'],
				'username'	=> $v['m_username'],
				'subject'	=> $v['vn_subject'],
				'status'	=> $v['vn_status'],
				'created'	=> $v['vn_created'],
				'updated'	=> $v['vn_updated'],
			);
		}
		$this->_total = $serv->count_by_conditions($conditions);

		// 输出结果
		$this->_result = array(
			'total' => $this->_total,
			'limit' => $this->_params['limit'],
			'page' => $this->_params['page'],
			'data' => $temp
		);
		return true;
	}

	/**
	 * 搜索条件
	 * @param array $conditions
	 * @return boolean
	 */
	protected function _so_conditions(&$conditions) {
		/** 判断是否为时间格式 */
		$vn_created = rstrtotime($this->_sotext);
		if (0 < $vn_created) {
			$conditions['vn_created'] = $vn_created;
			return true;
		}

		$conditions['username'] = '%'.$this->_sotext.'%';
		return true;
	}
}
