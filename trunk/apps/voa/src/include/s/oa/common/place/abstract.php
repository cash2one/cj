<?php
/**
 * abstract.php
 * serive/公共模块/场地管理/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_s_oa_common_place_abstract extends voa_s_abstract {

	/** 应用唯一标识名 */
	public $p_id = '';

	/** 应用设置信息 */
	public $p_sets = array();

	/** 场所相关人员级别值与默认(如果业务需要自定名称则使用用户自定)名称的映射关系 */
	public $place_member_levels = array();

	/** 最多允许创建类型数 */
	public $type_max_count = 0;

	/** 最多允许创建的分区级别深度 */
	public $region_max_deepin = 0;

	public function __construct() {

		parent::__construct();

		// 定义唯一标识名
		$this->p_id = 'common_place';

		// 当前应用的设置信息
		$this->p_sets = voa_h_cache::get_instance()->get($this->p_id.'_setting', 'oa');

		// 自应用配置中读取默认的相关人级别名称映射关系
		if (empty($this->place_member_levels) && isset($this->p_sets['level_default_name'])) {
			$this->place_member_levels = $this->p_sets['level_default_name'];
		}

		// 程序内置的默认级别与名称映射关系
		$_place_member_levels = array(
			voa_d_oa_common_place_member::LEVEL_CHARGE => '负责人',
			voa_d_oa_common_place_member::LEVEL_NORMAL => '相关人',
		);

		// 校验级别与名称映射关系是否合法，以避免一些特殊情况下的意外
		foreach ($_place_member_levels as $_level => $_name) {
			// 上面已经定义了该级别映射关系则忽略
			if (array_key_exists($_level, $this->place_member_levels)) {
				continue;
			}
			// 未定义级别映射关系，则使用程序默认的名称
			$this->place_member_levels[$_level] = $_name;
		}
		unset($_place_member_levels, $_level, $_name);

		// 类型最多允许创建数(默认遵从数据层配置)
		$this->type_max_count = voa_d_oa_common_place_type::DATA_MAX_TOTAL;
		if (!empty($this->p_sets['type_max_count'])) {
			$this->type_max_count = $this->p_sets['type_max_count'];
		}

		// 最多允许创建的分区深度(几级)
		$this->region_max_deepin = voa_d_oa_common_place_region::DEEPIN_MAX;
		if (!empty($this->p_sets['region_deepin_max'])) {
			$this->region_max_deepin = $this->p_sets['region_deepin_max'];
		}
	}

}
