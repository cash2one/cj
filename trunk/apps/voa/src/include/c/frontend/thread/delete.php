<?php

/**
 * 删除工作台信息
 * $Author$
 * $Id$
 */
class voa_c_frontend_thread_delete extends voa_c_frontend_thread_base
{

    public function execute()
    {
        $tid = intval($this->request->get('tid'));

        try {
            // 事务开始
            voa_uda_frontend_transaction_abstract::s_begin();

            $uda = &uda::factory('voa_uda_frontend_thread_delete');
            $result = array();
            if (! $uda->execute(array(
		                'tid' => $tid
		            ), $result)) {
                $this->_error_message($uda->errmsg);
                return true;
            }

            // 提交事务
            voa_uda_frontend_transaction_abstract::s_commit();
        } catch (help_exception $e) {
            // 事务回滚
            voa_uda_frontend_transaction_abstract::s_rollback();
            $this->_error_message($e->getMessage());
            return true;
        }

        $this->_json_message();
    }
}

