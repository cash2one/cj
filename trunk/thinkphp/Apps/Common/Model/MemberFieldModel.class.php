<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/12/18
 * Time: 下午6:56
 */

namespace Common\Model;

use Common\Model\AbstractModel;
use Common\Common\User;

class MemberFieldModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'mf_';
	}

	/**
	 * 根据uid获取人员所有信息方法
	 * @param $uid_list array 用户id
	 * @param $field array 开启自定义字段
	 * @return array
	 */
	public function list_field_by_uid($uid_list, $field) {

		//搜索的字段
		foreach ($field as &$_fiel) {
			$_fiel = 'a.' . $this->prefield . $_fiel;
		}
		$str_field = implode(',', $field);
		$wheres = array();
		$params = array();

		$wheres[] = "`a`.`m_uid` IN (?)";
		$params[] = $uid_list;
		$wheres[] = "`b`.`m_status`< ?";
		$params[] = $this->get_st_delete();
		$wheres[] = "`a`.`mf_status`<?";
		$params[] = $this->get_st_delete();

		return $this->_m->fetch_array("SELECT a.*,b.m_username,b.m_openid,b.m_gender,b.m_mobilephone,b.m_weixin,b.m_email,b.cd_id,b.cj_id FROM `oa_member_field` AS a LEFT JOIN `oa_member` AS b ON a.m_uid = b.m_uid WHERE " . implode(" AND ", $wheres), $params);
	}

	/**
	 * 获取表默认数据
	 * @return array
	 */
	public function list_field() {

		$sql = "SHOW FIELDS FROM __TABLE__";

		return $this->_m->fetch_array($sql);
	}

	/**
	 * 根据 $uid 读取用户信息
	 * @param string $uid
	 * @return boolean
	 */
	public function get_list_by_uid($uid, $setting) {

		return $this->_m->fetch_row("SELECT {$setting} FROM __TABLE__ WHERE m_uid=? AND {$this->prefield}status<?", array (
			$uid,
			$this->get_st_delete()
		));
	}

}
