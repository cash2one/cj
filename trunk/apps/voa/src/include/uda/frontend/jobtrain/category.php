<?php
/**
 * voa_uda_frontend_jobtrain_category
 * Create By wowxavi
 * $Author$
 * $Id$
 */

class voa_uda_frontend_jobtrain_category extends voa_uda_frontend_base {
	/** service 类 */
	private $__service = null;
	private $_serv_right = null;
	private $_serv_article = null;
	private $_serv_coll = null;
	private $_serv_comment = null;
	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_jobtrain_category();
			$this->_serv_right = new voa_s_oa_jobtrain_right();
			$this->_serv_study = new voa_s_oa_jobtrain_study();
			$this->_serv_article = new voa_s_oa_jobtrain_article();
			$this->_serv_coll = new voa_s_oa_jobtrain_coll();
			$this->_serv_comment = new voa_s_oa_jobtrain_comment();
		}
	}
	/**
	 * 保存分类
	 * @param array $request 请求的参数
	 * @param array $args 其他额外的参数
	 * @return boolean
	 */
	public function save_cata(array $request, $args) {
		$request['cd_ids'] = implode(',', $request['cd_ids']);
		$request['m_uids'] = implode(',', $request['m_uids']);
		$fields = array(
			'title' => array(
				'title', parent::VAR_STR,
				array($this->__service, 'validator_title'),
				null, false
			),
			'cd_ids' => array('cd_ids', parent::VAR_STR, null, null, false),
			'm_uids' => array('m_uids', parent::VAR_STR, null, null, false),
			'orderid' => array('orderid', parent::VAR_INT, null, null, false),
			'is_all' => array('is_all', parent::VAR_INT, null, null, false),
			'is_open' => array('is_open', parent::VAR_INT, null, null, false),
		);
		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}
		// 如果分类标题已存在
		/*
		$conds = array(
			'title' => $this->__request['title'],
			'id<>?' => $args['id']
		);
		if ($this->__service->get_by_conds($conds)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_jobtrain::CATEGORY_TITLE_EXIST);
		}
		*/
		// 处理子分类
		if($args['pid']) {
			$this->__request['pid'] = $args['pid'];
		}
		if($this->__request['is_all']) {
			$this->__request['cd_ids'] = $this->__request['m_uids'] = '';
		}

		try {
			$this->__service->begin();

			$cata_id = $args['id'];
			if($args['id']) {
				// 更新分类
				$cata = $this->__request;
				$cata['id'] = $args['id'];
				$this->__service->update($args['id'], $this->__request);
				// 更新子分类权限
				$this->_set_sub_right($cata);
			} else {
				$cata = $this->__service->insert($this->__request);
			}
			// 设置分类权限
			$this->_set_right($cata);

			$this->__service->commit();
		} catch (Exception $e) {
			$this->__service->rollback();
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}
	/**
	 * 保存分类列表
	 * @param array $request 请求的参数
	 * @return boolean
	 */
	public function save_catalist(array $request) {
		$data = array();
		foreach ($request['cat'] as $k => $cat) {
			$data = array(
				'orderid' => rintval($cat['orderid'])
			);
			if($cat['title']) {
				$data['title'] = $cat['title'];
			}
			$this->__service->update($k, $data);
		}
		return true;
	}
	/**
	 * 获取所有分类
	 * @return array
	 */
	public function list_cata($is_tree = true, $conds = null){
		$catas = $this->__service->list_by_conds($conds, null, array('orderid' => 'ASC', 'id' => 'ASC'));
		if($is_tree){
			return $this->_get_tree($catas);
		}else{
			return $this->_get_by_depth($catas);
		}
		
	}
	/**
	 * 递归输出
	 * @param arr $data
	 * @param int $id
	 * @return array
	 */
	private function _get_tree($data, $id=0) {
		$arr = array();
		foreach ( $data as $key => $item ) {
			if($item['pid']==$id){
				$arr[$item['id']]=$item;
				unset($data[$key]);
				$arr[$item['id']]['childs']=$this->_get_tree( $data,$item['id'] );
			}
		}
		return $arr;
	}
	/**
	 * 递归输出深度
	 * @param arr $data
	 * @param int $id
	 * @return array
	 */
	private function _get_by_depth($data, $id=0, $depth=1) {
		$arr = array();
		foreach ( $data as $key => $item ) {
			if($item['pid']==$id){
				$item['depth']=$depth;
				$arr[$item['id']]=$item;
				unset($data[$key]);
                $arr = $arr+$this->_get_by_depth( $data,$item['id'],$depth+1);
			}
		}
		return $arr;
	}
	/**
	 * 递归获取子分类
	 * @param arr $data
	 * @param int $id
	 * @return array
	 */
	private function _get_childs($data, $id=0) {
		$arr = array();
		foreach ( $data as $key => $item ) {
			if($item['pid'] == $id){
				$arr[$item['id']] = $item;
				unset($data[$key]);
                $arr = $arr+$this->_get_childs( $data,$item['id']);
			}
		}
		return $arr;
	}
	/**
	 * 获取数据
	 * @param int $id
	 * @return array
	 */
	public function get_cata($id) {
		$result = $this->__service->get($id);
		if (!$result) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_jobtrain::CATEGORY_NOT_EXIST);
		}

		if(!empty($result['cd_ids'])) {
			$s_department = new voa_s_oa_common_department();
			$depms = $s_department->fetch_all_by_key(explode(',', $result['cd_ids']));
			foreach($depms as $k => $v) {
				$result['departments'][] = array(
					'id' => $k,
					'cd_name' => $v['cd_name'],
					'isChecked' => (bool)true,
				);
			}
		}

		if(!empty($result['m_uids'])) {
			$s_member = new voa_s_oa_member();
			$members = $s_member->fetch_all_by_ids(explode(',', $result['m_uids']));
			foreach($members as $k => $v) {
				$result['members'][] = array(
					'm_uid' => $v['m_uid'],
					'm_username' => $v['m_username'],
					'selected' => (bool)true,
				);
			}
			
		}

		return $result;
	}

	/**
	 * 删除分类
	 * @param int $id
	 * @return bool
	 */
	public function del_cata($id) {
		
		if (!$id) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_jobtrain::CATEGORY_NOT_EXIST);
		}
		// 有子分类不删除
		$subcatas = $this->__service->get_by_conds(array('pid' => $id));
		if ($subcatas) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_jobtrain::CATEGORY_HAVE_SUB);
		}
		// 有文章不删除
		$count = $this->_serv_article->count_by_conds(array('cid' => $id));
		if ($count) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_jobtrain::CATEGORY_HAVE_ARTICLE);
		}
		$this->__service->delete($id);
		return true;
	}

	/**
	 * 设置分类权限
	 * @param arr $cata
	 */
	protected function _set_right($cata) {
		// 物理 删除 权限
		$this->_serv_right->delete_real_records_by_conds(array('cid' => $cata['id'] ));
		if(!$cata['is_all']) {
			// 部门 插入权限
			if(!empty($cata['cd_ids'])) {
                $cd_ids = explode(',', $cata['cd_ids']);
                foreach ($cd_ids as $v) {
                    $data_right[] = array(
                        'aid' => 0,
                        'cid' => $cata['id'],
                        'm_uid' => 0,
                        'cd_id' => $v,
                        'is_all' => 0
                    );
                }
            }
			// 用户 插入权限
            if(!empty($cata['m_uids'])) {
                $m_uids = explode(',', $cata['m_uids']);
                foreach ($m_uids as $v) {
                    $data_right[] = array(
                        'aid' => 0,
                        'cid' => $cata['id'],
                        'm_uid' => $v,
                        'cd_id' => 0,
                        'is_all' => 0
                    );
                }
            }
            $this->_serv_right->insert_multi($data_right);
		}else{
			$data_right = array(
                'aid' => 0,
                'cid' => $cata['id'],
                'm_uid' => 0,
                'cd_id' => 0,
                'is_all' => 1
            );
            $this->_serv_right->insert($data_right);
		}
        // 获取该分类范围学习人数
        $m_sum = $this->_serv_article->get_study_sum($cata);
        // 获取分类下文章
		$article_list = $this->_serv_article->list_by_conds(array('cid' => $cata['id']));
		if($article_list){
			// 获取文章id数组
			$a_ids = array();
			foreach ($article_list as $_v) {
				$a_ids[]=$_v['id'];
			}
			// 更新文章学习、人数和总人数
			$this->_serv_article->update($a_ids, array('study_num' => 0, 'study_sum' => $m_sum));
			// 删除收藏、评论、学习
			$this->_serv_study->delete_by_conds(array('aid' => $a_ids ));
			//$this->_serv_coll->delete_by_conds(array('aid' => $a_ids ));
			//$this->_serv_comment->delete_by_conds(array('aid' => $a_ids ));
		}
	}
	/**
	 * 设置子分类权限
	 * @param arr $cata
	 */
	protected function _set_sub_right($cata) {
		if(!$cata['is_all']) {
			$subcatas = $this->__service->list_by_conds( array('pid' => $cata['id']) );
			$p_cd_ids = explode(',', $cata['cd_ids']);
            $p_m_uids = explode(',', $cata['m_uids']);
            foreach ($subcatas as $k => $v) {
                $s_cd_ids = explode(',', $v['cd_ids']);
                $s_m_uids = explode(',', $v['m_uids']);
                $n_cd_ids = $this->_array_diff($p_cd_ids, $s_cd_ids);
				$n_m_uids = $this->_array_diff($p_m_uids, $s_m_uids);
				 // 范围不等于旧，重新设置范围和权限
                if(!$this->_array_same($s_cd_ids, $n_cd_ids) || !$this->_array_same($s_m_uids, $n_m_uids)){
                	$this->_set_right($v);
                }
            }
		}
	}


	/**
	 * 比较两个数组返回新数组
	 * @param arr $p_arr
	 * @param arr $s_arr
	 * @return arr
	 */
	private function _array_diff($p_arr, $s_arr) {
		$n_arr = array();
		foreach ($s_arr as $k => $v) {
			if(in_array($v, $p_arr)) {
				$n_arr[] = $v;
			}
		}
		return $n_arr;
	}
	/**
	 * 比较两个数组是否相同
	 * @param arr $arr1
	 * @param arr $arr2
	 * @return bool
	 */
	private function _array_same($arr1, $arr2) {
		if(array_diff($arr1,$arr2) || array_diff($arr2,$arr1)){
	        return false;
	    }else{
	        return true;
	    }
	}
				
}