<?php
/**
 * voa_uda_frontend_showroom_abstract
 * 统一数据访问/陈列/基类
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_showroom_abstract extends voa_uda_frontend_base {

	public $d_class ;  //showroom目录下d层类
	/** 应用的唯一标识名 */
	public $plugin_identifier = '';
	/** 应用设置信息 */
	public $plugin_setting = array();
	/** 站点全局设置 */
	public $setting = array();

	public function __construct($ptname = array()) {

		parent::__construct();

		$this->_timestamp = startup_env::get('timestamp');

		// 如果未指定当前应用的唯一标识名，则自当前类提取判断
		if (!$this->plugin_identifier) {
			list(,,,$this->plugin_identifier) = explode('_', rstrtolower(__CLASS__));
		}

		// 当前应用的设置信息
		$this->plugin_setting = voa_h_cache::get_instance()->get('plugin.'.$this->plugin_identifier.'.setting', 'oa');

		// 站点全局配置
		$this->setting = voa_h_cache::get_instance()->get('setting', 'oa');

		//获取应用配置
		$d_showroom_setting = new voa_d_oa_showroom_setting();
		foreach ($d_showroom_setting->list_all() as $s) {
			if ($d_showroom_setting::TYPE_ARRAY == $s['type']) {
				$this->plugin_setting[$s['key']] = unserialize($s['value']);
			} else {
				$this->plugin_setting[$s['key']] = $s['value'];
			}
		}

	}

	/**
	 * 找到指定用户所关联的部门ID
	 * @param number $m_uid 用户id
	 * @return array $ids 部门ID
	 */
	public  function get_department_id($m_uid) {

		$department = new voa_d_oa_member_department();
		$ids = $department->fetch_all_by_uid($m_uid);

		$all = $this->get_all_departments($ids);
		$new = array();
		$new = array_flip(array_flip($all));
		if (!empty($new)) {
			foreach ($new as $k => $v) {
				if ($v ==0){
					unset($new[$k]);
				}
			}
		}

		return $new;
	}

	public function get_all_departments($cd_ids) {

		$d_departments = new voa_d_oa_common_department();
		$departments = $d_departments->fetch_all();
		$departments_ids = array_column($departments, 'cd_upid', 'cd_id');
		$all = $cd_ids;
		$this->__get_parents($cd_ids, $departments_ids, $all);

		return $all;
	}

	private function __get_parents($cd_ids, $departments_ids, &$all){
		$temp = array();
		$temp = array_intersect_key($departments_ids,$cd_ids);
		if (!empty($temp)){
			$all = array_merge($all, $temp);
			self::__get_parents(array_flip($temp), $departments_ids, $all);
		}
	}

}

