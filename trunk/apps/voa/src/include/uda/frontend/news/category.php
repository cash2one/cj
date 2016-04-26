<?php
/**
 * voa_uda_frontend_news_category
 * 统一数据访问/新闻公告/类型设置
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_news_category extends voa_uda_frontend_news_abstract {
	/** service 类 */
	private $__service = null;
	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_news_category();
		}
	}

	/**
	 * 保存类型
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)编辑的新闻公告
	 * @param array $options 其他额外的参数（扩展用）
	 * @return boolean
	 */
	public function save_category(array $request, array &$result) {

		//保存类型
		try {
			$this->__service->begin();

			if (empty($request['cat'])) {
				return voa_h_func::throw_errmsg(voa_errcode_oa_news::CATEGORIES_NOT_EXIST);
			}

			$old_ids = array();   //原有的所有类型
			$new_ids = array();   //新编辑的类型中保留有的原类型
			$first_ids = array();   // 原来没有子类型现在添加了子类型的父类型
			//获取原有的类型ID
			$categoires = $this->__service->list_all();
			if ($categoires) {
				$old_ids = array_column($categoires, 'nca_id');
			}
			
			$data = array();
			$updateDataArray = array();
			foreach ($request['cat'] as $k => $cat) {
				//添加第一级分类
				if (strpos($k, 'new_') === false) { //如果是已有分类
					$updateDataArray = array(
						'parent_id' => 0,
						'nca_id' => $k,
						'name' => $cat['name'],
						'orderid' => (int)$cat['orderid']
					);
					$nca_id = $k;
					$this->__service->update($k, $updateDataArray);
					$new_ids[] = $k;
				} else { //如果是新添加分类
					$insert = array(
						'name' => $cat['name'],
						'orderid' => (int)$cat['orderid']
					);
					$row = $this->__service->insert($insert);
					$nca_id = $row['nca_id'];
				}

				//添加第二级分类
				if (isset($cat['nodes']) && !empty($cat['nodes'])) {
					$flag = true;
					foreach ($cat['nodes'] as $sk => $sub) {
						if (strpos($sk, 'new_') === false) {//如果是已有分类
							$updateDataArray = array(
								'parent_id' => $nca_id,
								'nca_id' => $sk,
								'name' => $sub['name'],
								'orderid' => (int)$sub['orderid']
							);
							$flag = false;
							$new_ids[] = $sk;
							$this->__service->update($sk, $updateDataArray);
						} else {//如果是新添加分类
							$data[] = array(
								'parent_id' => $nca_id,
								'nca_id' => null,
								'name' => $sub['name'],
								'orderid' => (int)$sub['orderid']
							);
						}
					}
					if ($flag) {
						$first_ids[] = $k;
					}
				}
			}
			if(!empty($data)){
				$this->__service->insert_multi($data);
			}
			
			$delete_ids = array_diff($old_ids, $new_ids);
			if(!empty($delete_ids)){
				//先删除分类
				$this->__service->delete_by_conds(array('nca_id' => $delete_ids));
			}
			$delete_ids = array_merge($delete_ids, $first_ids);
			//将已被删除的类型ID  和  原来没有子类型现在添加了子类型的父类型    在公告中类型设为0
			
			if (!empty($delete_ids)) {
				$s_news = new voa_s_oa_news();
				$s_right = new voa_s_oa_news_right();
				$s_news->update_by_conds(array('nca_id' => $delete_ids), array('nca_id' => 0));
				$s_right->update_by_conds(array('nca_id' => $delete_ids), array('nca_id' => 0));
			}

			$this->__service->commit();
		} catch (Exception $e) {
			$this->__service->rollback();
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

	/**
	 * 获取所有类型
	 * @return array
	 */
	public function list_categories() {
		// 获取所有类型
		$categories = $this->__service->list_all(null, array('orderid' => 'ASC'));
		if (!$categories) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::CATEGORIES_NOT_EXIST);
		}
		//整理输出
		$result = array();
		foreach ($categories as $cat) {
			if ($cat['parent_id'] == 0) {
				$result[$cat['nca_id']]['nca_id'] = $cat['nca_id'];
				$result[$cat['nca_id']]['name'] = $cat['name'];
				$result[$cat['nca_id']]['orderid'] = $cat['orderid'];
			}
			 if ($cat['parent_id'] != 0) {
				$result[$cat['parent_id']]['nodes'][] = $cat;
			} 
		}

		//排序
		$orderids = array();
		foreach ($result as &$cat) {
			$orderids[] = $cat['orderid'];
			$suborderids = array();
			if (isset($cat['nodes'])) {
				foreach ($cat['nodes'] as $sub) {
					$suborderids[] = $sub['orderid'];
				}
				array_multisort($suborderids, SORT_ASC, $cat['nodes']);
			}
		}
		array_multisort($orderids, SORT_ASC, $result);

		return $result;
	}

	/**
	 * 获取类型及其子类型
	 * @return array $request 条件
	 *
	 */
	public function get_category($nca_id) {
		//取得类型
		$category['name'] =  '分类类别';
		if($nca_id != 0){
			$categorylist = $this->__service->get($nca_id);
			if (!$categorylist) {
				//return voa_h_func::throw_errmsg(voa_errcode_oa_news::CATEGORIES_NOT_EXIST);
				$nca_id = 0;
			}else{
				$category['name'] = $categorylist['name'];
			}
		}
		//取得子类型
		$subs = $this->__service->list_by_conds(array('parent_id' => $nca_id));
		if ($subs) {
			$category['nodes'] = $subs;
		}
		return $category;
	}

}
