<?php

/**
 * @Author: ppker
 * @Date:   2015-07-28 16:18:04
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-08-19 10:32:56
 */
class voa_c_cyadmin_enterprise_appset_edit extends voa_c_cyadmin_enterprise_base {
	protected $notice_state = array(
		'0' => '试用期',
		'1' => '试用期-即将到期',
		'2' => '试用期-已到期',
		//'3' => '未付费',
		'3' => '已付费',
		'4' => '已付费-即将到期',
		'5' => '已付费-已到期'
	);

	// 套件数组
	protected $mod_array = array();

	public function execute() {
		$postx = $this->request->postx();
		//var_dump($postx);die;
		$uda  = &uda::factory( 'voa_uda_cyadmin_enterprise_appset' );
		$news = &uda::factory( 'voa_uda_cyadmin_enterprise_news' );
		if( $postx ) {
			if(empty($postx['notice'])) $postx['notice'] = array();
			//var_dump($postx);die;
			$data = array();
			$uda->update( $postx, $data );
			if( $uda->errmsg ) {
				$this->message( 'error', $uda->errmsg );
			}
			if( $data ) {
				$this->message( 'success', '保存成功', $this->cpurl( $this->_module, $this->_operation ) );
			}

		}
		// 获取所有数据
		$request = $uda->list_all();
		if( $request['notice']['value'] ) {
			$request['notice']['value'] = unserialize( $request['notice']['value'] );

			$meids = $request['notice']['value']['meid'];
			if( $meids ) {
				$news_data = $news->list_by_pks( $meids );
				$this->view->set( 'news_title', $news_data );
			}
			$this->view->set( 'notice', $request['notice']['value'] );
		}

		//var_dump($this->notice_state);die;

		$this->view->set( 'notice_state', $this->notice_state );
		// 套件id

		$tao_array = $this->_domain_plugin_list;

		$this->mod_array = array_column($tao_array, 'cpg_name', 'cpg_id');
		// $this->mod_array[0] = "请选择";

		$this->view->set( 'mod_array', $this->mod_array );
		
		if( $request ) {
			$this->view->set( 'request', $request );
		}

		// 消息模板搜索
		$mb_search = $this->request->get( 'search' );
		if( $mb_search ) {
			$page     = $this->request->get( 'page' );
			$multi    = '';
			$msg_list = array();
			$total    = '';
			$list     = $news->getlist_search( $page, $msg_list, $multi, $total, $mb_search );
			$mo_data  = array(
				'list'      => $msg_list,
				'multi'     => $multi,
				'mb_search' => $mb_search,
				'total'     => $total
			);

			echo json_encode( $mo_data );
			exit;
		}
		//拼接字符串
		$mod_str = '<select class=\"form-control pull-left tao\" name=\"notice[notice_mod][]\" style=\"width:113px;margin-right:10px;\">';
		
		foreach($this->mod_array as $key=>$val){
			$mod_str .= "<option value=".$key.">".$val."</option>";
		}
		$mod_str .= '</select>';
		$this->view->set('mod_str', $mod_str);
		
		// 获取消息模板
		$page     = $this->request->get( 'page' );
		$multi    = '';
		$msg_list = array();
		$total    = '';
		$list     = $news->getlist( $page, $msg_list, $multi, $total );
		$data1    = array();
		$news->format( $msg_list, $data1 );
		$this->view->set( 'data1', $data1 );
		$this->view->set( 'multi', $multi );
		$this->view->set( 'total', $total );


		$this->output( 'cyadmin/enterprise/appset/edit' );

		return true;
	}

}
