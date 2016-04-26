<?php
/**
 * voa_c_api_dailyreport_get_list
 * 日报列表
 * $Author$
 * $Id$
 */
class voa_c_api_dailyreport_get_list extends voa_c_api_dailyreport_base {

	/** 当前查询的数据起始行 */
	protected $_start;
	/** 数据总数 */
	protected $_total;
	/** 数据列表 */
	protected $_list;
	/** 日报表 */
	protected $_serv;
	/** 最后更新时间 */
	protected $_updated = 0;
	/** 搜索字段 */
	protected $_sotext = '';
    // 搜索开始时间
    private  $_starttime;
    // 搜索结束时间
    private  $_endtime;
    // 报告类型
    private  $_type;
    // 用户id
    private  $_uid;

	public function execute() {
		/*需要的参数*/
		$fields = array(
			/*当前页码*/
			'page' => array('type' => 'int', 'required' => false),
			/*每页显示数据数*/
			'limit' => array('type' => 'int', 'required' => false),
			/*列表搜索关键字*/
			'keyword' => array('type' => 'string', 'required' => false),
			/*读取的列表类型*/
			'action' => array('type' => 'string', 'required' => false),
            // 列表搜索起始时间
            'starttime' => array('type'=>'string','required'=>false),
            // 列表搜索结束时间
            'endtime' => array('type'=>'string','required'=>false),
            // 报告类型
            'type' => array('type' => 'int', 'required' => false),
            // 用户id
            'uid' => array('type' => 'int', 'required' => false),
		);
		if (!$this->_check_params($fields)) {
			/*检查参数*/
			return false;
		}

		/** 获取分页参数 */
		$page = $this->request->get('page');
		$limit = $this->request->get('limit');
		if (!$limit || 20 > $limit) {
			$limit = 20;
		}
		list(
			$this->_start, $this->_perpage, $this->_page
		) = voa_h_func::get_limit($page, $limit);

		//关键字搜索
		$this->_sotext = (string)$this->request->get('keyword');
		$this->_sotext = trim($this->_sotext);

		$acs = array('recv', 'mine');
		$ac = (string)$this->request->get('action');
		if (!in_array($ac, $acs)) {
			$ac = 'all';
		}
        // 搜索开始起时与结束时间
        $this->_starttime = rstrtotime($this->request->get('starttime'));
        $this->_endtime = rstrtotime($this->request->get('endtime'));
        // 报告类型
        $this->_type = $this->request->get('type');
        // 用户id
        $this->_uid = $this->request->get('uid');

		/** 搜索条件 */
		$conditions = array('ac' => $ac);
		$this->_so_conditions($conditions);
		/** 读取报告内容 及总数*/
		$serv = &service::factory('voa_s_oa_dailyreport_mem', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->fetch_by_search(startup_env::get('wbs_uid'), $conditions, $this->_start, $this->_perpage);
		$count = $serv->count_by_search(startup_env::get('wbs_uid'), $conditions);
        // 统计未读数
        $read_count = $serv->count_by_read(startup_env::get('wbs_uid'));

		/** 取出 dr_id */
		$dr_ids = array();

        foreach ($list as $v) {
            $dr_ids[] = $v['dr_id'];
        }
        // 如果action为,recv,去获取日报的阅读状态
        if ('recv' === $ac) {

            // 根据日报id,查询阅读状态
            $read_status = $serv->fetch_by_read($dr_ids,startup_env::get('wbs_uid'));
            $read = array();

            // 反转阅读状态数据
            foreach ($read_status as $key => $val) {

                $read[$val['dr_id']] = $val['is_read'];
            }
        }

		$uda = &uda::factory('voa_uda_frontend_dailyreport_format');
		/** 读取报告内容 */
		$serv = &service::factory('voa_s_oa_dailyreport', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->fetch_by_ids($dr_ids);

		//获取用户ID
		$mems = array();
        if ($list) {
            foreach ($list as $key => $row) {
                $mems[$row['m_uid']] = $row['m_uid'];

                // 如果,action是recv的,则去获取,日报的阅读状态

                if ('recv' === $ac) {

                    // 获取日报id，相应的阅读状态
                    $list[$key]['is_read'] = isset($read[$key]) ? $read[$key] : '2';
                } else {

                    $list[$key]['is_read'] = 2;
                }

            }
        }
		/** 用户头像信息 */
		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$users = $servm->fetch_all_by_ids(array_keys($mems));
		voa_h_user::push($users);

		/** 整理输出 */
		$posts = $uda->format_posts($list);
		foreach ($posts as &$v) {
			$v['avatar'] = voa_h_user::avatar($v['uid'], isset($users[$v['uid']]) ? $users[$v['uid']] : array());

		}
		unset($v);
		/*输出结果*/
        header('Content-type: application/json');
		$this->_result = array(
			'total' => $count,
			'limit' => $this->_params['limit'],
            'unread' => $read_count,
			'page' => $this->_params['page'],
			'data' => $posts ? array_values($posts) : array()
		);

		return true;
	}

	/**
	 * 搜索条件
	 * @param array $conditions
	 * @return boolean
	 */
	protected function _so_conditions(&$conditions) {
// 		if ($this->_sotext) {
			/** 判断是否为时间格式 */
// 			$report_time = rstrtotime($this->_sotext);
// 			if (0 < $report_time) {
// 				$datetime = explode(' ',$this->_sotext);
// 				$conditions['reporttime'] = rstrtotime($datetime[0]);
// 				return false;
// 			}

// 			if($this->_sotext != 0 || empty($this->_sotext)){
// 		        $conditions['dr_type'] = $this->_sotext;
// 		    }
// 		}

		//搜索用户姓名
		if (!empty($this->_sotext)) {
			$conditions['username'] = $this->_sotext;
		}

        // 搜索开始时间
        if (!empty($this->_starttime)) {
            $conditions['starttime'] = $this->_starttime;
        }

        // 搜索结束时间
        if (!empty($this->_endtime)) {
            $conditions['endtime'] = $this->_endtime + 86400;
        }

        // 报告类型
        if(!empty($this->_type)){
            $conditions['type'] = $this->_type;
        }

        // 用户id
        if ($this->_uid) {
            $conditions['uid'] = $this->_uid;
        }

		return true;
	}

}
