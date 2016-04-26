<?php
/**
* 收藏导出
* Create By wogu
* $Author$
* $Id$
*/

class voa_c_admincp_office_jobtrain_collexport extends voa_c_admincp_office_jobtrain_base {

	public function execute() {
		$aid = intval($this->request->get('aid'));
		if(!$aid) {
			$this->_error_message('参数错误');
		}

		$fileds = array(
			'm_username' => array(
				'name'  => '姓名',
				'width' => 30
			),
			'department' => array(
				'name'  => '部门',
				'width' => 30
			),
			'job' => array(
				'name'  => '职位',
				'width' => 30
			),
			'mobile' => array(
				'name'  => '手机',
				'width' => 30
			),
			'created' => array(
				'name'  => '收藏时间',
				'width' => 30
			)
		);
		// 获取内容
		$uda = &uda::factory('voa_uda_frontend_jobtrain_article');
		$article = $uda->get_article($aid);

		if(!$article) {
			$this->_error_message('内容不存在');
		}
		// 获取收藏人数
		$conds=array(
			'aid'=>$aid,
		);
		$uda_list = &uda::factory('voa_uda_frontend_jobtrain_coll');
		$uda_list->list_coll($result, $conds);
		$coll_list = $result['list'];
		if(!$result['total']) {
			$this->_error_message('人数为0无法导出');
		}

		if(!empty($coll_list)) {

			$list = array();
			foreach($coll_list as $v) {
				$list[] = array(
					'm_username' => $v['m_username'],
					'department' => $v['department'],
					'job' => $v['job'],
					'mobile' => $v['mobile'],
					'created' => rgmdate($v['created'], 'Y-m-d H:i')
				);
			}

			$this->__create_excel($list, $fileds, $article['title'].'-收藏情况');
		}

		exit; 
	}

	/**
	 * 生成xls表格文件
	 * @param array $list
	 * @return string
	 **/

	private function __create_excel($list, $fields, $title) {
		list( $title_string, $title_width, $row_data ) = $this->__excel_data($list, $fields);
		excel::make_excel_download($title, $title_string, $title_width, $row_data);
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