<?php
/**
 * voa_uda_frontend_superreport_abstract
 * 统一数据访问/超级报表/基类
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */

class voa_uda_frontend_superreport_abstract extends voa_uda_frontend_base {

	/** diy表 */
	public $table = null;
	/** diy列 */
	public $tablecol = null;

	public $member = array();

	public $plugin_setting = array();

	public function __construct($ptname = array()) {

		parent::__construct();

		if ($this->table == null) { //设置DIY表
			$this->table = 'superreport';
		}
		if ($this->tablecol == null) {  //设置DIY列
			// 取表格缓存
			$tables = voa_h_cache::get_instance()->get('diytable', 'oa');
			// 读取数据列表
			if (isset($tables[$this->table])) {
				$t = new voa_d_oa_diy_tablecol();
				$list = $t->list_by_tid($tables[$this->table]['tid'], array(), array('orderid' => 'desc'));
			}
			// 重新组合数据, 按 tid => array(tc_id => array(...))
			$ret = array();
			if (!empty($list)) {
				foreach ($list as $_v) {
					// 如果 field 为空
					/* if (empty($_v['field'])) {
						$_v['field'] = '_'.$_v['tc_id'];
					} */

					$ret[$_v['tc_id']] = $_v;
				}
			}

			$this->tablecol = $ret;
		}

		$this->plugin_setting = voa_h_cache::get_instance()->get('plugin.superreport.setting', 'oa');
	}

	/**
	 * 设置DIY表选项信息
	 */
	protected function  _init_diy_data($uda) {

		$uda->set_table($this->table);
		$uda->set_tablecols($this->tablecol);
		if (!empty($this->member)) {
			$uda->set_mem($this->member);
		}

	}

}
