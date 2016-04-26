<?php
/**
 * SubscribeService.class.php
 * $author$
 */

namespace UcRpc\Service;
use Common\Common\Wxqy\Service;
use Common\Common\Wxqy\Addrbook;
use Think\Log;

class SubscribeService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	// 更新用户关注状态
	public function update_subscribe() {

		// 初始化
		$serv = &Service::instance();
		$serv_ab = new Addrbook($serv);
		// 读取用户列表
		if (!$serv_ab->user_list($userlist)) {
			Log::record('read addressbook error.');
			return false;
		}

		// 遍历列表, 获取所有 openid
		$serv_mem = D('Common/Member');
		$st2openids = array();
		$openid2avatar = array();
		foreach ($userlist['userlist'] as $_u) {
			// 如果没有该状态值的数据, 则初始化
			if (!isset($st2openids[$_u['status']])) {
				$st2openids[$_u['status']] = array();
			}

			$st2openids[$_u['status']][] = $_u['userid'];
			// 如果没有头像, 则忽略
			if (empty($_u['avatar']) || !preg_match('/^http\s?\:\/\//i', $_u['avatar'])) {
				continue;
			}

			// 小图片头像
			$avatar = preg_replace('/\/\d+$/i', '/', $_u['avatar']);
			if ('/' != substr($avatar, -1)) {
				$avatar .= '/';
			}
			$avatar .= '64';
			$openid2avatar[$_u['userid']] = $avatar;
		}

		// 遍历, 开始更新
		foreach ($st2openids as $_st => $_ids) {
			// 把 openid 切成多个数组, 每个数组 200 个
			$chunks = array_chunk($_ids, 200);
			foreach ($chunks as $_openids) {
				$serv_mem->change_qywxstatus_by_openid($_openids, $_st);
			}
		}

		// 开始更新头像
		foreach ($openid2avatar as $_openid => $_avatar) {
			$serv_mem->update_by_openid($_openid, array('m_face' => $_avatar));
		}

		return true;
	}

}
