<?php
/**
 * voa_c_admincp_manage_job_base
 * 职务操作基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_manage_job_base extends voa_c_admincp_manage_base {

	/** 职务名称最大长度 */
	protected $name_length_max	=	30;
	/** 职务名称最小长度 */
	protected $name_length_min	=	1;
	/** 排序允许的最大数值 */
	protected $displayorder_max=	99;
	/** 排序允许的最小数值 */
	protected $displayorder_min=	0;

	protected function _before_action($action) {

		return parent::_before_action($action);
	}

	/**
	 * 获取指定职务信息
	 * @param number $cj_id
	 * @return array
	 */
	protected function _job_detail($cj_id){
		$job	=	is_numeric($cj_id) ? $this->_service_single('common_job', 'fetch', $cj_id) : array();
		if ( !$job ) {
			return $this->_service_single('common_job', 'fetch_all_field', array());
		}
		return $job;
	}

	/**
	 * 格式化显示顺序数字的输入
	 * @param number $displayorder
	 * @return number
	 */
	protected function _displayorder_format($displayorder){
		$max	=	$this->displayorder_max;
		$min	=	$this->displayorder_min;
		return ( is_numeric($displayorder) && rintval($displayorder, false) == $displayorder && $displayorder >= $min && $displayorder <= $max ) ? $displayorder : $max;
	}

	/**
	 * 职务的编辑和删除动作
	 * @param array $update
	 * @param array $delete
	 * @param boolean $returnMessage
	 */
	protected function _job_modify($update = array(), $delete = array(), $returnMessage = true){

		/** 存在待删除的数据 */
		if ( $delete ) {
			$tmp	=	array();
			foreach ( $delete AS $cj_id ) {
				if ( is_numeric($cj_id) ) {
					$tmp[$cj_id]	=	$cj_id;
				}
			}
			$delete	=	$tmp;
			unset($tmp);

			/** 删除指定的id的数据 */
			if ( $delete ) {
				$this->_service_single('common_job', 'delete', $delete);
			}
		}

		/** 存在待更新的数据 */
		if ( $update ) {
			$newUpdate	=	array();//整理每个数据的格式
			foreach ( $update AS $cj_id => $cj ) {

				/** id非数字 */
				if ( !is_numeric($cj_id) ) {
					continue;
				}

				/** 已标记为删除则不更新 */
				if ( isset($delete[$cj_id]) ) {
					continue;
				}

				/** 数据合法，则推入待更新数组 */
				if ( isset($cj['cj_displayorder']) && isset($cj['cj_name']) && validator::is_len_in_range($cj['cj_name'], $this->name_length_min, $this->name_length_max) ) {
					$newUpdate[$cj_id]	=	array(
							'cj_displayorder'	=>	$this->_displayorder_format($cj['cj_displayorder']),
							'cj_name'			=>	$cj['cj_name']
					);
				}

			}
			unset($update);

			/** 经过整理后，进行实际的更新操作 */
			if ( $newUpdate ) {

				/** 待更新数据的旧信息 */
				$old	=	$this->_service_single('common_job', 'fetch_all_by_key', array_keys($newUpdate));

				/** 循环比对哪些id的数据需要进行更新 */
				foreach ( $old AS $cj_id=>$old_cj ) {
					$newCj	=	$this->_updated_fields($old_cj, $newUpdate[$cj_id]);
					/** 需要进行更新 且 名称不重复 */
					if ( $newCj && ( !isset($newCj['cj_name']) || !$this->_service_single('common_job', 'count_by_name_notid', $newCj['cj_name'], $cj_id) ) ) {
						$this->_service_single('common_job', 'update', $newCj, $cj_id);
					}
				}
			}
		}

		/** 更新缓存 */
		voa_h_cache::get_instance()->get('job', 'oa', true);

		/** 直接返回操作提示信息 */
		if ( $returnMessage === true ) {
			$this->message('success', '维护职务信息操作完毕', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id), false);
		}

		/** 只返回结果，后续另行操作 */

		return true;
	}

	/**
	 * 响应提交添加或者编辑动作
	 * /manage/job/base
	 * @param number $cj_id
	 * @param boolean $returnMessage 操作成功后是否返回提示信息
	 */
	protected function _response_submit_edit($cj_id, $returnMessage = true){

		!$cj_id && $cj_id = 0;

		/** 当前管理的职务的详情 */
		$jobDetail	=	$this->_job_detail($cj_id);
		/** 如果是新增，判断职务数量是否超过限制 */
		if ( ( !$cj_id || !$jobDetail['cj_id'] ) && $this->_service_single('common_job', 'count_all', array()) >= $this->job_maxcount ) {
			$this->message('error', '系统限制只允许添加最多 '.$this->job_maxcount.' 个职务，请返回');
		}

		/** 获取提交过来的数据 */
		$param	=	array();
		$param['cj_name']			=	$this->request->post('cj_name');
		$param['cj_displayorder']	=	$this->request->post('cj_displayorder');

		/** 经过检查通过后的数据 */
		$newParam	=	array();

		/** 检查职务名称 */
		if ( !isset($param['cj_name']) ) {
			$this->message('error', '职务名称必须填写');
		} else {
			if ( !validator::is_len_in_range($param['cj_name'], $this->name_length_min, $this->name_length_max) ) {
				$this->message('error', '职务名称长度必须填写，且要求小于 '.$this->name_length_max.'个 字节');
			}
			if ( $this->_service_single('common_job', 'count_by_name_notid', $param['cj_name'], $cj_id) > 0 ) {
				$this->message('error', '职务名称“'.rhtmlspecialchars($param['cj_name']).'”已被使用，请更换一个');
			}
		}
		$newParam['cj_name']	=	$param['cj_name'];

		/** 检查显示顺序输入是否正确 */
		if ( isset($param['displayorder']) ) {
			$newParam['cj_displayorder']	=	$this->_displayorder_format($param['cj_displayorder']);
		} else {
			$newParam['cj_displayorder']	=	rintval($jobDetail['cj_displayorder']);
		}

		/** 提取出待更新的数据 */
		$newParam	=	$this->_updated_fields($jobDetail, $newParam);

		if ( empty($newParam) ) {
			$this->message('error', '没有被修改的数据，无须提交更新');
		}

		/** 编辑 */
		if ( $cj_id ) {
			$this->_service_single('common_job', 'update', $newParam, $cj_id);
			$message	=	'编辑职务信息操作完毕';

			/** 新增 */
		} else {
			$this->_service_single('common_job', 'insert', $newParam);
			$message	=	'添加新职务信息操作完毕';
		}

		/** 更新缓存 */
		voa_h_cache::get_instance()->get('job', 'oa', true);

		/** 直接返回操作提示信息 */
		if ( $returnMessage === true ) {
			$this->message('success', $message, $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id), false);
		}

		/** 只返回结果，后续另行操作 */

		return true;


	}
}
