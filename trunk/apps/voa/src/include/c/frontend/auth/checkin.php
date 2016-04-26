<?php
/**
 * voa_c_frontend_auth_checkin
 * 验证auth登录
 * Created by zhoutao.
 * Created Time: 2015/7/5  8:59
 */

class voa_c_frontend_auth_checkin extends voa_c_frontend_auth_base {

	//不强制登录，允许外部访问
	protected function _before_action($action) {
		$this->_auto_login = true;
		if (!parent::_before_action($action)) {
			return false;
		}
		return true;
	}

    /** 解密后的时间戳 */
	private $__de_timestamp = '';

	/** 当前时间戳 */
	private $__timestamp = '';

	/** 密钥 */
	private $__secret_key = '';

	public function execute () {

		$getx = $this->request->getx();

		$singture = md5($getx['authcode'] . $getx['timestamp'] . config::get('voa.auth_key'));

		// 判断密钥是否正确
		if ($singture != $getx['singture']) {
			$this->_error_message('', null, null, null, '密钥错误！');
			return false;
		}

		// 判断密钥是否过期
		$this->__secret_key = config::get('voa.auth_key');
		$this->__de_timestamp = rbase64_decode($getx['timestamp']);
		$this->__de_timestamp = authcode($this->__de_timestamp, $this->__secret_key, 'DECODE', '');
		$this->__timestamp = startup_env::get('timestamp');
		if ($this->__timestamp - $this->__de_timestamp >= 900) {
			// 删除该密钥
			$delete = &uda::factory('voa_uda_frontend_auth_delete');
			$out = null;
			$delete->delete_authcode($getx['authcode'], $out);
			$this->_error_message('', null, null, null, '密钥已过期！');
			return false;
		}

		// 验证通过，更新状态
		$uda = &uda::factory('voa_uda_frontend_auth_update');
		$data = array(
			'm_uid' => (int)startup_env::get('wbs_uid'),
			'state' => (int)1,
			'authcode' => (string)$getx['authcode']
		);
		$out = null;
		$uda->check_update($data, $out);

/***gq***/
//获取用户信息
$member = &uda::factory('voa_uda_frontend_member_get');
$member->member_by_uid($data['m_uid'],$m_info);
//获取用户部门
$dep = &uda::factory('voa_uda_frontend_department_get');
$dep->department($m_info['cd_id'],$dep_info);
//p($dep_info);
//p($m_info);

$dep_name = '';
if(!empty($dep_info)) $dep_name = $dep_info['cd_name'];
$this->view->set('m_info',$m_info);
$this->view->set('dep_name',$dep_name);
/***gq***/

		$this->view->set('authcode', $getx['authcode']);
		// 这里的命名不能为tiamstamp，页面变量有冲突，会导致时间戳是流动的
		$this->view->set('auth_timestamp', $getx['timestamp']);
		$this->view->set('singture', $getx['singture']);
		$this->_output('auth/check');

		return true;
	}

}
