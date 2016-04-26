<?php
/**
 * voa_c_admincp_setting_dailyreport_modify
 * 企业后台/系统设置/日报/更改设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_dailyreport_setting extends voa_c_admincp_setting_base {

    protected $_proc = "图片设置";
    protected $_comment = "类型设置";
    protected $_flag = false;

	public function execute() {

		/** 当前操作的数据表 */
		$this->_current_operation_table = 'dailyreport';

		/** 定义变量设置数组 */
		$this->_current_keys_setting = array(
			/*
			 'perpage' => array(
			 	'type' => 'number',
			 	'id' => 'perpage',
			 	'name' => 'perpage',
			 	'comment' => '每页显示的日报数量',
			 	'title' => '每页显示日报数',
			 	'max' => 30,
			 	'min' => 1
			 ),*/
			'upload_image' => array(
				'type' => 'yesorno',
				'id' => 'upload_image',
				'name' => 'upload_image',
				'comment' => '选择“是”则新建报告时允许上传图片，否则不允许。',
				'title' => '是否允许上传报告图片'
			),
			'upload_image_min_count' => array(
				'type' => 'number',
				'id' => 'upload_image_min_count',
				'name' => 'upload_image_min_count',
				'comment' => '如果设置非零的正整数（不能大于10），则要求员工必须至少上传该数量的图片。设置为“0”，则不做限制。',
				'title' => '要求至少上传的图片数'
			),
			'upload_image_max_count' => array(
				'type' => 'number',
				'id' => 'upload_image_max_count',
				'name' => 'upload_image_max_count',
				'comment' => '最多允许上传的图片数，请设置10以内的正整数',
				'title' => '最多允许上传的图片数'
			),
			'daily_type' => array(
				'type' => 'custom',
				'id' => 'daily_type',
				'name' => 'daily_type',
				'comment' => '日报类型',
				'title' => '日报类型'
			)
		);

		/** 以后动作交由 voa_c_admincp_setting_base->_after_action()方法来接管 */
		$p_sets = voa_h_cache::get_instance()->get('plugin.dailyreport.setting', 'oa');//读日报配置缓存
		if (empty($p_sets['daily_type'])) {
			voa_h_cache::get_instance()->get('plugin.dailyreport.setting', 'oa', true);
			$this->message('error', '工作报告类型不存在');
		}
		$this->view->set('dailyType', $p_sets['daily_type']);//日报类型数组
		/** 是否分tab显示*/
		$this->_flag = true;
		$this->view->set('flag', $this->_flag);//默认没有单独设置功能
		$this->view->set('proc', $this->_proc);//默认是图片设置
		$this->view->set('comment', $this->_comment);//默认类型设置
	}

	/**
	 * 验证变量值
	 */
	protected function _validator_setting_value() {
		$setting = $this->_current_keys_setting;
		if (isset($this->_current_change_data['perpage'])) {
			if (!validator::is_int($this->_current_change_data['perpage'])) {
				$this->message('error', $setting['perpage']['title'].' 必须为大于零的整数');
			}
			if ($this->_current_change_data['perpage'] < $setting['perpage']['min'] || $this->_current_change_data['perpage'] > $setting['perpage']['max']) {
				$this->message('error', $setting['perpage']['title'].' 应该设置为'.$setting['perpage']['min'].'到'.$setting['perpage']['max'].'之间的整数');
			}
		}

		if (isset($this->_current_change_data['upload_image'])) {
			$this->_current_change_data['upload_image'] = $this->_current_change_data['upload_image'] ? 1 : 0;
		}

		if (isset($this->_current_change_data['upload_image_max_count'])) {
			$this->_current_change_data['upload_image_max_count'] = (int)$this->_current_change_data['upload_image_max_count'];
			if ($this->_current_change_data['upload_image_max_count'] > 10 || $this->_current_change_data['upload_image_max_count'] <= 0) {
				$this->_current_change_data['upload_image_max_count'] = 10;
			}
		}

		if (isset($this->_current_change_data['upload_image_min_count'])) {
			$this->_current_change_data['upload_image_min_count'] = (int)$this->_current_change_data['upload_image_min_count'];
			if ($this->_current_change_data['upload_image_min_count'] > 10 || $this->_current_change_data['upload_image_min_count'] < 0) {
				$this->_current_change_data['upload_image_min_count'] = 0;
			}
			if (isset($this->_current_change_data['upload_image_max_count']) && $this->_current_change_data['upload_image_max_count'] < $this->_current_change_data['upload_image_min_count']) {
				$this->_current_change_data['upload_image_min_count'] = 0;
			}
		}
	}

}
