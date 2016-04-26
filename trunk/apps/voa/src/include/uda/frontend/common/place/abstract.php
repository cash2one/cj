<?php
/**
 * voa_uda_frontend_common_place_abstract
 * 统一数据访问/场所/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_uda_frontend_common_place_abstract extends voa_uda_frontend_common_abstract {

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

	public function __construct($ptname = array()) {

		parent::__construct();

		// 读取公共设置
		$service_place = new voa_s_oa_common_place_abstract();
		$this->p_id = $service_place->p_id;
		$this->p_sets = $service_place->p_sets;
		$this->place_member_levels = $service_place->place_member_levels;
		$this->type_max_count = $service_place->type_max_count;
		$this->region_max_deepin = $service_place->region_max_deepin;

	}

}
