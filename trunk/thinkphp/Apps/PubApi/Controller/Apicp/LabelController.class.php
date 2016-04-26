<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 15/12/18
 * Time: 下午2:03
 */
namespace PubApi\Controller\Apicp;

class LabelController extends AbstractController {

	const LABELNAME_LENGTH = 16;//标签名长度

	/**
	 * 标签列表接口
	 */
	public function List_get() {

		$params = I('get.');

		//获取标签列表
		$serv_label = D('Common/CommonLabel', 'Service');
		$list = $serv_label->list_label($params);

		//相同排序号处理
		if (!empty($list)) {
			$list = $serv_label->displayorder($list);
		}

		//返回值
		$this->_result = array(
			'list' => $list,
		);
	}

	/**
	 * 新增标签接口
	 */
	public function Add_post() {

		$params = I('post.');
		$serv_label = D('Common/CommonLabel', 'Service');
		//判断参数
		if (empty($params['name'])) {
			E('_ERR_EMPTY_POST_LNAME');
			return false;
		}
		//判断名字长度
		if (mb_strstr($params['name']) > self::LABELNAME_LENGTH) {
			E('_ERR_LABEL_OVER_LENGTH');
			return false;
		}
		//判断名字是否重复
		$conds_name['name'] = $params['name'];
		$record = $serv_label->list_by_conds($conds_name);
		if ($record) {
			E('_ERR_EXISTS_LABEL_NAME');
			return false;
		}
		//排序
		if (empty($params['displayorder'])) {
			$data['displayorder'] = 1;
		} else {
			$data['displayorder'] = $params['displayorder'];
		}

		//待入库数据
		$data['lastordertime'] = NOW_TIME;
		$data['name'] = $params['name'];

		//入库操作
		$serv_label->insert($data);
		return true;
	}

	/**
	 * 编辑标签初始化接口
	 */
	public function Initial_get() {

		$params = I('get.');
		$laid = $params['laid'];
		//非空判断
		if (empty($params)) {
			E('_ERR_EMPTY_GET_LAID');
			return false;
		}

		$record = array();
		//获取标签信息
		$serv_label = D('Common/CommonLabel', 'Service');
		$record = $serv_label->get($laid);
		//不存在
		if (empty($record)) {
			E('_ERR_NOT_EXISTS_LABEL');
			return false;
		}

		//返回值
		$this->_result = array(
			'laid' => $laid,
			'name' => $record['name'],
			'displayorder' => $record['displayorder'],
		);
	}

	/**
	 * 编辑操作接口
	 */
	public function Edit_post() {

		$params = I('post.');
		$laid = $params['laid'];
		//非空判断
		if (empty($params['name'])) {
			E('_ERR_EMPTY_POST_LNAME');
			return false;
		}

		//判断名字长度
		if (mb_strstr($params['name']) > self::LABELNAME_LENGTH) {
			E('_ERR_LABEL_OVER_LENGTH');
			return false;
		}

		//非数字
		if (empty($params['displayorder']) || $params['displayorder'] < 0 || !is_numeric($params['displayorder'])) {
			$data['displayorder'] = 1;
		} else {
			$data['displayorder'] = (int)$params['displayorder'];
		}

		//获取上次的排序号
		$serv_label = D('Common/CommonLabel', 'Service');
		$record = $serv_label->get($laid);

		//如果更改排序则更新时间
		if ($record['displayorder'] != $data['displayorder']) {
			$data['lastordertime'] = NOW_TIME;
		}
		$data['name'] = $params['name'];

		//更新操作
		$serv_label->update($laid, $data);

		return true;
	}

	/**
	 * 标签删除接口
	 */
	public function Delete_post() {

		$params = I('post.');
		$laid = $params['laid'];
		//非空判断
		if (empty($params)) {
			E('_ERR_EMPTY_GET_LAID');
			return false;
		}
		$laid_list = explode(',', $laid);
		// 删除敏感成员设置里可能存在的标签ID
		foreach ($laid_list as $_laid) {
			$this->delete_labelid_from_setting($_laid);
		}
		$serv_label = D('Common/CommonLabel', 'Service');

		//删除操作
		$serv_label->delete($laid_list);

		//删除标签里的人
		$conds_label_mem['laid'] = $laid;
		$serv_label_mem = D('Common/CommonLabelMember', 'Service');
		$serv_label_mem->delete_by_conds($conds_label_mem);

		// 更新缓存
		clear_cache();

		return true;
	}

	/**
	 * 标签人员列表接口
	 */
	public function ListMember_get(){

		$params = I('get.');
		$laid = $params['laid'];
		$page = $params['page'];
		$limit = $params['limit'];
		// 判断是否为空
		if (empty($params['page'])) {
			$page = 1;
			$params['page'] = 1;
		}
		if (empty($params['limit'])) {
			$limit = 10;
			$params['limit'] = 10;
		}
		if(empty($params['laid'])){
			E('_ERR_EMPTY_LAID');
			return false;
		}
		// 分页参数
		list($start, $limit, $page) = page_limit($page, $limit);
		// 分页参数
		$page_option = array($start, $limit);
		$serv_label_mem = D('Common/CommonLabelMember', 'Service');

		//标签里人的总数
		$total = 0;
		$total = $serv_label_mem->count_by_conds_member($params);
		//标签里的人和部门
		$show_list = array();
		$list = $serv_label_mem->list_by_conds_member($params, $page_option);

		//数据格式
		if(!empty($list)){
			$list = $this->list_format($list);
			//获取标签里的所有人
			$conds_all['laid'] = $params['laid'];
			$all_mem = $serv_label_mem->list_by_conds($conds_all);
			$all_list = $this->list_format($all_mem);
		}

		$pages = ceil($total/$limit);

		//返回值
		$this->_result = array(
			'mem_list' => $list,
			'all_list' => $all_list,
			'total' => $total,
			'page' => $page,
			'pages' => $pages,
		);
	}

	/**
	 * 标签里人员信息格式方法
	 * @param $list array 待格式数组
	 * @return mixed array 格式后数组
	 */
	public function list_format($list){

		//人员数据格式
		foreach($list as $_val){
			$mem_list[] = $_val['m_uid'];
		}
		//获取详细信息
		$serv_member = D('Common/Member', 'Service');
		$conds_mem['m_uid'] = $mem_list;
		$mem_list = $serv_member->list_by_conds($conds_mem);
		//以m_uid为键
		foreach($mem_list as $_dep){
			$new_mem_list[$_dep['m_uid']] = $_dep;
		}
		foreach($list as $val){
			$tmp = array();
			$tmp['m_uid'] = $val['m_uid'];
			$tmp['m_username'] = $val['m_username'];
			$tmp['m_face'] = $new_mem_list[$val['m_uid']]['m_face'];
			$cd_id = $new_mem_list[$val['m_uid']]['cd_id'];
			$tmp['cd_name'] = $this->_departments[$cd_id]['cd_name'];
			$show_list[] = $tmp;
		}

		return $show_list;
	}

	/**
	 * 添加人员到标签
	 */
	public function AddMember_post(){

		$params = I('post.');
		$laid = $params['laid'];

		$mem_list = explode(',', $params['m_uid']);
		//完善人员信息
		$serv_mem = D('Common/Member', 'Service');
		$conds_mem['m_uid'] = $mem_list;
		$member_list = $serv_mem->list_by_conds($conds_mem);
		if(!empty($member_list)){
			foreach($member_list as $val){
				$m_list[$val['m_uid']] = $val;
			}
		}
		$serv_label_mem = D('Common/CommonLabelMember', 'Service');

		//格式待入库的数据
		foreach($mem_list as $_val){
			if(!isset($m_list[$_val])){
				E('_ERR_NOT_EXISTS_MEMBER');
				return false;
			}
			$tmp = array();
			$tmp['m_uid'] = $_val;
			$tmp['m_username'] = $m_list[$_val]['m_username'];
			$tmp['laid'] = $laid;
			$data[] = $tmp;
		}
		//获取该标签里的人
		$conds_current['laid'] = $laid;
		$current_mem = $serv_label_mem->delete_by_conds($conds_current);

		//入库
		$serv_label_mem->insert_all($data);

		//返回值
		$this->_result = array(
			'laid' => $laid
		);

		return true;
	}

	/**
	 * 从标签里移除人员接口
	 */
	public function DeleteMem_post(){

		$params = I('post.');
		$laid = $params['laid'];
		$m_uid = $params['m_uid'];
		//非空判断
		if(empty($params['m_uid'])){
			E('_ERR_EMPTY_POST_UID');
			return false;
		}
		if(empty($params['laid'])){
			E('_ERR_EMPTY_GET_LAID');
			return false;
		}
		$uid_list = explode(',', $m_uid);

		$serv_label_mem = D('Common/CommonLabelMember', 'Service');
		//删除操作
		if(!empty($uid_list)){
			$serv_label_mem->delete_by_laid_muid($uid_list, $laid);
		}

		return true;
	}

	/**
	 * 删除敏感成员设置中的标签ID
	 * @param $laid
	 * @return bool
	 */
	public function delete_labelid_from_setting($laid) {

		// 获取设置缓存
		$cache = &\Common\Common\Cache::instance();
		$setting = $cache->get('Common.member_setting');

		// 获取属性规则
		$label = $setting['sensitive'];
		foreach ($label as $_key => &$_label) {
			// 去掉ID
			if (in_array($laid, $_label['laid'])) {
				$key = array_search($laid, $_label['laid']);
				unset($_label['laid'][$key]);
				$_label['laid'] = array_values($_label['laid']);
				// 如果ID变为空了
				if (empty($_label['laid'])) {
					unset($label[$_key]);
				}

				break;
			}
		}

		$label_insert = serialize($label);
		$serv_label = D('Common/MemberSetting', 'Service');
		$serv_label->update_by_conds(array('m_key' => 'sensitive'), array('m_value' => $label_insert));

		return true;
	}

}
