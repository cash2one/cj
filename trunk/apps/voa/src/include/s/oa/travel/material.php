<?php
/**
 * voa_s_oa_travel_material
 * 素材
 *
 * $Author$
 * $Id$
 */

class voa_s_oa_travel_material extends voa_s_abstract {


	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 格式化素材信息
	 * @param array $material 素材信息
	 * @return boolean
	 */
	public function format(&$material) {

		$material['_created'] = rgmdate($material['created']);
		$material['_updated'] = rgmdate($material['updated']);
		return true;
	}

}
