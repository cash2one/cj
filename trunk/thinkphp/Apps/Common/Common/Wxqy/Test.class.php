<?php
/**
 * Created by PhpStorm.
 * User: gaoyaqiu
 * Date: 15/11/22
 * Time: 20:50
 */

namespace Common\Common\Wxqy;

class Test extends Base {

	public function convert($userid) {

		self::convert_to_openid_for_enterprise($userid);
	}

}
