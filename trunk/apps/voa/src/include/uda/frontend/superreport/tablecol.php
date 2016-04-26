<?php
/**
 * template.php
 * 内部api方法/超级报表模板
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_uda_frontend_superreport_tablecol extends voa_uda_frontend_superreport_abstract {

	/** 外部请求参数 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** 类型 service 类 */
	private $__service = null;
	/** diy uda 类 */
	private $__diy = null;

	/**
	 * 初始化
	 */
	public function __construct() {
		parent::__construct();

		if ($this->__diy === null) {
			$this->__diy = new voa_uda_frontend_diy_column_list();
		}
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_superreport_template();
		}
	}

	/**
	 * 取得模板原始数据
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)月报信息数组
	 * @param array $options 其他额外的参数（扩展用）
	 * @return boolean
	 */
	public function get_raw_template(array $request, array &$result) {


		// 取得模板数据
		$this->_init_diy_data($this->__diy);  //设置选项
		$this->__diy->execute(array(),$result);
		if (!$result) {
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::NO_TEMPLATE_ERROR);
		}

		return true;
	}

	/**
	 * 更新当个表格列属性信息
	 * @param array $member 用户信息
	 * @param array $gp 数据
	 * @param int $tc_id 表格id
	 * @throws service_exception
	 * @return boolean
	 */
	public function update($params) {

		// 取得模板数据
		$tablecols = array();
		$diy = new voa_uda_frontend_diy_column_update();
		$this->_init_diy_data($diy);  //设置选项
		$diy->execute($params, $tablecols);

		return true;
	}

	/**
	 * 更新当个表格列属性信息
	 * @param array $member 用户信息
	 * @param array $gp 数据
	 * @param int $tc_id 表格id
	 * @throws service_exception
	 * @return boolean
	 */
	public function add($params, &$tablecols) {

		// 添加模板数据
		$diy = new voa_uda_frontend_diy_column_add();
		$this->_init_diy_data($diy);  //设置选项
		$diy->execute($params, $tablecols);

		return true;
	}

	/**
	 * 删除表格列属性信息
	 * @param int $tc_id 产品属性ID
	 * @throws service_exception
	 * @return boolean
	 */
	public function delete($tc_id) {

		$params['tc_id'] = $tc_id;
		// 取得模板数据
		$tablecols = array();
		$diy = new voa_uda_frontend_diy_column_delete();
		$this->_init_diy_data($diy);  //设置选项
		$diy->execute($params, $tablecols);

		return true;
	}

	public function init_tablecol($stc_id, &$list){

		// 取得模板数据
		$this->_init_diy_data($this->__diy);  //设置选项
		$this->__diy->execute(array(),$list);
		//删除模板数据
		if ($list) {
			$result = array();
			$diy_delete = new voa_uda_frontend_diy_column_delete();
			$this->_init_diy_data($diy_delete);  //设置选项
			foreach ($list as $v) {
				$diy_delete->execute(array('tc_id' => $v['tc_id']), $result);
			}
		}

		//将新选择的模板或空白模板加入到DIY的列中
		$diy_add = new voa_uda_frontend_diy_column_add();
		$this->_init_diy_data($diy_add);  //设置选项
		if ($stc_id) { //如果选择的是模板,将模板数据加入DIY列中
			$s_template = new voa_s_oa_superreport_template();
			$templates = $s_template->get_template($stc_id);
			if ($templates) {
				$tablecols = array();
				foreach ($templates as $template) {
					$diy_add->execute($template, $tablecols);
				}
			}
		} else { //如果是新建空白模板，将系统字段=加入到DIY列中
			$fileds = unserialize($this->plugin_setting['reserve_field']);
			if ($fileds) {
				$tablecols = array();
				foreach ($fileds as $filed) {
					$diy_add->execute($filed, $tablecols);
				}
			}
		}

		$this->_init_diy_data($this->__diy);  //设置选项
		$this->__diy->execute(array(),$list);

		return true;
	}

}
