<?php
/**
 * voa_uda_frontend_minutes_search
 * 统一数据访问/会议记录应用/搜索
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_minutes_search extends voa_uda_frontend_minutes_base {

	/**
	 * 设置搜索minutes_mem表限制时间，只搜索此时间内的数据
	 * (单位：天)
	 * @var number
	 */
	public $mim_search_expire_days = 90;

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 构造供数据库检索时的条件
	 * @param array $search_default_fields 默认搜索字段以及空值，只为检查是否为搜索条件，使用时输出可另行设置默认显示值
	 * @param array $search_by 构造输入的搜索条件的显示数组
	 * @param array $conditions 供数据库检索时的条件数组
	 * @param array $shard_key 应用id信息
	 * @return boolean
	 */
	public function minutes_conditions($search_default_fields, &$search_by, &$conditions, $shard_key = array()) {

		// 搜索条件的原型输入
		$search_by_new = array();

		// 初始化供数据查询用的字段条件
		$conditions = array();

		/**
		 * $search_default_fields
		 * 默认空值：
		 * $searchDefaults = array(
			 'm_uid' => 0,
			 'mi_username' => '',//会议记录发起人姓名
			 'begintime' => '',//发起时间范围：开始时间
			 'endtime' => '',//发起时间范围：结束时间
			 'mim_username' => '',//审批人姓名
			 //'mi_status' =>'-1',//记录状态
			 'mi_subject' => '',//会议主题关键词
			 );
		 */

		foreach ($search_default_fields as $_k => $_v) {
			if (!isset($_GET[$_k])) {
				continue;
			}
			$v = $this->_request->get($_k);
			if ($_v == $v || !is_scalar($v)) {
				// 如果搜索值与初始化的字段值一致 或 不是一个标量
				continue;
			}
			$v = trim($v);
			$search_by_new[$_k] = $v;

			if (strpos($_k, 'mim_') === 0) {
				// 搜索进程表数据

				if ($v == '') {
					continue;
				}

				if ($_k == 'mim_username') {
					//搜索抄送人姓名

					// 找到此时间之后的审批信息，默认只允许 90天的数据
					$time_after = startup_env::get('timestamp') - 86400 * $this->mim_search_expire_days;
					$search_mem = array();
					$search_mem['mim_created'] = array($time_after, '>=');
					$search_mem['m_username'] = array('%'.addcslashes($v, '%_').'%', 'like');

					$serv_mem = &service::factory('voa_s_oa_minutes_mem', $shard_key);
					$mem_list = $serv_mem->fetch_by_conditions($search_mem);

					if (empty($mem_list)) {
						$conditions['mi_id'] = 0;
					} else {
						if (!isset($conditions['mi_id'])) {
							$conditions['mi_id'] = array();
						}
						$tmp = array();
						// 通过抄送人找到会议记录id
						foreach ($mem_list as $_data) {
							if (!isset($conditions['mi_id'][$_data['mi_id']])) {
								$tmp[$_data['mi_id']] = $_data['mi_id'];
							}
						}
						$conditions['mi_id'] = array($tmp);
						unset($_data);
					}
					unset($time_after, $search_mem, $mem_list);
				}

			} elseif ($_k == 'begintime' || $_k == 'endtime') {
				// 搜索时间范围

				if ($v && validator::is_date($v)) {
					$_v_time = rstrtotime($v);
					if ($_k == 'endtime') {
						$_v_time = $_v_time + 86400;
					}
					if ($_k == 'begintime') {
						$conditions['mi_created'] = array($_v_time, '>=');
					} elseif ($_k == 'endtime') {
						$conditions['mi_created'] = array($_v_time, '<');
					}
				}

			} elseif ($_k == 'mi_username') {
				// 搜索请假人

				if ($v != '') {
					$conditions['m_username'] = array(
							'%'.addcslashes($v, '%_').'%',
							'like'
					);
				}

			} elseif ($_k == 'mi_subject') {
				// 搜索标题

				if ($v != '') {
					$conditions['mi_subject'] = array(
							'%'.addcslashes($v, '%_').'%',
							'like'
					);
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
