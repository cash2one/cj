<?php

namespace Sign\Service;

class SignScheduleLogService extends AbstractService {

	// 构造方法
	public function __construct() {

		$this->_d = D("Sign/SignScheduleLog");
		parent::__construct();
	}



    /**
     * 获取指定日期的排班规则
     * @param array $params 传入参数
     */
    public function get_schedule_history($params) {

        $record = $this->_d->get_schedule_history($params);
        return $record;
    }

}
