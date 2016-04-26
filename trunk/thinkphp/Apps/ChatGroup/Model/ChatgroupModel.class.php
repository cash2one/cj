<?php

/**
 * ChatgroupModel.class.php
 * $author$
 */
namespace ChatGroup\Model;

class ChatgroupModel extends AbstractModel {

	// 群聊
	const TYPE_CHATGROUP = 1;
	// 单聊
	const TYPE_ONECHATGROUP = 2;

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'cg_';
	}

	// 获取群聊值
	public function get_type_chatgroup() {

		return self::TYPE_CHATGROUP;
	}

	// 获取单聊值
	public function get_type_onechatgroup() {

		return self::TYPE_ONECHATGROUP;
	}

	/**
	 * 根据群组id获得聊天组信息
	 * @param int $cgid 群组d
	 * @return array 群组信息
	 */
	public function get_chatgroup_by_cgid($cgid) {

		$sql = "SELECT cg_id, cg_name,cg_type, m_uid, m_username, cg_created, cg_updated FROM __TABLE__ WHERE cg_id=? AND cg_status<?";
		$params = array(
			$cgid,
			$this->get_st_delete()
		);

		return $this->_m->fetch_row($sql, $params);
	}

	/**
	 * 根据群组id返回应该返回的用户id
	 * @param $uid 当前用户
	 * @param $array 群组id集合
	 * retunrn array 用户id集合
	 */
	public function  get_uids_by_cgids($uid, $array) {

		$sql = "SELECT A.`cg_id`, B.`m_uid`
				FROM `oa_chatgroup` A
				LEFT JOIN `oa_chatgroup_member` B
				ON A.`cg_id` = B.`cg_id`
				AND B.`cgm_status`<?
				AND B.`m_uid`<>A.`m_uid`
				WHERE A.`cg_id` in (?)";
		$params = array(
			$this->get_st_delete(),
			$array
		);
		return $this->_m->fetch_array($sql, $params);
	}

	/**
	 * 根据用户id返回群组头像
	 * @param $array 用户ids集合
	 * retunrn array 群组头像集合
	 */
	public function  get_chatface_by_cgids($array) {

		$sql = "SELECT a.m_uid, a.m_face, a.m_username FROM oa_member a  WHERE  a.m_uid IN (?)";
		$params = array($array);

		return $this->_m->fetch_array($sql, $params);
	}

	/**
	 * 检查用户是否为创建者
	 * @param mixed $uid 用户ID
	 * @param int $cgid 群组ID
	 * @return boolean
	 */
	public function is_creater($uid, $cgid) {

		return 0 < $this->_m->result('SELECT COUNT(*) FROM __TABLE__ WHERE `cg_id`=? AND `m_uid`=? AND `cg_status`<?', array(
			$cgid, $uid, $this->get_st_delete()
		));
	}

	/**
	 * 根据创建群组人员m_uid获得聊天组信息
	 *
	 * @param int $m_uid 群组m_uid
	 * @param $array 群组集合
	 */
	public function get_chatgroup_by_muid($m_uid, $cg_type) {

		$sql = "SELECT cg_id, cg_name, m_uid, m_username, cg_created FROM __TABLE__ WHERE `m_uid`=? AND `cg_type`=? AND `cg_status`<?";
		$params = array(
			$m_uid,
			$cg_type,
			$this->get_st_delete()
		);

		return $this->_m->fetch_array($sql, $params);
	}

	/**
	 * 根据微信 chatid 获取群组信息
	 * @param string $chatid 群组id
	 * @return Ambigous <multitype:, unknown>
	 */
	public function get_by_chatid($chatid) {

		return $this->_m->fetch_row('SELECT * FROM __TABLE__ WHERE `cg_chatid`=? AND `cg_status`<? LIMIT 1', array(
			$chatid, $this->get_st_delete()
		));
	}

}
