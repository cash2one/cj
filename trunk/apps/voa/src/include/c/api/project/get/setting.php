<?php
/**
 * voa_c_api_project_get_setting
 * 获取任务应用的系统配置接口
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_project_get_setting extends voa_c_api_project_base {

	public function execute() {

		// 可选进度值
		$procvs = explode(',', $this->_p_sets['procvs']);
		if (!in_array('0', $procvs)) {
			// 不存在0则加入
			$procvs[] = '0';
		}
		if (!in_array('100', $procvs)) {
			// 不存在100则加入
			$procvs[] = '100';
		}
		$procvs = array_map('intval', $procvs);
		sort($procvs);
		///////////

		$this->_result = array(
			'procvs' => array(
				'name' => '可选进度值',
				'value' => $procvs
			),
			'upload_image' => array(
				'name' => '是否允许上传图片',
				'value' => (int)$this->_p_sets['upload_image']
			),
			'upload_image_max_count' => array(
				'name' => '最多允许上传的图片数',
				'value' => (int)$this->_p_sets['upload_image_max_count'],
			),
			'upload_image_min_count' => array(
				'name' => '最少要求上传的图片数',
				'value' => (int)$this->_p_sets['upload_image_min_count'],
			),
		);

	}

}
