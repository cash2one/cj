<?php
/**
 * voa_uda_frontend_reimburse_search
 * 统一数据访问/报销/搜索
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_reimburse_search extends voa_uda_frontend_reimburse_base {

	/** controller_request 实例 */
	protected $_request;

	public function __construct() {
		parent::__construct();
		$this->_request = controller_request::get_instance();
	}

	/**
	 * 构造供数据表（reimburse）检索时的条件
	 * @param array $search_default_fields 默认搜索字段以及空值，只为检查是否为搜索条件，使用时输出可另行设置默认显示值
	 * @param array $search_by 构造输入的搜索条件的显示数组
	 * @param array $conditions 供数据库检索时的条件数组
	 * @param array $shard_key 应用id信息
	 * @return boolean
	 */
	public function reimburse_conditions($search_default_fields, &$search_by, &$conditions, $shard_key = array()) {

		// 搜索条件的原型输入
		$search_by_new = array();

		// 初始化供数据查询用的字段条件
		$conditions = array();

		/**
		 * $search_default_fields
		 * 默认空值：
		 * $search_default_fields = array(
		 * 'm_uid' => '',//申请人id
		 * 'm_username' => '',//申请人用户名
		 * 'rbpc_username' => '',//审核人
		 * 'rb_subject' => '',//报销主题.
		 * 'rb_type' => '',//报销分类.
		 * 'rb_time_after' => '',//申请时间范围：此时间之后.
		 * 'rb_time_before' => '',//申请时间范围：此时间之前.
		 * 'rb_status' => '',//审批状态
		 );
		*/
		$find_rb_ids = array();
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

			if ($_k == 'rb_time_after' || $_k == 'rb_time_before') {
				// 按时间搜索

				if ($v && validator::is_date($v)) {
					$_v_time = rstrtotime($v);
					if ($_k == 'rb_time_after') {
						$conditions['rb_time'] = array($_v_time, '>=');
					} elseif ($_k == 'endtime') {
						$conditions['rb_time'] = array($_v_time + 86400, '<');
					}
				}

			} elseif ($_k == 'rb_subject') {
				// 搜索标题或内容

				if ($v != '') {
					$conditions[$_k] = array(
							'%'.addcslashes($v, '%_').'%',
							'like'
					);
				}

			} elseif ($_k == 'rb_type') {
				// 搜索类型

				if ($v != '' && isset($this->_sets['types'][$v])) {
					$conditions[$_k] = $v;
				}

			} elseif ($_k == 'm_username') {
				// 搜索申请人

				if ($v != '') {
					$conditions['m_username'] = array('%'.addcslashes($v, '%_').'%', 'like');
				}

			} elseif ($_k == 'rbpc_username') {
				// 搜索审核人

				if ($v != '') {
					$serv_proc = &service::factory('voa_s_oa_reimburse_proc', array('pluginid' => startup_env::get('pluginid')));
					$proc_list = $serv_proc->fetch_by_conditions(array('m_username' => array('%'.addcslashes($v, '%_').'%', 'like')));
					if (empty($proc_list)) {
						$find_rb_ids = array(0);
					} else {
						foreach ($proc_list as $_proc) {
							if (!isset($find_rb_ids[$_proc['rb_id']])) {
								$find_rb_ids[$_proc['rb_id']] = $_proc['rb_id'];
							}
						}
					}
				}

			} else{
				// 其他条件

				$conditions[$_k] = $v;
			}
		}
		if (!empty($find_rb_ids)) {
			$conditions['rb_id'] = array($find_rb_ids);
		}
		if ($conditions) {
			$conditions['stp_status'] = array(voa_d_oa_reimburse::STATUS_REMOVE, '<');
		}

		// 当前搜索的条件，用于显示给页面的搜索输入
		$search_by = array_merge($search_default_fields, $search_by_new);

		return true;
	}

}
