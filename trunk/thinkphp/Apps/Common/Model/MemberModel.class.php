<?php
/**
 * MemberModel.class.php
 * $author$
 */
namespace Common\Model;

use Common\Model\AbstractModel;
use Common\Common\User;

class MemberModel extends AbstractModel {

	// 用户在微信企业号的状态
	const QYSTATUS_SUBCRIBE = 1; // 已关注
	const QYSTATUS_BLOCK = 2; // 已冻结
	const QYSTATUS_UNSUBCRIBE = 4; // 未关注

	// 用户性别
	const GENDER_MALE = 1; // 男
	const GENDER_FEMALE = 2; // 女

	// 用户来源
	const SOURCE_QRCODE = 1; // 扫码
	const SOURCE_SYSTEM = 2; // 系统
	const SOURCE_OTHER = 3; // 其它

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'm_';
	}

	// 获取关注状态: 已关注
	public function get_qywxstatus_subscribe() {

		return self::QYSTATUS_SUBCRIBE;
	}

	// 获取关注状态: 冻结
	public function get_qywxstatus_block() {

		return self::QYSTATUS_BLOCK;
	}

	// 获取关注状态: 未关注
	public function get_qywxstatus_unsubscribe() {

		return self::QYSTATUS_UNSUBCRIBE;
	}

	public function list_qywxstatus() {

		return array(self::QYSTATUS_BLOCK, self::QYSTATUS_UNSUBCRIBE, self::QYSTATUS_SUBCRIBE);
	}

	// 获取性别: 男
	public function get_gender_male() {

		return self::GENDER_MALE;
	}

	// 获取性别: 女
	public function get_gender_female() {

		return self::GENDER_FEMALE;
	}

	public function list_gender() {

		return array(self::GENDER_FEMALE, self::GENDER_MALE);
	}

	/**
	 *
	 * @param       $username
	 * @param array $page_option
	 * @return array
	 */
	public function list_by_username($username, $page_option = array()) {

		$wheres = array();
		$params = array();

		$wheres = array(
			"`m_username` like ?",
			"`m_status` < ?",
		);
		$params = array(
			$username,
			$this->get_st_delete(),
		);

		$members = $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE " . implode(" AND ", $wheres), $params);

		return $members;
	}

	/**
	 * 根据 $openid 读取用户信息
	 * @param string $openid openid
	 * @return boolean
	 */
	public function get_by_openid($openid) {

		$member = $this->_m->fetch_row("SELECT * FROM __TABLE__ WHERE m_openid=? AND m_status<?", array(
			$openid,
			$this->get_st_delete(),
		));
		User::instance()->push($member);

		return $member;
	}

	/**
	 * 根据 openid 更新用户信息
	 * @param string $openid Openid
	 * @param array $data 待更新的数据
	 */
	public function update_by_openid($openid, $data) {

		return $this->update_by_conds(array('m_openid' => $openid), $data);
	}

	/**
	 * 根据 openid 读取列表
	 * @param array $openids openid 数组
	 * @return Ambigous <multitype:, unknown>
	 */
	public function list_by_openids($openids) {

		$members = $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE `m_openid` IN (?) AND `{$this->prefield}status`<?", array(
			(array)$openids,
			$this->get_st_delete(),
		));
		User::instance()->push($members);

		return $members;
	}

	/**
	 * 根据关键字搜索
	 * @param mixed  $cd_ids 部门ID
	 * @param string $kws 搜索关键字(用户名)
	 * + string keyword 关键字
	 * + string keyindex 索引
	 * @param string $page_option
	 * @param array  $order_option 排序
	 * @return boolean
	 */
	public function list_by_cdid_kws($cd_ids, $kws = array(), $page_option = null, $order_option = array()) {

		// limit
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		list($wheres, $params) = $this->_params_by_cdid_kws($cd_ids, $kws);
		$members = $this->_m->fetch_array("SELECT b.* FROM `oa_member_department` AS a LEFT JOIN __TABLE__ AS b ON a.m_uid=b.m_uid WHERE " . implode(" AND ", $wheres) . " GROUP BY `a`.`m_uid` {$orderby}{$limit}", $params);
		User::instance()->push($members);

		return $members;
	}

	/**
	 * 根据关键字统计总数
	 * @param mixed  $cd_ids 部门ID
	 * @param string $kws 搜索关键字(用户名)
	 * + string keyword 关键字
	 * + string keyindex 索引
	 * @return boolean
	 */
	public function count_by_cdid_kws($cd_ids, $kws = array()) {

		list($wheres, $params) = $this->_params_by_cdid_kws($cd_ids, $kws);

		return $this->_m->result("SELECT COUNT(DISTINCT a.m_uid) FROM `oa_member_department` AS a LEFT JOIN __TABLE__ AS b ON a.m_uid=b.m_uid WHERE " . implode(" AND ", $wheres), $params);
	}

	/**
	 * 根据部门id和关键字查询用户列表
	 * @param array $cd_ids 部门id数组
	 * @param array $kws 关键字数组
	 * @return multitype:multitype:string  multitype:array string NULL
	 */
	public function _params_by_cdid_kws($cd_ids, $kws = array()) {

		$wheres = array();
		$params = array();
		// 如果部门ID不为空
		if (!empty($cd_ids)) {
			$wheres[] = "`a`.`cd_id` IN (?)";
			$params[] = (array)$cd_ids;
		}

		// 用户名称
		if (!empty($kws['keyword'])) {
			$wheres[] = "`b`.`m_username` LIKE ?";
			$params[] = "%" . $kws['keyword'] . "%";
		}

		// 用户名称索引
		if (!empty($kws['keyindex'])) {
			$wheres[] = "`b`.`m_index` LIKE ?";
			$params[] = $kws['keyindex'] . "%";
		}

        // 已关注企业号的人
        if (!empty($kws['m_qywxstatus'])) {
            $wheres[] = "`b`.`m_qywxstatus` = ?";
            $params[] = $kws['m_qywxstatus'];
        }

		$serv_md = D('Common/MemberDepartment');
		$wheres[] = "`b`.`m_status`<? AND `a`.`md_status`<?";
		$params[] = $this->get_st_delete();
		$params[] = $serv_md->get_st_delete();

		return array($wheres, $params);
	}

	/**
	 * 根据部门id读取用户列表
	 * @param array $cd_ids 部门id数组
	 * @param int|array $page_option 分页参数
	 *  + int => limit $page_option
	 *  + array => limit $page_option[0], $page_option[1]
	 * @param array $order_option 排序信息
	 */
	public function list_by_cdid($cd_ids, $page_option = null, $order_option = array()) {

		// limit
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		$wheres = array();
		$params = array();
		if (!empty($cd_ids)) {
			$wheres[] = "`a`.`cd_id` IN (?)";
			$params[] = (array)$cd_ids;
		}

		$wheres[] = "`b`.`m_status`<?";
		$params[] = $this->get_st_delete();

		$members = $this->_m->fetch_array("SELECT b.* FROM `oa_member_department` AS a LEFT JOIN __TABLE__ AS b ON a.m_uid=b.m_uid WHERE " . implode(" AND ", $wheres) . " GROUP BY `a`.`m_uid` {$orderby}{$limit}", $params);
		User::instance()->push($members);

		return $members;
	}

	/**
	 * 根据手机号读取用户信息
	 * @param $phone 手机号
	 * @return array
	 */
	public function get_by_phone($phone) {

		$member = $this->_m->fetch_row('SELECT * FROM __TABLE__ WHERE m_mobilephone=? AND m_status<?', array(
			$phone,
			$this->get_st_delete(),
		));
		User::instance()->push($member);

		return $member;
	}

	/**
	 * 根据 openid 更新用户的关注状态
	 * @param array $openids 用户 userid(openid)
	 * @param int   $status 用户在企业号的状态
	 * @return boolean
	 */
	public function change_qywxstatus_by_openid($openids, $status) {

		$wheres = array();
		$params = array();

		// 检查微信状态值是否合法
		if (!in_array($status, $this->list_qywxstatus())) {
			E('_ERR_QYWXSTATUS_INVALID');

			return false;
		}

		// 更新时 SET 数据
		$sets = array('m_qywxstatus=?');
		$params[] = $status;

		$openids = (array)$openids;
		// 如果是更新指定用户
		if (!empty($openids)) {
			$wheres[] = "m_openid IN (?)";
			$params[] = $openids;
		}

		// 剔除已经删除的
		$wheres[] = "m_status<?";
		$params[] = $this->get_st_delete();

		return $this->_m->execsql("UPDATE __TABLE__ SET " . implode(',', $sets) . " WHERE " . implode(' AND ', $wheres), $params);
	}

	/**
	 * 根据部门id和关注状态获取数据
	 * @param $cdids array 部门id
	 * @param $wxstatus int 关注状态
	 * @param $limit int 每页显示数量
	 * @param $page_option array 分页参数
	 * @return array
	 */
	public function list_by_cdid_status($cdids, $wxstatus, $limit, $page_option) {

		// limit
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}
		$wheres = array();
		$params = array();

		if (!empty($wxstatus)) {
			$wheres[] = "`a`.`m_qywxstatus` = ?";
			$params[] = $wxstatus;
		}
		//member_department，member关联
		if (!empty($cdids)) {
			$wheres[] = "`b`.`cd_id` IN (?)";
			$params[] = $cdids;
		}

		$serv_mdp = D('Common/MemberDepartment');
		$wheres[] = "`b`.`md_status`<?";
		$params[] = $serv_mdp->get_st_delete();
		$wheres[] = "a.m_status < ?";
		$params[] = $this->get_st_delete();

		return $this->_m->fetch_array("SELECT a.*,b.* FROM `oa_member` AS a LEFT JOIN `oa_member_department` AS b ON a.m_uid=b.m_uid WHERE " . implode(" AND ", $wheres), $params);
	}


	/**
	 * 根据部门id和关注状态获取数据
	 * @param $cdids array 部门id
	 * @param $wxstatus int 关注状态
	 * @return array
	 */
	public function count_by_cdid_status($cdids, $wxstatus) {

		$wheres = array();
		$params = array();

		if (!empty($wxstatus)) {
			$wheres[] = "`a`.`m_qywxstatus` = ?";
			$params[] = $wxstatus;
		}
		//member_department，member关联
		if (!empty($cdids)) {
			$wheres[] = "`b`.`cd_id` IN (?)";
			$params[] = $cdids;
		}

		$serv_mdp = D('Common/MemberDepartment');
		$wheres[] = "`b`.`md_status`<?";
		$params[] = $serv_mdp->get_st_delete();
		$wheres[] = "a.m_status < ?";
		$params[] = $this->get_st_delete();

		return $this->_m->result("SELECT COUNT(*) FROM `oa_member` AS a LEFT JOIN `oa_member_department` AS b ON a.m_uid=b.m_uid WHERE " . implode(" AND ", $wheres), $params);
	}

	/**
	 * 根据用户关注状态返回导出数据
	 * @param $wxstatus int 用户关注状态
	 * @param $limit int 每页显示数量
	 * @param $page_option array 分页参数
	 * @return array|bool
	 */
	public function list_by_conds_dump($wxstatus, $limit, $page_option) {

		// limit
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}
		$wheres = array();
		$params = array();

		if (!empty($wxstatus)) {
			$wheres[] = "m_qywxstatus = ?";
			$params[] = $wxstatus;
		}
		$wheres[] = "m_status < ?";
		$params[] = $this->get_st_delete();

		return $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE " . implode(' AND ', $wheres) . "{$limit}", $params);
	}

	/**
	 * 根据微信号/手机号/邮箱/userid读取记录
	 * @param array $conds 条件
	 * @return int 数量
	 */
	public function list_by_unique_field($conds) {

		$wheres = array();
		$params = array();

		$wheres[] = "m_status<?";
		$params[] = $this->get_st_delete();

		// 拼装 OR 条件
		$c_wheres = array();
		foreach ($conds as $_field => $_val) {
			$c_wheres[] = "{$_field}=?";
			$params[] = $_val;
		}

		$wheres[] = '('.implode(' OR ', $c_wheres).')';
		return $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE " . implode(' AND ', $wheres), $params);
	}

	/**
	 * 统计小于更新时间的人数
	 * @param $updated
	 * @return array
	 */
	public function count_less_than_updated($updated) {

		return $this->_m->result("SELECT COUNT(*) FROM __TABLE__ WHERE {$this->prefield}updated <" . $updated . " AND m_status < " . $this->get_st_delete());
	}

	/**
	 * 读取小于更新时间的数据
	 * @param $updated
	 * @param $limit
	 * @return mixed
	 */
	public function list_less_than_updated($updated, $limit) {

		$wheres = array();
		$params = array();

		$wheres[] = "m_updated < ?";
		$params[] = $updated;

		// 剔除已经删除的
		$wheres[] = "m_status<?";
		$params[] = $this->get_st_delete();

		return $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE " . implode(' AND ', $wheres) . " LIMIT " . implode(',', $limit), $params);
	}

	/**
	 * 根据用户名称头字母索引和部门id搜索
	 * @param string $index 用户名头字母索引
	 * @param int|array $cdids 部门ID
	 * @param mixed $page_option 分页
	 * @param mixed $orderby 排序
	 */
	public function list_by_index_cdids($index, $cdids, $page_option, $order_option = array('m_index' => 'ASC')) {

		// limit
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		$wheres = array();
		$params = array();
		$this->_params_by_index_cdids($index, $cdids, $wheres, $params);

		return $this->_m->fetch_array("SELECT b.* FROM oa_member_department AS `a`
				LEFT JOIN __TABLE__ AS `b` ON `a`.`m_uid`=`b`.`m_uid`
				WHERE " . implode(' AND ', $wheres) . " GROUP BY b.m_uid{$orderby}{$limit}", $params);
	}

	/**
	 * 根据用户名索引和部门id统计总数
	 * @param string $index 用户名索引
	 * @param array $cdids 部门id
	 * @return Ambigous <multitype:, number, mixed>
	 */
	public function count_by_index_cdids($index, $cdids) {

		$wheres = array();
		$params = array();
		$this->_params_by_index_cdids($index, $cdids, $wheres, $params);

		return $this->_m->result("SELECT COUNT(DISTINCT a.m_uid) FROM oa_member_department AS a
				LEFT JOIN __TABLE__ AS b ON `a`.`m_uid`=`b`.`m_uid`
				WHERE " . implode(' AND ', $wheres), $params);
	}

	protected function _params_by_index_cdids($index, $cdids, &$wheres, &$params) {

		$wheres[] = '`b`.`m_index` LIKE ?';
		$params[] = $index . '%';

		$wheres[] = '`a`.`cd_id` IN (?)';
		$params[] = $cdids;

		// 各表的数据状态
		$wheres[] = '`md_status`<? AND `m_status`<?';
		$params[] = \Common\Model\MemberDepartmentModel::ST_DELETE;
		$params[] = $this->get_st_delete();
		return true;
	}

	/**
	 * 根据用户名称头字母索引搜索
	 * @param string $index 用户名头字母索引
	 * @param mixed $page_option 分页
	 * @param mixed $orderby 排序
	 */
	public function list_by_index($index, $page_option = array(), $order_option = array('m_index' => 'ASC')) {

		// limit
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		return $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE `m_index` LIKE ? AND `m_status`<?{$orderby}{$limit}", array(
			$index . '%', $this->get_st_delete()
		));
	}

	/**
	 * 根据用户名索引统计总数
	 * @param string $index 用户名索引
	 * @return Ambigous <multitype:, number, mixed>
	 */
	public function count_by_index($index) {

		return $this->_m->result("SELECT COUNT(*) FROM __TABLE__ WHERE `m_index` LIKE ? AND `m_status`<?", array(
			$index . '%', $this->get_st_delete()
		));
	}

}
