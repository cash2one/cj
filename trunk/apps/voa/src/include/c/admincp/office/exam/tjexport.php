<?php
/**
* 统计导出
* Create By wogu
* $Author$
* $Id$
*/

class voa_c_admincp_office_exam_tjexport extends voa_c_admincp_office_exam_base {

	public function execute() {
		$id = intval($this->request->get('id'));
		if(!$id) {
			$this->_error_message('参数错误');
		}

		// 导出类型
		$status = intval($this->request->get('status'));
		if($status == 0) {
			$fileds = array(
				'username' => array(
					'name'  => '姓名',
					'width' => 30
				),
				'cd_name' => array(
					'name'  => '部门',
					'width' => 30
				),
				'my_begin_time' => array(
					'name'  => '开始时间',
					'width' => 30
				),
				'my_end_time' => array(
					'name'  => '结束时间',
					'width' => 30
				),
				'my_time' => array(
					'name'  => '用时',
					'width' => 30
				),
				'my_score' => array(
					'name'  => '分数',
					'width' => 30
				),
				'status' => array(
					'name'  => '状态',
					'width' => 30
				)
			);
		} else {
			$fileds = array(
				'username' => array(
					'name'  => '姓名',
					'width' => 30
				),
				'cd_name' => array(
					'name'  => '部门',
					'width' => 30
				),
				'created' => array(
					'name'  => '创建时间',
					'width' => 30
				),
				'status' => array(
					'name'  => '状态',
					'width' => 30
				)
			);
		}

		$s_paper = new voa_s_oa_exam_paper();
		$paper = $s_paper->get_by_id($id);
		if(!$paper) {
			$this->_error_message('试卷不存在');
		}

		$s_tj = new voa_s_oa_exam_tj();

		$conds=array(
			'paper_id'=>$id,
		);
		

		if($status==0){
			$conds['status']=array(0,'>');
		}else{
			$conds['status']=0;
		}
		$uda_list = &uda::factory('voa_uda_frontend_exam_tj');
		$uda_list->list_tj($result, $conds);
		$tjs=$result['list'];
		if(!$result['total']) {
			$this->_error_message('人数为0无法导出');
		}

		//$tjs = $s_tj->list_by_conds($conds);
		if(!empty($tjs)) {
			$uids = array();
			foreach($tjs as $tj) {
				$uids[] = $tj['m_uid'];
			}

			// 获取考生信息
			$s_member = new voa_s_oa_member();
			$members = $s_member->fetch_all_by_ids($uids);
			
			$cd_ids = array();
			foreach($members as $member) {
				$cd_ids[] = $member['cd_id'];
			}

			// 获取部门信息
			$departments = voa_h_department::get_multi($cd_ids);

			$list = array();
			foreach($tjs as $tj) {
				if($status == 0) {
					$list[] = array(
						'username' => $members[$tj['m_uid']]['m_username'],
						'cd_name' => $departments[$members[$tj['m_uid']]['cd_id']]['cd_name'],
						'my_begin_time' => rgmdate($tj['my_begin_time'], 'Y-m-d H:i'),
						'my_end_time' => rgmdate($tj['my_end_time'], 'Y-m-d H:i'),
						'my_time' => $tj['my_time'],
						'my_score' => $tj['my_score'],
						'status' => $tj['my_is_pass'] == 1 ? '已通过' : '未通过',
					);
				} else {
					$list[] = array(
						'username' => $members[$tj['m_uid']]['m_username'],
						'cd_name' => $departments[$members[$tj['m_uid']]['cd_id']]['cd_name'],
						'created' => gmdate('Y-m-d H:i', $tj['created']),
						'status' => '未参与',
					);
				}
			}

			$this->__create_excel($list, $fileds);
		}

		exit; 
	}

	/**
	 * 生成xls表格文件
	 * @param array $list
	 * @return string
	 **/

	private function __create_excel($list, $fields) {
		list( $title_string, $title_width, $row_data ) = $this->__excel_data($list, $fields);
		excel::make_excel_download('考试统计', $title_string, $title_width, $row_data);
	}

	/**
	 * 将数据转换成excle表格所需要个格式
	 * @param array $data
	 * @return array
	 **/
	private function __excel_data($data, $fileds) {
		$field2colnum = array(); // 字段与excel列字母对应关系
		$titleString  = array(); // excel 标题栏文字
		$titleWidth   = array(); // excel 标题栏宽度
		$excelData    = array(); // excel 行数据
		$ord          = 65; // 第一列字母A的ASCII码值
		foreach($fileds as $key => $arr ) {
			$colCode                 = chr( $ord );
			$field2colnum[ $key ]    = $colCode;
			$titleString[ $colCode ] = $arr['name'];
			$titleWidth[ $colCode ]  = $arr['width'];
			$ord ++;
		}
		$i = 0;
		foreach( $data as $row ) {
			foreach( $field2colnum as $k => $col ) {
				$excelData[ $i ][ $col ] = isset( $row[ $k ] ) ? $row[ $k ] : '';
			}
			$i ++;
		}

		return array(
			$titleString,
			$titleWidth,
			$excelData
		);
	}
}
