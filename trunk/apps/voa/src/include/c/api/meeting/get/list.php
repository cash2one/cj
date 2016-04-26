<?php
/**
 * voa_c_api_meeting_get_list
 * 会议列表
 * $Author$
 * $Id$
 */
class voa_c_api_meeting_get_list extends voa_c_api_meeting_base {

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
			$this->_params['action'] = 'join';
		}
		/**
		 * 动作集合
		 * fin: 已结束
		 * join: 待参加
		 */
		$acs = array('fin', 'join');
		if (!in_array($this->_params['action'], $acs)) {
			/*设置默认动作为 我的申请列表*/
			return $this->_set_errcode(voa_errcode_api_meeting::LIST_UNDEFINED_ACTION, $this->_params['action']);
		}

		/*获取分页参数*/
		list($this->_start, $this->_params['limit'], $this->_params['page']) = voa_h_func::get_limit($this->_params['page'], $this->_params['limit'], 100);

		/*更新时间*/
		$this->_updated = startup_env::get('timestamp') + 10;

		/*调用处理方法*/
		$list = array();
		$func = '_'.$this->_params['action'];
		if (!method_exists($this, $func)) {
			return $this->_set_errcode(voa_errcode_api_meeting::LIST_UNDEFINED_FUNCTION, $this->_params['action']);
		}

		/*参与人表*/
		$this->_serv = &service::factory('voa_s_oa_meeting_mem', array('pluginid' => $this->_plugin['cp_pluginid']));

		/*呼叫对应动作方法*/
		call_user_func(array($this, $func));
		/*整理数据*/
		$data = array();
		foreach ($this->_list as $_mt_id => $_p) {
			//获取个人状态
			$mem = $this->_serv->fetch_by_mt_id_uid($_p['mt_id'], $this->_member['m_uid']);
			if(!$mem) {
				continue;
			}
			if($mem['confirm']) {
				$status = '已签到';
				$class = 'ui-icon-approve02';
			}else{
				if($mem['mm_status'] == voa_d_oa_meeting_mem::STATUS_CONFIRM) {
					$status = '已确认';
					$class = 'ui-icon-approve02';
				}else if($mem['mm_status'] == voa_d_oa_meeting_mem::STATUS_ABSENCE) {
					$status = '不参加';
					$class = 'ui-icon-approve04';
				}else if($mem['mm_status'] == voa_d_oa_meeting_mem::STATUS_NORMAL ) {
					$status = '未确认';
					$class = '';
				}
			}

			$data[] = array(
				'id' => $_p['mt_id'],// 会议ID
				'uid' => $_p['m_uid'],// 创建者uid
				'username' => $_p['m_username'],// 创建者名字
				'subject' => $_p['mt_subject'],// 主题
				'message' => $_p['mt_message'],// 主题
				'mr_id' => $_p['mr_id'],
				'mr_name' => $this->_rooms[$_p['mr_id']]['mr_name'],// 会议室
				'begintime' => $_p['mt_begintime'],// 会议时间
				'endtime' => $_p['mt_endtime'],// 会议时间
				'y' => rgmdate($_p['mt_begintime'], 'y'),
				'm' => rgmdate($_p['mt_begintime'], 'm'),
				'd' => rgmdate($_p['mt_begintime'], 'j'),
				'bthi' => rgmdate($_p['mt_begintime'], 'H:i'),
				'endhi' => rgmdate($_p['mt_endtime'], 'H:i'),
				'room'	=>	$this->_rooms[$_p['mr_id']]['mr_name'],
				'_mem_status' => $status,
				'_class' => $class,
			);
		}

		/*输出结果*/
		$this->_result = array(
			//'total' => $this->_total,
			'limit' => $this->_params['limit'],
			'page' => $this->_params['page'],
			'list' => $data ? array_values($data) : array()
		);

		return true;
	}

	/** 读取已结束的列表 */
	public function _fin() {
		/** 根据参会人读取会议信息列表 */
		$d = new voa_d_oa_meeting2();
		$rs = $d->fin_list(startup_env::get('wbs_uid'), $this->_start, $this->_params['limit']);
		$this->_list = $rs['list'];
		$this->_total = $rs['total'];
	}

	/** 读取待参加的列表 */
	public function _join() {
		/** 根据参会人读取会议信息列表 */
		$d = new voa_d_oa_meeting2();
		$rs = $d->join_list(startup_env::get('wbs_uid'), $this->_start, $this->_params['limit']);
		$this->_list = $rs['list'];
		$this->_total = $rs['total'];
	}
}
