<?php

/**
 * 代理加盟-列表
 * Created by PhpStorm.
 * User: ChangYi(xubinshan)
 * Date: 2015/6/29
 * Time: 14:47
 */
class voa_c_cyadmin_enterprise_agent_list extends voa_c_cyadmin_enterprise_base {

	public function execute() {
		$search_default = array(
			'company'           => '',
			'fullname'          => '',
			'created_begintime' => '',
			'created_endtime'   => ''
		);
		$search_conds   = array(); // 记住查询条件，填充到页面
		$conditions     = array(); // 供查询数据库用的查询条件
		$page_option    = array(); // 分页条件集合
		$this->_parse_search_cond( $search_default, $search_conds, $conditions );
		$issearch = $this->request->get( 'issearch' ) ? 1 : 0;

		$limit = 12; // 每页显示数量
		$page  = $this->request->get( 'page' ); // 当前页码
		if( ! is_numeric( $page ) || $page < 1 ) {
			$page = 1;
		}
		list( $start, $limit, $page ) = voa_h_func::get_limit( $page, $limit );
		$page_option = array(
			$start,
			$limit
		);

		// 载入搜索uda类
		$uda_list = &uda::factory( 'voa_uda_cyadmin_agent_list' );
		// 数据结果
		$result = array();
		// 实际查询条件
		$conditions = $issearch ? $conditions : array();
		$uda_list->list_news( $result, $conditions, $page_option );
		// 导出csv文件
		if( $this->request->post( 'export' ) == 'export' || $this->request->get( 'export' ) == 'export' ) {
			$this->__putout( $conditions );
		}
		// 分页链接信息
		$multi = '';
		if( $result['total'] > 0 ) {
			// 输出分页信息
			$multi = pager::make_links( array(
				'total_items'      => $result['total'],
				'per_page'         => $limit,
				'current_page'     => $page,
				'show_total_items' => true
			) );
		}
		$this->view->set( 'total', $result['total'] );
		$this->view->set( 'list', $result['list'] );
		$this->view->set( 'condi', $search_conds );
		$this->view->set( 'multi', $multi );
		$this->view->set( 'form_delete_url', $this->cpurl( $this->_module, 'agent', 'delete' ) );
		$this->view->set( 'view_url', $this->cpurl( $this->_module, 'agent', 'view', array(
			'aid' => ''
		) ) );
		$this->view->set( 'delete_url', $this->cpurl( $this->_module, 'agent', 'delete', array(
			'acid' => ''
		) ) );
		$this->output( 'cyadmin/agent/list' );
	}

	/**
	 * 重构搜索条件
	 *
	 * @param array $searchDefault
	 *            初始条件
	 * @param array $searchBy
	 *            输入的查询条件
	 * @param array $conditons
	 *            组合的查询
	 */
	protected function _parse_search_cond( $search_default, &$search_conds, &$conditons ) {
		foreach( $search_default as $_k => $_v ) {
			if( isset( $_GET[ $_k ] ) && $_v != $this->request->get( $_k ) ) {
				$search_conds[ $_k ] = $this->request->get( $_k );
				if( $_k == 'created_begintime' ) {
					$conditons['created>=?'] = rstrtotime( $this->request->get( $_k ) );
				} elseif( $_k == 'created_endtime' ) {
					$conditons['created<?'] = rstrtotime( $this->request->get( $_k ) ) + 86400;
				} elseif( $_k == 'company' ) {
					$conditons['company LIKE ?'] = '%' . ( $this->request->get( $_k ) ) . '%';
				} elseif( $_k == 'fullname' ) {
					$conditons['fullname LIKE ?'] = '%' . ( $this->request->get( $_k ) ) . '%';
				} else {
					$conditons[ $_k ] = ( $this->request->get( $_k ) );
				}
			}
		}

		return true;
	}

	/**
	 * 构造下载文件
	 *
	 * @param $condi 查询条件
	 *
	 * @return bool
	 */
	private function __putout( $condi ) {
		$page_option = array();
		// 载入搜索uda类
		$uda_list = &uda::factory( 'voa_uda_cyadmin_agent_list' );
		// 数据结果
		$result = array();
		// 实际查询条件
		$uda_list->list_news( $result, $condi, $page_option );
		if( ! $result['total'] ) {
			$this->message( 'error', '没有数据！' );
		}
		$limit   = '1000';
		$zip     = new ZipArchive();
		$path    = voa_h_func::get_sitedir() . 'excel/';
		rmkdir($path);
		$zipname = $path . 'enterprise' . date( 'YmdHis', time() );
		// 读取数据
		$page   = ceil( $result['total'] / $limit );
		$data   = array();
		$result = null;
		if( ! file_exists( $zipname ) ) {

			$zip->open( $zipname . '.zip', ZipArchive::OVERWRITE );

			for( $i = 1; $i <= $page; $i ++ ) {
				$conlist     = array();
				$page_option = array(
					( $i - 1 ) * $limit,
					$limit
				);
				$uda_list->list_news( $conlist, $condi, $page_option );
				// 格式化列表输出
				if( ! empty( $conlist['list'] ) && is_array( $conlist['list'] ) ) {
					foreach( $conlist['list'] as &$_ca ) {
						$_ca = $this->_profile_format( $_ca );
					}

					if( isset( $_ca ) ) {
						unset( $_ca );
					}
				}
				$result = $this->__create_excel( $conlist['list'], $i, $path );
				if( $result ) {
					$zip->addFile( $result, $i . '.xls' );
				}
			}
			$zip->close();
			$this->__put_header( $zipname . '.zip' );
			$this->__clear( $path );

			return false;
		}
	}

	/**
	 * 生成xls表格文件
	 *
	 * @param array $list
	 * @param int $i
	 * @param string path
	 *
	 * @return string
	 *
	 * */

	private function __create_excel( $list, $i, $path ) {
		$options = array();
		$attrs   = array();
		list( $title_string, $title_width, $row_data ) = $this->__excel_data( $list );
		excel::make_tmp_excel_download( '代理加盟列表', $path . $i . '.xls', $title_string, $title_width, $row_data, $options, $attrs );

		return $path . $i . '.xls';
	}

	/**
	 * 将数据转换成excle表格所需要个格式
	 *
	 * @param array $data
	 * @param boolean $departments
	 * @param boolean $jobs
	 *
	 * @return array
	 * */
	private function __excel_data( $data, $departments = false, $jobs = false ) {
		$_excel_fileds_agent = array(
			'fullname'        => array(
				'name'  => '联系人姓名',
				'width' => 20
			),
			'telephone'       => array(
				'name'  => '联系人电话',
				'width' => 28
			),
			'email'           => array(
				'name'  => '邮箱',
				'width' => 20
			),
			'region'          => array(
				'name'  => '代理区域',
				'width' => 50
			),
			'company'         => array(
				'name'  => '公司名称',
				'width' => 50
			),
			'company_address' => array(
				'name'  => '公司地址',
				'width' => 50
			),
			'remark'          => array(
				'name'  => '公司简介',
				'width' => 100
			),
			'location_ip'     => array(
				'name'  => '提交ip',
				'width' => 18
			),
			'created'         => array(
				'name'  => '提交时间',
				'width' => 20
			)
		);

		$field2colnum = array(); // 字段与excel列字母对应关系
		$titleString  = array(); // excel 标题栏文字
		$titleWidth   = array(); // excel 标题栏宽度
		$excelData    = array(); // excel 行数据
		$ord          = 65; // 第一列字母A的ASCII码值
		foreach( $_excel_fileds_agent as $key => $arr ) {
			$colCode                 = chr( $ord );
			$field2colnum[ $key ]    = $colCode;
			$titleString[ $colCode ] = $arr['name'];
			$titleWidth[ $colCode ]  = $arr['width'];
			$ord ++;
		}
		$i           = 0;
		$departments = false;
		$jobs        = false;
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

	/**
	 * 下载输出至浏览器
	 */
	private function __put_header( $zipname ) {
		if( ! file_exists( $zipname ) ) {
			exit( "下载失败" );
		}
		$file = fopen( $zipname, "r" );
		Header( "Content-type: application/octet-stream" );
		Header( "Accept-Ranges: bytes" );
		Header( "Accept-Length: " . filesize( $zipname ) );
		Header( "Content-Disposition: attachment; filename=" . basename( $zipname ) );
		echo fread( $file, filesize( $zipname ) );
		$buffer = 1024;
		while( ! feof( $file ) ) {
			$file_data = fread( $file, $buffer );
			echo $file_data;
		}
		fclose( $file );
	}

	/**
	 * 清理产生的临时文件
	 */
	private function __clear( $path ) {
		$dh = opendir( $path );
		while( $file = readdir( $dh ) ) {
			if( $file != "." && $file != ".." ) {
				unlink( $path . $file );
			}
		}
	}
}
