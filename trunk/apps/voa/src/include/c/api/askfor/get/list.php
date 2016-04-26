<?php
/**
 * voa_c_api_askfor_get_list
 * 审批列表
 * $Author$
 * $Id$
 */
class voa_c_api_askfor_get_list extends voa_c_api_askfor_base {

	/** 当前查询的数据起始行 */
	protected $_start;
	/** 数据总数 */
	protected $_total;
	/** 数据列表 */
	protected $_list;
	/** 审批表 */
	protected $_serv;
	/** 最后更新时间 */
	protected $_updated = 0;

	protected $_askfor_status_class = array(
		voa_d_oa_askfor::STATUS_NORMAL => '',
		voa_d_oa_askfor::STATUS_APPROVE_APPLY => 'ui-icon-approve01',
		voa_d_oa_askfor::STATUS_APPROVE => 'ui-icon-approve02',
		voa_d_oa_askfor::STATUS_REFUSE => 'ui-icon-approve03',
		voa_d_oa_askfor::STATUS_CANCEL => 'ui-icon-approve04',
	);

	public function execute() {

		// 需要的参数
		$fields = array(
			// 当前页码
			'page' => array('type' => 'int', 'required' => false),
			// 每页显示数据数
			'limit' => array('type' => 'int', 'required' => false),
			// 读取的列表类型
			'action' => array('type' => 'string', 'required' => false)
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

		if (empty($this->_params['action'])) {
			// 设置当前的动作
			$this->_params['action'] = 'askforing';
		}

		// 获取分页参数
		list($this->_start, $this->_params['limit'], $this->_params['page']) = voa_h_func::get_limit($this->_params['page'], $this->_params['limit'], 100);

		// 更新时间
		$this->_updated = startup_env::get('timestamp') + 10;

		// 调用处理方法
		$list = array();
		$func = '_'.$this->_params['action'];
		if (!method_exists($this, $func)) {
			return $this->_set_errcode(voa_errcode_api_askfor::LIST_UNDEFINED_FUNCTION, $this->_params['action']);
		}

		// 审批表
		$this->_serv = &service::factory('voa_s_oa_askfor', array('pluginid' => $this->_pluginid));

		// 呼叫对应动作方法
		$this->$func();

		// 整理数据
		$data = array();
		foreach ($this->_list as $_p) {
			$data[] = array(
				'af_id' => $_p['af_id'],
				'uid' => $_p['m_uid'],
				'subject' => rhtmlspecialchars($_p['af_subject']),
				'message' => rhtmlspecialchars($_p['af_message']),
				'status' => $_p['af_status'],
				'username' => rhtmlspecialchars($_p['m_username']),
				'afp_username' => rhtmlspecialchars($_p['afp_username']),
				'_created' => rgmdate($_p['af_created'], 'Y-m-d H:i'),
				'_status' => $this->_askfor_status_descriptions[$_p['af_status']],
				'_class' => $this->_askfor_status_class[$_p['af_status']],
				'avatar' => voa_h_user::avatar($_p['m_uid'])
			);
		}

		// 输出结果
		$this->_result = array(
	//		'total' => $this->_total,
			'limit' => $this->_params['limit'],
			'page' => $this->_params['page'],
			'list' => $data
		);
		return true;
	}

	/** 我发起的审批中的审批列表 */
	public function _askforing() {
		//审批表
		$this->_list = $this->_serv->fetch_my_askforing(startup_env::get('wbs_uid'), $this->_start, $this->_params['limit']);
		/* $this->_total = $this->_serv->count_my_by_conditions(startup_env::get('wbs_uid'), array(
			voa_d_oa_askfor::STATUS_NORMAL,
			voa_d_oa_askfor::STATUS_APPROVE_APPLY
		)); */
	}

	/** 我发起的已审批的审批列表 */
	public function _askfored() {
		//审批表
		$this->_list = $this->_serv->fetch_my_askfored(startup_env::get('wbs_uid'), $this->_start, $this->_params['limit']);
		//$this->_total = $this->_serv->count_my_by_conditions(startup_env::get('wbs_uid'), array(voa_d_oa_askfor::STATUS_APPROVE));
	}

	/** 我发起的被驳回和撤销的审批列表 */
	public function _refuse_cancel() {
		//审批表
		$this->_list = $this->_serv->fetch_my_refuse_cancel(startup_env::get('wbs_uid'), $this->_start, $this->_params['limit']);
		/* $conditions = array(
			'm_uid' => startup_env::get('wbs_uid'),
			'af_status' => array(voa_d_oa_askfor::STATUS_REFUSE, voa_d_oa_askfor::STATUS_CANCEL)
		);
		$this->_total = $this->_serv->count_my_by_conditions(startup_env::get('wbs_uid'), array(
			voa_d_oa_askfor::STATUS_REFUSE,
			voa_d_oa_askfor::STATUS_CANCEL
		)); */
	}

	/** 读取等待我批复的审批列表 */
	public function _approving() {
		//审批表
		$serv = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => $this->_pluginid));

        $temp_arr = array();
        $serv = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => $this->_pluginid));
        $serv_md = &service::factory('voa_s_oa_member_department');
        $uids = array('m_uid' => startup_env::get('wbs_uid'));
        $pos = $serv_md->fetch_by_conditions($uids);
        if (!empty($pos)) {
            $lists = $serv->fetch_by_conditions(array('mp_id' => $pos['mp_id']));
            if (!empty($lists)) {
                foreach ($lists as $v) {
                    if ($v['afp_status'] == voa_d_oa_askfor::STATUS_NORMAL) {
                        $temp_arr[$v['af_id']] = $this->_serv->fetch_by_id($v['af_id']);
                    }
                }
                foreach($temp_arr as $key => $val) {
                    $temp_arr[$key]['afp_username'] =$val['m_username'];
                }

            }
        }
        $temp_arr1 = $this->_serv->fetch_my_approving(startup_env::get('wbs_uid'), $this->_start, $this->_params['limit']);

        if (!empty($temp_arr)) {
            $list = array_merge($temp_arr1, $temp_arr);
        } else {
            $list = $temp_arr1;
        }

        /* 处理记录按照时间排序 */
        $times = array();
        foreach ($list as $v) {
            $times[] = $v['af_created'];
        }
        array_multisort($times, SORT_DESC, $list);

		$this->_list = $temp_arr1;
		//$this->_total = $serv->count_by_conditions(startup_env::get('wbs_uid'), array(voa_d_oa_askfor::STATUS_NORMAL));
	}

	/** 读取我已批复的审批列表 */
	public function _approved() {
		//审批表
        $temp_arr = array();
		$serv = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => $this->_pluginid));
        $serv_md = &service::factory('voa_s_oa_member_department');
        $uids = array('m_uid' => startup_env::get('wbs_uid'));
        $pos = $serv_md->fetch_by_conditions($uids);
        if (!empty($pos)) {
            $lists = $serv->fetch_by_conditions(array('mp_id' => $pos['mp_id']));
            if (!empty($lists)) {
                foreach ($lists as $v) {
                    if ($v['afp_status'] == voa_d_oa_askfor::STATUS_APPROVE_APPLY || $v['afp_status'] == voa_d_oa_askfor::STATUS_APPROVE) {
                        $temp_arr[$v['af_id']] = $this->_serv->fetch_by_id($v['af_id']);
                    }
                }
                foreach($temp_arr as $key => $val) {
                    $temp_arr[$key]['afp_username'] =$val['m_username'];
                }

            }
        }

        $temp_arr1 = $this->_serv->fetch_my_approved(startup_env::get('wbs_uid'), $this->_start, $this->_params['limit']);
        //if (!empty($temp_arr)) {
         ///   $list = array_merge($temp_arr1, $temp_arr);
        //} else {
         //   $list = $temp_arr1;
        //}

        /* 处理记录按照时间排序 */
        //$times = array();
        //foreach ($list as $v) {
          //  $times[] = $v['af_created'];
        //}
        //array_multisort($times, SORT_DESC, $list);

		$this->_list = $temp_arr1;

		/* $this->_total = $serv->count_by_conditions(startup_env::get('wbs_uid'), array(
			voa_d_oa_askfor::STATUS_APPROVE,
			voa_d_oa_askfor::STATUS_APPROVE_APPLY,
			voa_d_oa_askfor::STATUS_REFUSE
		)); */
	}
}
