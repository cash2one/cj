<?php
/**
 * voa_uda_frontend_askoff_search
 * 统一数据访问/日报应用/构造搜索条件
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_askoff_search extends voa_uda_frontend_askoff_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 搜索进程表的时间范围，只允许搜索此定义时间之后内的数据
	 * (单位：天)
	 * @var number
	 */
	public $aopc_search_expire_days = 90;

	/**
	 * 构造供数据库检索时的条件
	 * @param array $search_default_fields 默认搜索字段以及空值，只为检查是否为搜索条件，使用时输出可另行设置默认显示值
	 * @param array $search_by 构造输入的搜索条件的显示数组
	 * @param array $conditions 供数据库检索时的条件数组
	 * @param array $shard_key 应用id信息
	 * @return boolean
	 */
	public function askoff_conditions($search_default_fields, &$search_by, &$conditions, $shard_key) {

		/**
		 * $search_default_fields
		 * 默认空值：
		 * $searchDefaults = array(
				'm_uid' => 0,
				'ao_username' => '',//请假人姓名
				'ao_type' => '-1',//请假类型
				'ao_begintime' => '',//请假开始时间
				'ao_endtime' => '',//请假结束时间
				'aopc_username' => '',//审批人姓名
				'ao_status' =>'-1',//审批状态
				'ao_subject' => '',//请假关键词
			);
		 */

		// 搜索条件的原型输入
		$search_by_new = array();

		// 初始化供数据查询用的字段条件
		$conditions = array();

		foreach ($search_default_fields as $_k => $_v) {
			if (!isset($_GET[$_k])) {
				continue;
			}

			$v = $this->_request->get($_k);
			// 如果搜索值与初始化的字段值一致 或 不是一个标量
			if ($_v == $v || !is_scalar($v)) {
				continue;
			}

			$v = trim($v);
			$search_by_new[$_k] = $v;

			if (strpos($_k, 'aopc_') === 0) {
				// 搜索进程表数据

				if ($v == '') {
					continue;
				}

				if ($_k == 'aopc_username') {
					//搜索审批、抄送人姓名

					// 找到此时间之后的审批信息，默认只允许 90天的数据
					$time_after = startup_env::get('timestamp') - 86400 * $this->aopc_search_expire_days;
					$search_proc = array();
					$search_proc['aopc_created'] = array($time_after, '>=');
					$search_proc['m_username'] = array('%'.addcslashes($v, '%_').'%', 'like');

					$serv_proc = &service::factory('voa_s_oa_askoff_proc', $shard_key);
					$proc_list = $serv_proc->fetch_by_conditions($search_proc);

					if (empty($proc_list)) {
						$conditions['ao_id'] = 0;
					} else {
						if (!isset($conditions['ao_id'])) {
							$conditions['ao_id'] = array();
						}

						$tmp = array();
						// 通过审批人找到请假id
						foreach ($proc_list as $_data) {
							if (!isset($conditions['ao_id'][$_data['ao_id']])) {
								$tmp[$_data['ao_id']] = $_data['ao_id'];
							}
						}

						$conditions['ao_id'] = array($tmp);
						unset($_data);
					}

					unset($time_after, $search_proc, $proc_list);
				}
			} elseif ($_k == 'ao_begintime' || $_k == 'ao_endtime') {
				// 搜索时间范围
				if ($v && validator::is_date($v)) {
					$_v_time = rstrtotime($v);
					if ($_k == 'oa_endtime') {
						$_v_time = $_v_time + 86400;
					}

					if ($_k == 'ao_begintime') {
						$conditions['ao_begintime'] = array($_v_time, '>=');
					} elseif ($_k == 'ao_endtime') {
						$conditions['ao_endtime'] = array($_v_time, '<');
					}
				}

			} elseif ($_k == 'ao_username') {
				// 搜索请假人
				if ($v != '') {
					$conditions['m_username'] = array('%'.addcslashes($v, '%_').'%', 'like');
				}
			} elseif ($_k == 'ao_subject') {
				// 搜索标题
				if ($v != '') {
					$conditions['ao_subject'] = array(
							'%'.addcslashes($v, '%_').'%',
							'like'
					);
				}
			} elseif ($_k == 'ao_status') {
				// 搜索请假状态
				if ($v > 0) {
					if (isset($this->askoff_status[$v])) {
						$conditions['ao_status'] = $v;
					}
				}
			} elseif ($_k == 'ao_type') {
				// 搜索请假类型
				if ($v > 0 && isset($this->_sets['types']) && isset($this->_sets['types'][$v])) {
					$conditions['ao_type'] = $v;
				}
			} else{
				// 其他条件
				$conditions[$_k] = $v;
			}
		}

		// 当前搜索的条件，用于显示给页面的搜索输入
		$search_by = array_merge($search_default_fields, $search_by_new);

		return true;
	}

}
