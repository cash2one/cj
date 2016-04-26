<?php

class voa_d_testing_test extends orm
{
	protected $_table = 'orm_oa.member';
	public function find_all() {
		return $this->_find_all();
	}
	public function find_row() {
		return $this->_find_row();
	}

	public function findOne() {
		return $this->_findOne();
	}
	public function update() {
		return $this->_update();
	}
}
