<?php
/**
 * voa_c_api_member_list
 */
class voa_c_api_member_get_list extends voa_c_api_member_base {

	protected $_wx_status = array(
	voa_d_oa_member::WX_STATUS_FOLLOWED,
	voa_d_oa_member::WX_STATUS_FREEZE,
	voa_d_oa_member::WX_STATUS_UNFOLLOW
	);

	public function execute()
	{
		$this->__list();
	}

	private function __list() {

		//获取部门id参数
		$cd_id = (int)$this->request->get('cd_id');
		if ($cd_id < 1) {
			$this->_result = array('list' => array());
			return;
		}

		$kw = $this->request->get('kw');
		$page = (int)$this->request->get('page') ;

		//$status = $this->request->get('status');
		if (empty($page)) {
			$page = 1;
		}


		$limit = $this->request->get('limit');
		if(empty($limit) || $limit <= 0){
			$limit = 10;
		}

		if($limit > 1000){
			$limit = 1000;
		}
		$count = 0;
		$start = ($page - 1) * $limit;
		$condi = array();
		//搜索
		$m_uids = $this->__search($kw);
		if ($m_uids === null) {
			$this->_result = array('list' => array());
			return ;
		}
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));

		//判断是否查询关注状态
		/*if ($status !== '' && in_array($status, $this->_wx_status)) {
			$condi['m_qywxstatus'] = $status;

		}*/
		//判断部门是否存在，并且非全公司
		if ($this->_departments[$cd_id] && $this->_departments[$cd_id]['cd_upid'] != 0) {

			//获取下面所有子部门
			$dp_ids = $this->_get_depart_childrens($cd_id);
			$dp_ids[] = $cd_id;

			//获取部门数据
			$m_uids = $this->__in_department($dp_ids, $m_uids, $count, $start, $limit);
			//获取成员数据
			$condi['m_uid'] = array($m_uids, 'IN');
		}

		elseif(is_array($m_uids) && !empty($m_uids)) {
			//获取成员数据
			$condi['m_uid'] = array($m_uids, 'IN');
		}

		$members = $serv_m->fetch_all_by_conditions($condi, array('m_displayorder' => 'ASC'), $start, $limit);
		$count = $serv_m->count_by_conditions($condi);

		$members = $this->__format($members);
		//分页数据
		/*
		$pages = pager::make_links(array(
			'total_items' => $count,
			'per_page' => $limit,
			'current_page' => $page,
			'show_total_items' => true,
		));
		*/
		$this->_result = array('list' => $members, 'total' => $count);
	}

	/**
	 * 关键字模糊查询
	 * @param $kw
	 * @return array
	 */
	private function __search($kw) {

		$kw = trim($kw);
		if (empty($kw)) {
			return array();
		}

		/** 根据用户名搜索通讯录信息 */
		$serv_ms = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$conditions = array(
            'm_username' => array('%'.$kw.'%', 'like')
		);
		/** 从搜索表取通讯录数据 */
		$list = $serv_ms->fetch_all_by_conditions($conditions);
		if ($list) {
			return array_column($list, 'm_uid');
		}
		return null;
	}

	/**
	 * 查询所在部门
	 * @param $cd_ids
	 * @param $m_uids
	 * @param $page
	 * @param $limit
	 * @return array
	 */
	private function __in_department($cd_ids, $m_uids) {

		$serv_md = &service::factory('voa_s_oa_member_department');

		$condi['cd_id'] = array($cd_ids, 'IN');
		if ($m_uids) {
			$condi['m_uid'] = array($m_uids, 'IN');
		}
		$list = $serv_md->fetch_all_by_conditions($condi);

		if ($list) {
			return array_column($list, 'm_uid');
		}

		return array();
	}

	/**
	 * 组织输出数据
	 * @param $members
	 * @return array
	 */
	private function __format($members) {
		$list = array();
		foreach ($members as $member) {
			$item['m_uid'] = $member['m_uid'];
			$item['m_username'] = $member['m_username'];
			//$item['m_mobilephone'] = $member['m_mobilephone'];
			//$item['m_email'] = $member['m_email'];
			/*if ($member['m_gender'] == 0) {
				$item['m_gender'] = '未知';
			} elseif ($member['m_gender'] == 1) {
				$item['m_gender'] = '男';
			} elseif ($member['m_gender'] == 2) {
				$item['m_gender'] = '女';
			}
			$item['job'] = '';
			if ($member['cj_id'] && isset($this->_jobs[$member['cj_id']])) {
				$item['job'] = $this->_jobs[$member['cj_id']]['cj_name'];
			}
			$item['m_qywxstatus'] = $member['m_qywxstatus'];
			*/
			$item['m_face'] = voa_h_user::avatar($member['m_uid'], $member);
			$list[] = $item;
        }

        return $list;
    }
}
