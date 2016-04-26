<?php
/**
 * abstract.php
 * serive/超级报表/基类
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_s_oa_superreport_abstract extends voa_s_abstract {

	/** d层类 */
	protected $_d_class;

	/** 应用唯一标识名 */
	public $p_id = '';

	/** 应用设置信息 */
	public $p_sets = array();

	public function __construct() {

		parent::__construct();

		// 定义唯一标识名
		$this->p_id = 'common_place';

		// 当前应用的设置信息
		$this->p_sets = voa_h_cache::get_instance()->get($this->p_id.'_setting', 'oa');

	}

}
