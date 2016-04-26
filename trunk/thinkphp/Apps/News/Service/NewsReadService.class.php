<?php
/**
 * NewsReadService.class.php
 * $author$
 */
namespace News\Service;

class NewsReadService extends AbstractService {

	protected $_right;
	protected $_member;
	protected $_member_department;

	const IS_ALL = 1;

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("News/NewsRead");
		$this->_right = D("News/NewsRight");
		$this->_member = D("Common/Member");
		$this->_member_department = D('Common/MemberDepartment');
	}

	/**
	 * 用户的已读列表
	 * @param int $m_uid 用户id
	 * @return array 用户已读的列表
	 */
	public function read_list($m_uid) {

		$list = $this->_d->list_by_uid($m_uid);

		return $list;
	}

	/**
	 * 未读用户列表
	 * @param int $ne_id
	 * @return array
	 */
	public function un_read_list($ne_id) {

		$list = array();

		//获取可读人员列表
		$may = $this->may_read_list($ne_id);

		if(!$may){

			return false;
		}
		//获取已读人员列表
		$has = $this->has_read_list($ne_id);
		if(!$has){

			$has = array();
		}
		$list = array_diff($may, $has);
		return $list;
	}

	/**
	 * 获取可读人员列表
	 * @param int $ne_id
	 * @return array
	 * */

	public function may_read_list($ne_id){

		$list = array();
		$read = $this->_right->list_by_conds(array('ne_id'=>$ne_id));
		$read = array_values($read);

		//如果为全公司
		if($read[0]['is_all'] == self::IS_ALL){
			$result = $this->_member->list_all();
			$list = array_column($result, 'm_uid');
		}else{

			//获取可阅读的部门
			$department = array_filter(array_column($read, 'cd_id'));
			if (!empty($department)) {
				$result = $this->_member_department->list_by_conds(array('cd_id' => $department));
				$list = array_column($result, 'm_uid');
			}

			//获取可阅读的人员
			$member = array_filter(array_column($read, 'm_uid'));
			if (!empty($member)) {
				$list = array_merge($member,$list);
			}

			//去重
			$list = array_values(array_unique($list));
		}

		return $list;
	}

	/**
	 * 获取已读人员列表
	 * @param int $ne_id
	 * @return array
	 * */

	public function has_read_list($ne_id){

		$list = array();

		$list = $this->list_by_conds(array('ne_id' => $ne_id));

		if(!$list){

			return false;
		}

		//返回以m_uid组成的数组
		$list = array_column($list, 'm_uid');

		return $list;
	}

	/**
	 * 插入阅读记录
	 * @param $ne_id 公告ID
	 * @param $m_uid 用户m_uid
	 */
	public function insert_data($ne_id, $m_uid) {

		$conds = array('ne_id' => $ne_id, 'm_uid' => $m_uid);
		$result = $this->_d->list_by_conds($conds);

		// 如果不存在
		if (!$result) {
			$this->_d->insert($conds);
			return true;
		}

		return false;
	}

}
