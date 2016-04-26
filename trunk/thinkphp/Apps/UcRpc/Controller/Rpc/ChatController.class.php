<?php
/**
 * ChatController.class.php
 * $author$
 */

namespace UcRpc\Controller\Rpc;

class ChatController extends AbstractController {


	/**
	 * <Item>
	 *  <FromUserName><![CDATA[fromUser]]></FromUserName>
	 *  <CreateTime>1348831860</CreateTime>
	 *  <MsgType><![CDATA[event]]></MsgType>
	 *  <Event><![CDATA[create_chat]]></Event>
	 *  <ChatInfo>
	 *    <ChatId><![CDATA[235364212115767297]]></ChatId>
	 *    <Name><![CDATA[企业应用中心]]></Name>
	 *    <Owner>zhangsan</Owner>
	 *    <UserList>zhangsan|lisi|wangwu</UserList>
	 *  </ChatInfo>
	 * </Item>
	 * @param array $args 消息内容
	 */
	public function create_chat($args) {

		// 合并创建者和群组成员
		$openids = explode('|', $args['chat_info']['user_list']);
		$openids[] = $args['chat_info']['owner'];

		// 读取用户
		$serv_mem = D('Common/Member', 'Service');
		$users = $serv_mem->list_by_openids($openids);

		// 所有者信息
		$owner = array();
		// 成员信息
		$uids = array();
		foreach ($users as $_u) {
			if ($_u['m_openid'] == $args['chat_info']['owner']) {
				$owner = $_u;
			} else {
				$uids[] = $_u['m_uid'];
			}
		}

		// 扩展参数
		$extend = array(
			'uid' => $owner['m_uid'],
			'username' => $owner['m_username']
		);

		$params = array(
			'cg_name' => $args['chat_info']['name'],
			'm_uids' => $uids,
			'chatid' => $args['chat_info']['chat_id']
		);

		// 如果新增操作失败
		$serv_chatgrouop = D('ChatGroup/Chatgroup', 'Service');
		$chatgroup = array();
		$userlist = array();
		if (!$serv_chatgrouop->create_chat_group($chatgroup, $userlist, $params, $extend)) {
			\Think\Log::record(var_export($args, true));
			return false;
		}

		return true;
	}

	/**
	 * 更新会话
	 * <Item>
	 *  <FromUserName><![CDATA[fromUser]]></FromUserName>
	 *  <CreateTime>1348831860</CreateTime>
	 *  <MsgType><![CDATA[event]]></MsgType>
	 *  <Event><![CDATA[update_chat]]></Event>
	 *  <Name><![CDATA[企业应用中心]]></Name>
	 *  <Owner><![CDATA[zhangsan]]></Owner>
	 *  <AddUserList><![CDATA[zhaoliu]]></AddUserList>
	 *  <DelUserList><![CDATA[lisi|wangwu]]></DelUserList>
	 *  <ChatId><![CDATA[235364212115767297]]></ChatId>
	 * </Item>
	 * @param unknown $args
	 */
	public function update_chat($args) {

		// 获取聊天群组
		$serv_chatgroup = D('ChatGroup/Chatgroup', 'Service');
		$chatgroup = $serv_chatgroup->get_by_chatid($args['chat_id']);

		// 编辑群组信息
		if (!empty($args['name'])) {
			if (!$serv_chatgroup->edit_by_cgid($chatgroup['cg_id'], $args['name'])) {
				\Think\Log::record(var_export($args, true));
				return false;
			}
		}

		$serv_mem = D('Common/Member', 'Service');
		$serv_chatgroup_mem = D('ChatGroup/ChatgroupMember', 'Service');
		// 如果有新添加的群组成员
		if (!empty($args['add_user_list'])) {
			$openids = explode('|', $args['add_user_list']);
			$users = $serv_mem->list_by_openids($openids);

			$new_uids = array();
			foreach ($users as $_u) {
				$new_uids[] = $_u['m_uid'];
			}

			// 如果添加新成员执行失败
			if (!empty($new_uids) && !$serv_chatgroup_mem->add_list_members($chatgroup['cg_id'], $new_uids)) {
				\Think\Log::record(var_export($args, true));
				return false;
			}
		}

		// 如果有要删除的聊天组成员
		if (!empty($args['del_user_list'])) {
			$openids = explode('|', $args['del_user_list']);
			$users = $serv_mem->list_by_openids($openids);

			$del_uids = array();
			foreach ($users as $_u) {
				$del_uids[] = $_u['m_uid'];
			}

			// 如果移除群成员失败
			if (!empty($del_uids) && !$serv_chatgroup_mem->remove_member($chatgroup['cg_id'], $del_uids)) {
				\Think\Log::record(var_export($args, true));
				return false;
			}
		}

		return true;
	}

	/**
	 * 退出聊天群组
	 * <Item>
	 *  <FromUserName><![CDATA[fromUser]]></FromUserName>
	 *  <CreateTime>1348831860</CreateTime>
	 *  <MsgType><![CDATA[event]]></MsgType>
	 *  <Event><![CDATA[quit_chat]]></Event>
	 *  <ChatId><![CDATA[235364212115767297]]></ChatId>
	 * </Item>
	 * @param array $args 退出操作
	 */
	public function quit_chat($args) {

		// 取群组信息
		$serv_chatgroup = D('ChatGroup/Chatgroup', 'Service');
		$chatgroup = $serv_chatgroup->get_by_chatid($args['chat_id']);

		// 取用户信息
		$serv_mem = D('Common/Member', 'Service');
		$user = $serv_mem->get_by_openid($args['from_user_name']);

		// 退出企业号群聊
		if (!$serv_chatgroup->quit_chat_group($chatgroup['cg_id'], $user['m_uid'])) {
			\Think\Log::record(var_export($args, true));
			return false;
		}

		return true;
	}

	/**
	 * 保存图片
	 * @param string $media_id 图片的 media_id
	 * @return boolean
	 */
	protected function _save_image_by_media_id(&$fileinfo, $media_id) {

		// 下载
		$serv = &\Common\Common\Wxqy\Service::instance();
		$serv_media = new \Common\Common\Wxqy\Media($serv);

		// 获取图片文件信息
		if (!$serv_media->get($fileinfo, $media_id)) {
			\Think\Log::record($media_id);
			return false;
		}

		// 特殊处理, 把缓存移到旧框架下
		$pattern = addcslashes(cfg('DATA_CACHE_PATH'), '"\/\'\.');
		$replacement = dirname(dirname(APP_PATH)) . '/apps/voa/data/attachments/';
		$file = preg_replace('/^' . $pattern . '/i', $replacement, get_sitedir());

		// 保存文件
		if (!empty($fileinfo['file_name'])) {
			$ext = end(explode('.', $fileinfo['file_name']));
		} else {
			$ext = 'jpg';
		}

		$fileinfo['attachment'] = rgmdate(NOW_TIME, 'Y/m/') . NOW_TIME . random(6) . '.' . $ext;
		$fileinfo['save_path'] = $file . $fileinfo['attachment'];
		file_put_contents($fileinfo['save_path'], base64_decode($fileinfo['file_data']));
		unset($fileinfo['file_data']);

		// 取图片信息
		$get_image_size = getimagesize($fileinfo['save_path']);
		if ($get_image_size && is_array($get_image_size) && !empty($get_image_size[2])) {
			$fileinfo['width'] = $get_image_size[0];
			$fileinfo['height'] = $get_image_size[1];
		}

		return true;
	}

	/**
	 * 图片消息
	 * <Item>
	 *   <FromUserName><![CDATA[fromUser]]></FromUserName>
	 *   <CreateTime>1348831860</CreateTime>
	 *   <MsgType><![CDATA[image]]></MsgType>
	 *   <PicUrl><![CDATA[this is a url]]></PicUrl>
	 *   <MediaId><![CDATA[media_id]]></MediaId>
	 *   <MsgId>1234567890123456</MsgId>
	 *   <Receiver>
	 *     <Type>single</Type>
	 *     <Id>lisi</Id>
	 *   </Receiver>
	 * </Item>
	 * @param array $args 图片消息
	 * @return boolean
	 */
	public function sendimg($args) {

		// 读取文件信息
		$fileinfo = array();
		if (!$this->_save_image_by_media_id($fileinfo, $args['media_id'])) {
			return false;
		}

		// 读取用户信息
		$serv_mem = D('Common/Member', 'Service');
		$member = $serv_mem->get_by_openid($args['from_user_name']);

		// 群组id
		$chatid = $args['receiver']['id'];
		$chatgroup = array();
		if (!$this->__get_chatgroup($chatgroup, $args)) {
			\Think\Log::record(var_export($args, true));
			return false;
		}

		// 附件信息入库
		$attachment = array(
			'm_uid' => $member['m_uid'],
			'm_username' => $member['m_username'],
			'at_filename' => $fileinfo['file_name'],
			'at_filesize' => $fileinfo['file_size'],
			'at_attachment' => $fileinfo['attachment'],
			'at_remote' => 0,
			'at_description' => '',
			'at_isimage' => 1,
			'at_isattach' => 0,
			'at_width' => $fileinfo['width'],
			'at_thumb' => 0
		);
		$attach_serv = D('Common/CommonAttachment', 'Service');
		$attachment['at_id'] = $attach_serv->insert($attachment);

		// 聊天组ID
		$param = array(
			'chat_content' => '',
			'chat_attachment' => $attachment['at_id'],
			'group_id' => $chatgroup['cg_id'],
			'chatid' => $chatid
		);

		$extend = array(
			'uid' => $member['m_uid'],
			'username' => $member['m_username']
		);

		$record = array();
		$serv_cgr = D('ChatGroup/ChatgroupRecord', 'Service');
		if (!$serv_cgr->add_msg($record, $param, $extend)) {
			\Think\Log::record(var_export($args, true));
			return false;
		}

		return true;
	}

	/**
	 * 根据参数读取聊天群组信息
	 * @param array $chatgroup 群组
	 * @param array $args 输入参数
	 * @return boolean
	 */
	private function __get_chatgroup(&$chatgroup, $args) {

		$serv_chatgroup = D('ChatGroup/Chatgroup', 'Service');
		if ('single' != $args['receiver']['type']) {
			$chatgroup = $serv_chatgroup->get_by_chatid($args['receiver']['id']);
			return !empty($chatgroup);
		}

		$serv_mem = D('Common/Member', 'Service');
		$mems = $serv_mem->list_by_openids(array($args['receiver']['id'], $args['from_user_name']));
		$uids = array();
		$sender = array();
		$accepter = array();
		foreach ($mems as $_m) {
			$uids[] = $_m['m_uid'];
			if ($args['from_user_name'] == $_m['m_openid']) {
				$sender = $_m;
			} else {
				$accepter = $_m;
			}
		}

		if (2 > count($uids)) {
			return false;
		}

		$chatid = min($uids[0], $uids[1]);
		$chatid .= '_' . max($uids[0], $uids[1]);
		if (! $chatgroup = $serv_chatgroup->get_by_chatid($chatid)) {
			$chatgroup = array();
			$userlist = array();
			$params = array(
				'm_uids' => array($accepter['m_uid']),
				'cg_name' => $accepter['m_username']
			);
			$extend = array('uid' => $sender['m_uid'], 'username' => $sender['m_username']);
			$cg_name = (string)$params['cg_name'];
			$m_uids = (array)$params['m_uids'];
			if (!$serv_chatgroup->create_chat_group($chatgroup, $userlist, $params, $extend)) {
				\Think\Log::record($serv_chatgroup->get_errcode() . ':' . $serv_chatgroup->get_errmsg());
				return false;
			}
		}

		return true;
	}

	/**
	 * 发送消息
	 * <Item>
	 *  <FromUserName><![CDATA[fromUser]]></FromUserName>
	 *  <CreateTime>1348831860</CreateTime>
	 *  <MsgType><![CDATA[text]]></MsgType>
	 *  <Content><![CDATA[this is a test]]></Content>
	 *  <MsgId>1234567890123456</MsgId>
	 *  <Receiver>
	 *   <Type>single</Type>
	 *   <Id>lisi</Id>
	 *  </Receiver>
	 * </Item>
	 * @param array $args 消息信息
	 */
	public function sendmsg($args) {

		// 读取用户信息
		$serv_mem = D('Common/Member', 'Service');
		$member = $serv_mem->get_by_openid($args['from_user_name']);

		// 群组id
		$chatid = $args['receiver']['id'];
		$chatgroup = array();
		if (!$this->__get_chatgroup($chatgroup, $args)) {
			\Think\Log::record(var_export($args, true));
			return false;
		}

		// 重新整理聊天记录
		$contents = explode("\n", str_replace("\r", "\n", $args['content']));
		foreach ($contents as $_k => &$_v) {
			if (0 < $_k && !empty($_v)) {
				$_v = '<div>' . $_v . '</div>';
			}
		}

		unset($_v);
		// 聊天组ID
		$param = array(
			'chat_content' => implode('', $contents),
			'chat_attachment' => '',
			'group_id' => $chatgroup['cg_id'],
			'chatid' => $chatid
		);

		$extend = array(
			'uid' => $member['m_uid'],
			'username' => $member['m_username']
		);

		$record = array();
		$serv_cgr = D('ChatGroup/ChatgroupRecord', 'Service');
		if (!$serv_cgr->add_msg($record, $param, $extend)) {
			\Think\Log::record(var_export($args, true));
			return false;
		}

		return true;
	}

}
