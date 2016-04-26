<?php
/**
 * @Author: ppker
 * @Date:   2015-10-20 18:08:50
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-10-21 13:45:00
 */

class voa_s_cyadmin_enterprise_dueread extends voa_s_abstract {

	public function get_dueread_data($uid, &$read_data) {

		$read_data = array(); // 返回的数据
		$d = new voa_d_cyadmin_enterprise_dueread(); 

		$conds = array('uid =?' => $uid);
		$read_data = $d->list_by_conds($conds);
		return true;
	}

}
