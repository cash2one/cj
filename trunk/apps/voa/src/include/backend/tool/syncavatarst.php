<?php
/**
 * syncavatarst.php
 * 同步用户(关注状态)信息
 * @uses php tool.php -n syncavatarst
 * $Author$
 * $Id$
 */

class voa_backend_tool_syncavatarst extends voa_backend_base {
	/** 参数 */
	private $__opts = array();
	// 数据库连接
	protected $_db;

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
	}

	public function main() {

		startup_env::set('domain', 'carrefour');
		// 读取配置信息
		if (!voa_h_conf::init_db()) {
			exit('config file is missing.');
			return false;
		}

		$addr = &voa_wxqy_addressbook::instance();
		$result = array();
		if (!$addr->user_list($result)) {
			exit('read error');
			return false;
		}

		$serv = &service::factory('voa_s_oa_member');
		foreach ($result['userlist'] as $u) {
			$avatar = '';
			if ($u['avatar'] && preg_match('/^http\s?\:\/\//i', $u['avatar'])) {
				$avatar = preg_replace('/\/\d+$/i', '/', $u['avatar']);
				if ('/' != substr($avatar, -1)) {
					$avatar .= '/';
				}

				$avatar .= '64';
			}

			$serv->update_by_conditions(array(
				'm_weixin' => $u['weixinid'],
				'm_face' => $avatar,
				'm_qywxstatus' => $u['status']
			), array('m_openid' => $u['userid']));//echo $u['userid'];exit;
		}

		return true;
	}

}
