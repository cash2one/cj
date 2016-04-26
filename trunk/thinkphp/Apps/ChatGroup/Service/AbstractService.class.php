<?php
/**
 * AbstractService.class.php
 * $author$
 */

namespace ChatGroup\Service;

abstract class AbstractService extends \Common\Service\AbstractService {

	// 群聊最少人数
	const GROUP_MEMS_MIN = 3;

	// 构造方法
	public function __construct() {

		parent::__construct();

	}

	// 获取群聊最少人数
	public function get_group_mems_min() {

		return self::GROUP_MEMS_MIN;
	}

	/**
	 * 获得用户信息
	 * @param $uid 用户ID
	 * @return array 用户信息
	 */
	public function _get_user($uid) {

		$serv_m = D('Common/Member', 'Service');
		return $serv_m->get($uid);
	}

	/**
	 * 获得多个用户信息
	 * @param array $uids 用户ID
	 * @return array 用户信息
	 */
	public function _list_users($uids) {
		$uids = (array)$uids;
		//用户服务层
		$serv_m = D('Common/Member', 'Service');
		return $serv_m->list_by_pks($uids);
	}

	/**
	 * 获得附件
	 * @param $atids 附件ID
	 * @return array 附件
	 */
	public function _list_attachments($atids) {

		$atids = (array)$atids;
		// 附件服务层
		$serv_attachment = D('Common/CommonAttachment', 'Service');
		return $serv_attachment->list_by_pks($atids);
	}

	/**
	 * @param $atid
	 * @return mixed
	 */
	public function _get_attachment($atid){

		$serv_attachment=D('Common/CommonAttachment', 'Service');
		return $serv_attachment->get($atid);
	}

	/**
	 * 创建企业号群信息
	 * @param int $groupid chatgroup表id
	 * @param string $groupname 群组名称
	 * @param string $ownerid 所有者openid
	 * @param array $userlist 群用户列表
	 * @return boolean
	 */
	public function wxqy_create($chatid, $groupname, $ownerid, $userlist) {

		// 如果未达到 3 人, 则不创建群组
		if (self::GROUP_MEMS_MIN > count($userlist)) {
			return true;
		}

		// 初始化企业号 service
		$serv_wx = &\Common\Common\Wxqy\Service::instance();
		// 初始化聊天类接口
		$chat = new \Com\Chat($serv_wx);
		// 群信息
		$chatinfo = array(
			'chatid' => $chatid,
			'name' => $groupname,
			'owner' => $ownerid,
			'userlist' => $userlist
		);
		// 调用创建接口
		if (!$chat->create($chatinfo)) {
			\Think\Log::record('chat create fail:' . var_export($chatinfo, true));
		}

		return true;
	}

	/**
	 * 发送微信消息
	 * @param string $chatid 群聊id
	 * @param array $record 聊天信息
	 * @param string $from 发送者openid
	 * @return boolean
	 */
	public function wxqy_send($chatid, $record, $from) {

		// 读取群信息
		$d = D("ChatGroup/Chatgroup");
		$chatgroup = $d->get_by_chatid($chatid);

		// 初始化企业号 service
		$serv_wx = &\Common\Common\Wxqy\Service::instance();
		// 初始化聊天接口
		$chat = new \Com\Chat($serv_wx);
		// 如果是群聊, 则发群消息
		if ($d->get_type_chatgroup() == $chatgroup['cg_type']) {
			// 生成聊天群组id
			$to = $chatid;
			// 获取群组类型标识
			$chattype = $chat->get_chat_type_group();
		} else {
			// 获取组成员
			$group_mem = D("ChatGroup/ChatgroupMember");
			$users = $group_mem->list_member_by_cgid($chatgroup['cg_id']);
			// 遍历群组成员
			foreach ($users as $_u) {
				// 如果不是发送者, 则说明是聊天对象
				if ($_u['m_openid'] != $from) {
					$to = $_u['m_openid'];
					break;
				}
			}

			// 单聊标识
			$chattype = $chat->get_chat_type_single();
		}

		// 解析聊天消息换行
		if (!empty($record['cgr_content'])) {
			$msg = preg_replace('/\<div(.*?)\>(.*?)\<\/div\>/i', "\n\\2\n", $record['cgr_content']);
			$msg = str_replace("\n\n", "\n", $msg);
			$msg = str_replace('&nbsp;', ' ', $msg);
			$msg = preg_replace('/<\/?([a-zA-Z]+)[^>]*>/i', "", $msg);
			$msgtype = $chat->get_msg_type_text();
		} else { // 附件
			// 读取附件
			$attachment = $this->_get_attachment($record['at_id']);
			// 上传
			$serv = &\Common\Common\Wxqy\Service::instance();
			$serv_media = new \Common\Common\Wxqy\Media($serv);
			$media = array();
			// 特殊处理, 把缓存移到旧框架下
			$pattern = addcslashes(cfg('DATA_CACHE_PATH'), '"\/\'\.');
			$replacement = dirname(dirname(APP_PATH)) . '/apps/voa/data/attachments/';
			$file = preg_replace('/^' . $pattern . '/i', $replacement, get_sitedir() . $attachment['at_attachment']);

			if (preg_match('/(jpg|png|gif|jpeg|bmp)$/i', $file)) {
				if (!$serv_media->upload_image($media, array('path' => $file, 'name' => $attachment['at_filename']))) {
					\Think\Log::record($file);
					return false;
				}

				$msgtype = $chat->get_msg_type_img();
			} else {
				if (!$serv_media->upload_file($media, array('path' => $file, 'name' => $attachment['at_filename']))) {
					\Think\Log::record($file);
					return false;
				}

				$msgtype = $chat->get_msg_type_file();
			}

			$msg = $media['media_id'];
		}

		// 如果发送出错, 则记录日志
		if (!$chat->send($msg, $to, $from, $chattype, $msgtype)) {
			\Think\Log::record('send error:' . $msg . "\t" . $to . "\t" . $from);
		}

		return true;
	}

	/**
	 * 退出指定群
	 * @param int $chatid 聊天微信群id
	 * @param string $openid openid
	 * @return boolean
	 */
	public function wxqy_quit($chatid, $openid) {

		// 初始化企业号 service
		$serv_wx = &\Common\Common\Wxqy\Service::instance();
		// 初始化聊天接口
		$chat = new \Com\Chat($serv_wx);
		$params = array('chatid' => $chatid, 'op_user' => $openid);
		if (!$chat->quit($params)) {
			\Think\Log::record('quit error:' . var_export($params, true));
		}

		return true;
	}

	/**
	 * 更新群信息
	 * @param int $chatid 聊天微信群id
	 * @param string $groupname 群名称
	 * @param string $owner 群所有者openid
	 * @param array $new_uids 新加入群的用户uid
	 * @param array $del_uids 删除用户的uid
	 * @param string $op_user 操作者openid
	 */
	public function wxqy_update($chatid, $groupname, $owner, $new_uids = array(), $del_uids = array(), $op_user = '') {

		// 如果操作者为空, 则默认是所有者操作的
		if (empty($op_user)) {
			$op_user = $owner;
		}

		// 初始化企业号 service
		$serv_wx = &\Common\Common\Wxqy\Service::instance();
		// 初始化聊天接口
		$chat = new \Com\Chat($serv_wx);

		// 合并 uid
		$uids = array_merge($new_uids, $del_uids);
		// 读取用户列表
		$memlist = array();
		if (!empty($uids)) {
			$mem_d = D('Common/Member');
			$memlist = $mem_d->list_by_pks($uids);
		}

		// 新/旧 openid
		$add_user_list = array();
		$del_user_list = array();
		// 遍历用户列表
		foreach ($memlist as $_u) {
			if (in_array($_u['m_uid'], $new_uids)) {
				$add_user_list[] = $_u['m_openid'];
			} else {
				$del_user_list[] = $_u['m_openid'];
			}
		}

		// 调用编辑接口
		$params = array(
			'chatid' => $chatid,
			'op_user' => $op_user,
			'name' => $groupname,
			'owner' => $owner,
			'add_user_list' => $add_user_list,
			'del_user_list' => $del_user_list
		);
		if (!$chat->update($params)) {
			\Think\Log::record('update error:' . var_export($params, true));
		}

		return true;
	}

}
