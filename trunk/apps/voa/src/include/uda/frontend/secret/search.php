<?php
/**
 * voa_uda_frontend_secret_search
 * 统一数据访问/秘密/搜索
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_secret_search extends voa_uda_frontend_secret_base {

	/** controller_request 实例 */
	private $_request;

	/**
	 * 设置搜索secret_mem表限制时间，只搜索此时间内的数据
	 * (单位：天)
	 * @var number
	 */
	public $mim_search_expire_days = 90;

	public function __construct() {
		parent::__construct();
		$this->_request = controller_request::get_instance();
	}

	/**
	 * 构造供数据表（secret_post）检索时的条件
	 * @param array $search_default_fields 默认搜索字段以及空值，只为检查是否为搜索条件，使用时输出可另行设置默认显示值
	 * @param array $search_by 构造输入的搜索条件的显示数组
	 * @param array $conditions 供数据库检索时的条件数组
	 * @param array $shard_key 应用id信息
	 * @return boolean
	 */
	public function secret_post_conditions($search_default_fields, &$search_by, &$conditions, $shard_key = array()) {

		// 搜索条件的原型输入
		$search_by_new = array();

		// 初始化供数据查询用的字段条件
		$conditions = array();

		/**
		 * $search_default_fields
		 * 默认空值：
		 * $search_default_fields = array(
		 'after' => '',//发表时间范围：此时间之后
		 'before' => '',//发表时间范围：此时间之前
		 'stp_subject' => '',//主题关键词
		 'stp_message' => '',//回复内容关键词
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

			if ($_k == 'after' || $_k == 'before') {
				// 按时间搜索

				if ($v && validator::is_date($v)) {
					$_v_time = rstrtotime($v);
					if ($_k == 'after') {
						$conditions['stp_created'] = array($_v_time, '>=');
					} elseif ($_k == 'endtime') {
						$conditions['stp_created'] = array($_v_time + 86400, '<');
					}
				}

			} elseif ($_k == 'stp_subject' || $_k == 'stp_message') {
				// 搜索标题或内容

				if ($v != '') {
					$conditions[$_k] = array(
							'%'.addcslashes($v, '%_').'%',
							'like'
					);
				}

			} else{
				// 其他条件

				$conditions[$_k] = $v;
			}
		}
		if ($conditions) {
			$conditions['stp_status'] = array(voa_d_oa_secret_post::STATUS_REMOVE, '<');
		}

		// 当前搜索的条件，用于显示给页面的搜索输入
		$search_by = array_merge($search_default_fields, $search_by_new);

		return true;
	}

}
