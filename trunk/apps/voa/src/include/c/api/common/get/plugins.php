<?php
/**
 * voa_c_api_common_get_plugins
 * 获取权限菜单
 * $Author ppker
 * $Id$
 */

class voa_c_api_common_get_plugins extends voa_c_api_common_abstract {
	protected $_plugin_lists = null;
	
	/**
	 * 前端需要的数据
	 */
	protected $need_fields = array(
		'cp_pluginid',
		'cp_identifier',
		'cpg_id',
		'cp_name',
		'cp_icon',
		'cp_description',
		'cpg_name',
		'cpd_lastusetime'
	);

	/**
	 * [unset_fields description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function need_fields($data, &$list_data){
		foreach ($this->need_fields as $val) {
			$list_data[$val] = $data[$val];
		}
	}

	public function execute() {
		// 从缓存获取所有插件数据
		$this->_plugin_lists = voa_h_cache::get_instance()->get('plugin', 'oa');

		//var_dump($this->_plugin_lists);die;

		$plugin_display = &uda::factory('voa_uda_frontend_common_plugin_display');
		$plugin_group = &uda::factory('voa_uda_frontend_common_plugin_group');

		$my_plugins = null;
		$array_group = array();
		$m_uid = startup_env::get('wbs_uid');

		// 获取分组的数据
		$plugin_group = $plugin_group->get_list();
		if($plugin_group){
			$array_group = array_column($plugin_group, 'cpg_name', 'cpg_id');
		}
		// 查询
		$plugin_display->my_list_plugin($m_uid, $my_plugins);

		$my_que_data = array();
		$list = array();

		if($my_plugins && $plugin_display){
			// $array_pligin_id = array_column($my_plugins, 'cp_pluginid');
			foreach ($my_plugins as $key => $val) {
				if($this->_plugin_lists[$val['cp_pluginid']]) {
					// 增加数据
					$this->_plugin_lists[$val['cp_pluginid']]['cpd_lastusetime'] = $val['cpd_lastusetime'];
					$this->_plugin_lists[$val['cp_pluginid']]['cpg_name'] = $array_group[$this->_plugin_lists[$val['cp_pluginid']]['cpg_id']];
					
					// 组装数据给前端
					$qd_data = array();
					$this->need_fields($this->_plugin_lists[$val['cp_pluginid']], $qd_data);
					$my_que_data[] = $qd_data;
				}

			}
			
		}else{
			//$list = json_encode($this->_plugin_lists);
			$list = $this->_plugin_lists;
			foreach ($list as $k => &$v) {
				$v['cpg_name'] = $array_group[$v['cpg_id']];
				$v['cpd_lastusetime'] = 0;
				$qd_data = array();
				$this->need_fields($v, $qd_data);
				$my_que_data[] = $qd_data;

			}
		}

		$list = $my_que_data;
		/**
		 * 此处对数据再一次处理，方便前端人员
		 */
		$end_array = array(); // 接收数据组
		foreach ($plugin_group as $k1 => $v) {
			$temp = array();
			foreach ($list as $key => $val) {
				if($val['cpg_id'] == $k1){
					$temp['cpg_id'] = $k1;
					$temp['cpg_name'] = $val['cpg_name'];
					unset($val['cpg_id']);
					unset($val['cpg_name']);
					$temp['cp_data'][] = $val;
				}
			}
			if (!empty($temp)) {
				$end_array[] = $temp;
			}
		}
		//var_dump($end_array);die;
		$this->_result = empty($end_array) ? array() : $end_array;
		return true;
	}

}
