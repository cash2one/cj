<?php
/**
* 试卷详情
* Create By YanWenzhong
* $Author$
* $Id$
*/
class voa_c_admincp_office_exam_paperdetail extends voa_c_admincp_office_exam_base {

	public function execute() {
		if ($this->_is_post()) {
			$data = $this->request->postx();
			$id = intval($data['id']);
			try {
				$args = array(
					'id' => $id
				);
				$uda = &uda::factory('voa_uda_frontend_exam_paperdetail');

				if($data['editsubmit']) {
					$uda->edit_detail($data, $args);
				} elseif($data['resetsubmit']) {
					$uda->reset_detail($id);
					$this->message('success', '重新生成题目成功', $this->cpurl($this->_module, $this->_operation, 'paperdetail', $this->_module_plugin_id, array('id' => $id)));
				} else {
					$uda->add_detail($data, $args);
				}
			} catch (help_exception $h) {
				$this->_admincp_error_message($h);
			} catch (Exception $e) {
				logger::error($e);
				$this->_admincp_system_message($e);
			}

			$this->message('success', '请设置试卷详细信息', $this->cpurl($this->_module, $this->_operation, 'papersetting', $this->_module_plugin_id, array('id' => $id)));
		}

		$id = intval($this->request->get('id'));
		if(!$id) {
			$this->_error_message('参数错误');
		}

		$s_paper = new voa_s_oa_exam_paper();
		$paper = $s_paper->get_by_id($id);
		if(!$paper) {
			$this->_error_message('试卷不存在');
		}

		if(($paper['type'] == 0 && $paper['use_all'] == 1) || $paper['type'] == 2) {
			//$this->_error_message('此类试卷不用选择题目');
			$this->redirect($this->cpurl($this->_module, $this->_operation, 'addpaper', $this->_module_plugin_id, array('id' => $id), false ));
		}

		$s_ti = new voa_s_oa_exam_ti();

		// 获取题库
		$s_tiku = new voa_s_oa_exam_tiku();
		$tikus = $s_tiku->list_by_ids($paper['tiku']);

		// 获取已有试题
		$s_paperdetail = new voa_s_oa_exam_paperdetail();
		$details = $s_paperdetail->list_by_paperid($id);
		$tis = array();
		if($details) {
			$tids = array();
			foreach($details as $detail) {
				$tids[] = $detail['ti_id'];
			}

			// 获取试题
			$src_tis = $s_ti->list_by_ids($tids);
			$tis = array();
			foreach ($details as $detail) {
				$ti = $src_tis[$detail['ti_id']];
				$ti['score'] = $detail['score'];
				$ti['orderby'] = $detail['orderby'];
				$ti['detail_id'] = $detail['id'];
				$ti['tiku_name'] = $tikus[$ti['tiku_id']]['name'];
				$tis[] = $ti;
			}

			$this->view->set('types', voa_d_oa_exam_ti::$TYPES);
			$this->view->set('tis', $tis);
			$this->view->set('paper', $paper);
			$this->view->set('addpaper_url', $this->cpurl($this->_module, $this->_operation, 'addpaper', $this->_module_plugin_id, array('id' => $id)));
			$this->output('office/exam/paper_detail_edit');
		} else {
			// 获取试题
			$tis = $s_ti->list_by_tiku_ids($paper['tiku']);
			
			if($paper['type'] == 1) {
				$rand_tis = array();
				$map = array();
				foreach ($tis as $k => $v) {
					$map[$v['type']][] = $v;
				}

				foreach($paper['rules'] as $type => $rule) {
					if($rule['num']) {
						$rand_keys = (array) array_rand($map[$type], $rule['num']);
						foreach ($rand_keys as $key) {
							$ti = $map[$type][$key];
							$ti['score']=$rule['score'];
							$rand_tis[$ti['tiku_id'] . '-' . $ti['id']] = $ti;
						}
					}
				}

				ksort($rand_tis);
				$tis = $rand_tis;
			}

			$tiku_id = 0;
			$orderby=0;
			foreach ($tis as &$ti) {
				$ti['tiku_name'] = (!$tiku_id || $ti['tiku_id'] != $tiku_id) ? $tikus[$ti['tiku_id']]['name'] : '';

				$ti['orderby'] = ($ti['orderby']>$orderby) ? $ti['orderby'] : ++$orderby;

				$orderby=$ti['orderby'];

				$tiku_id = $ti['tiku_id'];
			}

			$this->view->set('types', voa_d_oa_exam_ti::$TYPES);
			$this->view->set('tis', $tis);
			$this->view->set('paper', $paper);
			$this->view->set('addpaper_url', $this->cpurl($this->_module, $this->_operation, 'addpaper', $this->_module_plugin_id, array('id' => $id)));
			$this->output('office/exam/paper_detail');
		}
	}
}
