<?php
/**
 * 获取相关报告用户名
 * voa_c_api_dailyreport_get_namelist
 * Created by PhpStorm.
 * User: ChangYi
 * Date: 2015/7/2
 * Time: 14:30
 */

class voa_c_api_dailyreport_get_namelist extends voa_c_api_dailyreport_base
{
	/** 搜索条件 */
	private $__text = null;

	public function execute(){
		/*需要的参数*/
		$fields = array(
			/*列表搜索关键字*/
			'keyword' => array('type' => 'string', 'required' => false)
		);
		/*检查参数*/
		if (!$this->_check_params($fields)) {
			return false;
		}
		$this->__text = $this->_params['keyword'];
		/** 报告内容相关人员*/


		$serv = &service::factory('voa_s_oa_dailyreport_mem', array('pluginid' => startup_env::get('pluginid')));
		/** 报告内容相关人员--我发送相关人*/
		$ci['status'] = 'mine';
		$mine = $serv->fetch_by_searchusername(startup_env::get('wbs_uid'), $ci);
		/** 报告内容相关人员--我接收相关人*/
		$ci['status'] = 'recv';
		$recv = $serv->fetch_by_searchusername(startup_env::get('wbs_uid'), $ci);
		//echo json_encode($list);die
		$result = array_merge($mine, $recv);
		$conditions = array();
		//$this->_conditions($conditions);
		$muid = array_unique(array_column($result, 'm_uid'));
		//$conditions = array_merge($conditions, array('m_uid'=>array($muid, 'in')));
		$conditions = array('m_uid'=>array($muid, 'in'));
		$member = &service::factory('voa_s_oa_member');
		$memberlist = $member->fetch_all_by_conditions($conditions);
		$membercount = count($memberlist);
		$member_uid = array_column($memberlist, 'm_uid');
		$array = $this->__FetchRepeatMemberInArray($result);
		foreach($array as $key=>&$val){
			if(!in_array($val['m_uid'], $member_uid)) {
				unset($array[$key]);
				continue;
			}
			if(isset($val['status']) && $val['status']== 'all') {
				$val['spell'] = $memberlist[$val['m_uid']]['m_index'];
				continue;
			}
			if(in_array($val, $mine)) {
				$val['status'] = 'mine';
			}
			if(in_array($val, $recv)) {
				$val['status'] = 'recv';
			}
			$val['spell'] = $memberlist[$val['m_uid']]['m_index'];
		}
		/**查询相关人员*/
		//$ci['m_uid'] = array_column($serach, 'm_uid');
		$this->_result = array(
			'total' => $membercount,
			'data' => $array ? array_values($array) : array()
		);

		return true;
	}

	/**
	 * 搜索条件
	 * @param array $conditions
	 * @return boolean
	 */
	protected function _conditions(&$conditions)
	{
		/** 判断是否为字母*/
		if(preg_match ("/^[A-Za-z]/", $this->__text)) {
			$conditions['m_index'] = array('%'.rstrtoupper(trim($this->__text)).'%', 'like');
		} else {
			$conditions['m_username'] =  array('%'.trim($this->__text).'%', 'like');
		}
		return true;
	}

	/**
	 * 去除重复数据--并给重复值增加状态(array('status'=> 'all') 发送，接收 都有
	 * @param $array
	 * @return array
	 */
	private function __FetchRepeatMemberInArray($array) {
		$return = array();
		$r = array();
		foreach($array as $key=>$v)
		{
			if(!in_array($v, $r))
			{
				$r[$key] = $v;
				$return[$key]=$v;
			}else{
				$nowkey = array_search($v, $r);
				$return[$nowkey]=array_merge($v, array('status'=>'all'));
			}
		}
		return $return;
	}
}
