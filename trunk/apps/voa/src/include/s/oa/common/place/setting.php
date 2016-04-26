<?php
/**
 * setting.php
 * service/公共模块/场所管理/设置表
 * validator_xx 验证方法，出错抛错
 * format_xx 格式化方法，引用返回结果
 * get_xx 获取数据
 * list_xxx 读取数据列表相关
 * ... 其他自定方法
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_s_oa_common_place_setting extends voa_s_oa_common_place_abstract {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 获取场所设置
	 * @param boolean $update 是否强制读取更新
	 * @return array
	 */
	public function get_place_setting_cache($update = false) {

		return voa_h_cache::get_instance()->get($this->p_id.'_setting', 'oa', $update);
	}

}
