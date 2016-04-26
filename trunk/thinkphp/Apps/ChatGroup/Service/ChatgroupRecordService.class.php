<?php
/**
 * ChatgroupService.class.php
 * $author$
 */

namespace ChatGroup\Service;
use ChatGroup\Model;
use ChatGroup;
use Common\Common\User;

class ChatgroupRecordService extends AbstractService {

	// 聊天组成员MODEL
	protected $_cgm_d;

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("ChatGroup/ChatgroupRecord");
		$this->_cgm_d = D("ChatGroup/ChatgroupMember");
	}

	/**
	 * 发送聊天信息
	 * @param array $chat_record 聊天信息
	 * @param array $params 传入的参数
	 * @param array $extend 扩展参数
	 */
	public function add_msg(&$chat_record, $params, $extend = array()) {

		// 发送人ID
		$uid = (int)$extend['uid'];
		// 发送人名称
		$username = (string)$extend['username'];
		// 聊天组ID
		$cgid = (int)$params['group_id'];
		// 聊天记录
		$record = (string)$params['chat_content'];
		// 附件
		$attachment = (string)$params['chat_attachment'];
		// 附件状态
		$cgr_attachment = $this->_d->get_type_content();

		// 聊天组信息不能为空
		if (empty($cgid)) {
			$this->_set_error('_ERR_GROUP_MESSAGE');
			return false;
		}

		// 聊天记录和附件不能同时为空
		if (empty($record) && empty($attachment)) {
			$this->_set_error('_ERR_RECORD_ATTACHMENT_MESSAGE');
			return false;
		}

		// 如果聊天信息不为空
		if (!empty($record)) {
			$record = html_entity_decode($record);
			$record = html_entity_decode($record);
			$record = preg_replace("/\/\:(:\'\(|:\'|:\)|:~|:B|:\||8-\)|:<|:$|:X|:Z|:-\||:@|:P|:D|:O|:\(|:\+|--b|:Q|:T|,@P|,@-D|:d|,@o|:g|\|-\)|:!|:L|:>|:,@|,@f|:-S|\?|,@x|,@@|:8|,@!|!!!|xx|bye|wipe|dig|handclap|&-\(|B-\)|<@|@>|:-O|>-\||P-\(|X-\)|:\*|@x|8\*|pd|<W>|beer|basketb|oo|coffee|eat|pig|rose|fade|showlove|heart|break|cake|li|bome|kn|footb|ladybug|shit|moon|sun|gift|hug|strong|weak|share|v|@\)|jj|@@|bad|lvu|no|ok|love|<L>|jump|shake|<O>|circle|kotow|turn|skip|oY)/i", "/:\\1 ", $record);
		}

		// 是否是附件,附件状态
		if (!empty($attachment)) {

			// 获得附件信息
			$temp_attachment = $this->_get_attachment($attachment);

			// 如果附件信息存在，并且是图片，则设置附件状态是图片
			if(!empty($temp_attachment) && $temp_attachment['at_isimage']==$this->_d->get_type_attachment_image())	{
				$cgr_attachment = $this->_d->get_type_image();
			}else{ // 否则设置状态为附件
				$cgr_attachment = $this->_d->get_type_attachment();
			}
		}

		// 用户信息不能为空
		if (empty($uid) && empty($username)) {
			$this->_set_error('_ERR_Add_RECORD_MESSAGE');
			return false;
		}

		// 聊天信息
		$chat_record = array(
			'cgr_send_uid' => $uid,
			'cg_id' => $cgid,
			'cgr_send_username' => $username,
			'cgr_attachment' => $cgr_attachment,
			'at_id' => $attachment,
			'cgr_content' => $record,
			'cgr_status' => $this->_d->get_st_create(),
			'cgr_created' => NOW_TIME
		);

		// 如果添加消息失败
		if (!$id = $this->_d->insert($chat_record)) {
			$this->_set_error('_ERR_INSERT_ERROR');
			return false;
		}

		// 返回信息ID
		$chat_record['cgr_id'] = $id;

		// 更新群成员未读消息数
		if (!$this->_cgm_d->increase_unread_count($cgid, $uid)) {
			$this->_set_error('_ERR_EDIT_MESSAGE_COUNT');
			return false;
		}

		// 重置自身的的未读数
		$this->_cgm_d->reset_unread_count($cgid, $uid);

		return true;
	}

	/**
	 * 获取聊天内容
	 * @param $list_record 返回的聊天内容
	 * @param $params 参数
	 * @param array $extend 扩展参数
	 * @return bool执行成功则返回true 否则返回false
	 */
	public function list_group_msg(&$list_record, $params, $extend) {

		// 每页个数
		$limit = (int)$params['limit'];
		// 聊天内容的最大记录id
		$max_record_id = 0;
		// 聊天内容的最小记录ID
		$min_record_id = 0;
		// 聊天组ID
		$cgid = (int)$params['group_id'];
		// 用户ID
		$uid = (int)$extend['m_uid'];

		// 获取记录ID
		if (isset($params['max_record_id'])) {
			$max_record_id = (int)$params['max_record_id'];
			$max_record_id = abs($max_record_id);
		} elseif (isset($params['min_record_id'])) {
			$min_record_id = (int)$params['min_record_id'];
			$min_record_id = abs($min_record_id);
		} else { // 如果最大ID值和最小ID值都没有 则赋值为-1做标识，去最新的一页
			$max_record_id = - 1;
		}

		$list_record = $this->_d->list_group_msg($cgid, $limit, $max_record_id, $min_record_id);
		// 附件ID
		$at_id2id = array();

		// 取附件ID 把附件ID和聊天信息做对应
		foreach ($list_record as $_key => &$_val) {
			$_val['m_face'] = User::instance()->avatar($_val['m_uid']);
			// 附件ID
			if ($_val['cgr_attachment'] != $this->_d->get_type_content()) {
				$at_id2id[$_val['at_id']] = $_key;
			}
		}
		unset($_val);

		// 如果存在附件
		if (! empty($at_id2id)) {
			// 获得附件
			$attachs = $this->_list_attachments(array_keys($at_id2id));

			// 关联附件信息
			foreach ($attachs as $_at) {
				$file = array();
				$file['at_id'] = $_at['at_id'];
				$file['at_filename'] = $_at['at_filename'];
				$file['at_attachment'] = $_at['at_attachment'];
				$file['at_filesize'] = $_at['at_filesize'];
				$atid = $at_id2id[$_at['at_id']];
				$list_record[$atid]['file'] = $file;
			}
		}

		// 如果是获得最新的聊天信息
		// 则修改最后读取的消息时间和修改未读消息数
		if (! empty($list_record) && ($max_record_id > 0 || $max_record_id == -1)) {
			if (! $this->_cgm_d->reset_unread_count($cgid, $uid)) {
				$this->_set_error('_ERR_RESET_MESSAGE_COUNT');
				return false;
			}
		}

		return true;
	}

}
