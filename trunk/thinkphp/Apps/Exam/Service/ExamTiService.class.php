<?php
namespace  Exam\Service;

class ExamTiService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();

		// 实例化相关模型
		$this->_d = D("Exam/ExamTi");
	}

	public function list_outer_tis_by_ids($ids) {
		$tis = $this->_d->list_by_pks($ids);
		$return = array();
		foreach ($tis as $ti) {
			$return[] = $this->_get_outer_ti($ti);
		}

		return $return;
	}

	public function list_random($tiku_ids, $rules) {
		$rand_tis = array();

		$tis = $this->_d->list_by_conds(array('tiku_id' => $tiku_ids));
		$map = array();
		foreach ($tis as $v) {
			$map[$v['type']][] = $v;
		}
		foreach($rules as $type => $rule) {
			$rand_keys = (array) array_rand($map[$type], $rule['num']);

			foreach ($rand_keys as $key) {
				$ti = $map[$type][$key];
				$rand_tis[$ti['id']] = $this->_get_outer_ti($ti);
			}
		}

		return $rand_tis;
	}

	public function list_answer_by_tids($tids, $rules) {
		$tis = $this->list_by_pks($tids);

		$answers = array();
		foreach ($tis as $ti) {
			$answers[$ti['id']] = array(
				'score' => $rules[$ti['type']]['score'],
				'answer' => $ti['answer'],
				'type' => $ti['type']
			);
		}

		return $answers;
	}

	public function list_by_ids($ids) {
		$tis = $this->_d->list_by_pks($ids);
		$return = array();
		foreach ($tis as $ti) {
			$return[$ti['id']] = array(
				'answer' => $ti['answer'],
				'tiku_id' => $ti['tiku_id'],
				'type' => $ti['type'],
				'title' => $ti['title'],
				'score' => $ti['score'],
				'options' => empty($ti['options']) ? array() : explode("\r\n", $ti['options']),
			);
		}

		return $return;
	}

	protected function _get_outer_ti($ti) {
		return array(
			'id' => $ti['id'],
			//'tiku_id' => $ti['tiku_id'],
			'type' => $ti['type'],
			'title' => $ti['title'],
			'score' => $ti['score'],
			'options' => empty($ti['options']) ? array() : explode("\r\n", $ti['options']),
		);
	}
}