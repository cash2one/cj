<?php
/**
 * ChatgroupRecordController.class.php
 * $author$
 */

namespace ChatGroup\Controller\Api;

class ChatgroupRecordController extends AbstractController {


	// 发送聊天信息
	public function SendMsg_post() {

		// 聊天信息
		$chat_record = array();
		// 用户提交的参数
		$param = I("request.");
		// 群组ID
		$group_id = $param['group_id'];
		// 当前用户信息
		$m_uid = $this->_login->user['m_uid'];
		// 非用户提交到扩展参数
		$extend = array(
			'uid' => $m_uid,
			'username' => $this->_login->user['m_username']
		);

		// 判断用户是否在群组内
		if (!$this->_is_in_chatgroup($m_uid, $group_id)) {
			$this->_set_error('_ERR_EXIT_OR_NOT_IN_CHATGROUP');
			return false;
		}

		// 获取聊天记录服务层
		$serv_cgr = D('ChatGroup/ChatgroupRecord', 'Service');
		//$charTrans = D('ChatgroupRecord');

		try {

			//$charTrans->startTrans();

			// 发送聊天信息
			if (!$serv_cgr->add_msg($chat_record, $param, $extend)) {
				E($this->get_errcode() . ":" . $this->get_errmsg());
				return false;
			}

			// 发送微信消息
			$d_chatgroup = D('ChatGroup/Chatgroup', 'Service');
			$chatgroup = $d_chatgroup->get($group_id);
			$serv_cgr->wxqy_send($chatgroup['cg_chatid'], $chat_record, $this->_login->user['m_openid']);

			//$charTrans->commit();

		} catch (Exception $e) {

			//$charTrans->rollback();
			$this->_set_error($e);
			return false;
		}

		//返回聊天记录
		$this->_result = $chat_record;
		return true;
	}

	// 获得聊天内容
	public function ListMsg_post() {

		// 聊天记录
		$list_record = array();
		// 用户提交的参数
		$param = I("request.");
		// 获得群组ID
		$group_id = $param['group_id'];
		// 要获取的消息数量
		$limit = (int)$param['limit'];
		// 当前用户
		$m_uid = $this->_login->user['m_uid'];
		// 非用户提交到扩展参数
		$extend = array(
			'm_uid' => $m_uid
		);

		// 判断用户是否在群组内
		if (!$this->_is_in_chatgroup($m_uid, $group_id)) {
			$this->_set_error('_ERR_EXIT_OR_NOT_IN_CHATGROUP');
			return false;
		}

//		//获取记录数
//		if ($limit < cfg('PERPAGE_MIN') || $limit > cfg('PERPAGE_MAX')) {
//			$limit = $this->_plugin->setting['perpage'];
//		}

		// 获得聊天记录
		// 如果获取失败
		$serv_cgr = D('ChatGroup/ChatgroupRecord', 'Service');
		if (!$serv_cgr->list_group_msg($list_record, $param, $extend, $limit)) {
			//E($this->get_errcode() . ":" . $this->get_errmsg());
			$this->_set_error('_ERR_GET_RECORD_ERROR');
			return false;
		}


		$serv_cg = D("ChatGroup/Chatgroup");
		$Chatgroup = $serv_cg->get_chatgroup_by_cgid($group_id);

		$serv_fmt = D('ChatGroup/Format', 'Service');
		$serv_fmt->chatgroup_record_format($list_record);

		$this->_result = array(
			"cg_id"=>$Chatgroup['cg_id'],
			"cg_name"=>$Chatgroup['cg_name'],
			"message" => $list_record,
			"limit" => $limit,
			"total" => count($list_record)
		);

		return true;
	}
}
