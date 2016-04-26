<?php
/**
 * voa_c_api_minutes_get_list
 * 会议纪要列表
 * $Author$
 * $Id$
 */

class voa_c_api_minutes_get_list extends voa_c_api_minutes_base {
	/** 当前查询的数据起始行 */
	protected $_start;
	/** 数据总数 */
	protected $_total;
	/** 数据列表 */
	protected $_list;
	/** 最后更新时间 */
	protected $_updated = 0;

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

		if ($this->_params['limit'] < 1) {
			// 设定每页数据条数的默认值
			$this->_params['limit'] = 10;
		}

		// 获取分页参数
		list($this->_start, $this->_params['limit'], $this->_params['page']) = voa_h_func::get_limit($this->_params['page'], $this->_params['limit'], 100);

		// 更新时间
		$this->_updated = startup_env::get('timestamp') + 10;
		
		/** 搜索条件 */
		$this->_so_conditions($conditions);


		/** 读取内容 */
		$serv = &service::factory('voa_s_oa_minutes_mem', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->fetch_by_search(startup_env::get('wbs_uid'), $conditions, $this->_start, $this->_params['limit']);
		
		/** 取出 mi_id */
		$mi_ids = array();
		foreach ($list as $v) {
			$mi_ids[] = $v['mi_id'];
		}

		$fmt = &uda::factory('voa_uda_frontend_minutes_format');
		/** 读取报告内容 */
		$serv = &service::factory('voa_s_oa_minutes', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->fetch_by_ids($mi_ids);
		$total = $serv->count_by_conditions(array('mi_id'=>array($mi_ids, 'in')));
		/** 整理输出 */
		foreach ($list as &$v) {
			$fmt->minutes($v);
			$v['_created_fmt'] = voa_h_func::date_fmt('y m d w', $v['mi_created']);
			list($v['_ymd'], $v['_hi']) = explode(' ', $v['_created']);
			$this->_updated = $v['mi_updated'];
		}
		// 整理json数据
		foreach ($list as $_mi_id => $_p) {
			$data[] = array(
				'id' => $_mi_id,// 任务ID
				'uid' => $_p['m_uid'],// 创建者uid
				'username' => $_p['m_username'],// 创建者名字
				'avatar' => voa_h_user::avatar($_p['m_uid']),//头像
				'subject' => $_p['mi_subject'],// 会议记录主题 
				'created' => $_p['mi_created'],// 会议记录时间
			);
		}
		// 输出结果
		$this->_result = array(
			'total' => $total,
			'limit' => $this->_params['limit'],
			'page' => $this->_params['page'],
			'data' => $data
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
		$report_time = rstrtotime($this->_sotext);
		if (0 < $report_time) {
			$conditions['reporttime'] = floor($report_time / 86400) * 86400;
			return false;
		}

		$conditions['username'] = '%'.$this->_sotext.'%';
		
		return true;
	}
}
