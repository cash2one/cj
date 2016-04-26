<?php
/**
 * 启用禁用
 */

class voa_c_admincp_api_sign_enable extends voa_c_admincp_api_sign_base {

	public function execute() {
		$get = $this->request->getx();

		if(empty($get['sbid'])){
			$this->output_error_message('100031', '操作失败', 'false');
			return false;
		}
		
		$sbid = $get['sbid'];
		$serv = &service::factory('voa_s_oa_sign_batch');
		$serv_dep = &service::factory('voa_s_oa_sign_department');
		
		//当前班次信息
		$info = $serv->get($sbid);
		$current = $get['current']==1 ? '0' : '1' ; // 禁用传递的是0, 启用传递的是1
		//判断部门是否冲突
		if($current == 1){
			$conds['sbid'] = $sbid;
			$dep_list = $serv_dep->list_by_conds($conds); // 班车对应的部门数据
			//自身班次所有部门
			$dep = array();
			
			foreach($dep_list as $val){
				$dep[] = $val['department'];
			}
			$conds_batch['enable'] = 1;
			$today = startup_env::get ( 'timestamp' );
			//判断班次是否到开启时间
			if($info['start_begin'] > $today){
				$this->output_error_message('100034', '该班次还未到启用时间', 'false');
				return false;
			}
			// 获取启用的结果集 判断是否重复
			$enable_list = $serv->list_by_conds($conds_batch);
			// var_dump($enable_list);die;
			$en_able = array();
			if(!empty($enable_list)){
				foreach($enable_list as $_e_l){
					$min_t = $_e_l ['start_begin'];
					if (! empty ( $_e_l ['start_end'] )) {		
						$max_t = $_e_l ['start_end'] + 86400;
						if ($_e_l ['enable'] == 1 && $today < $max_t && $today > $min_t) {
							$en_able [] = $_e_l;
						}
					} else {
						if ($_e_l ['enable'] == 1 && $today > $min_t) {
							$en_able [] = $_e_l;
						}
					
					}
				}
			}
			//var_dump($en_able);die;
			$enable =array();
			if(!empty($en_able)){
			foreach($en_able as $_val){
				$enable[] = $_val['sbid'];
			}
			
			$conds_en_dep['sbid IN (?)'] = $enable;
			//其他班次所有部门
			$en_dep_list = $serv_dep->list_by_conds($conds_en_dep);
			foreach($en_dep_list as $_dep){
				$en_dep_list[] = $_dep['department'];
			}
			// 该班次的所有部门
			foreach($en_dep_list as $_d){
				foreach($dep as $_de){ // 自身班次的所有部门
					if($_d == $_de){
						$this->output_error_message('100033', '该班次部门与其他班次冲突', 'false');
						return false;
					}
				}
			}
			}
		}
		$data['enable'] = $current;

		$serv->update($sbid, $data);
	
		$result = array(
				'sbid' => $sbid,
				'current' => $current,
		);
	
		//输出结果
		$this->_output_result($result);
	}
}
