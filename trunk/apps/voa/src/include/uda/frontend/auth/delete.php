<?php
/**
 * voa_uda_frontend_auth_delete
 * auth认证  删除
 * Created by zhoutao.
 * Created Time: 2015/7/10  10:57
 */

class voa_uda_frontend_auth_delete extends voa_uda_frontend_auth_base {

	public function delete_authcode($authcode, $out) {
		$this->auth_insert->delete_by_conds(array('authcode' => $authcode));
		return true;
	}



}
