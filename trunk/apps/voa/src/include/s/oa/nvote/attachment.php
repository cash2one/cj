<?php
/**
 * 投票调研-附件
 * voa_s_oa_nvote_attachment
 * User: luckwang
 * Date: 15/3/9
 * Time: 下午4:32
 */

class voa_s_oa_nvote_attachment extends voa_s_abstract {

    public function __construct() {
        parent::__construct();
    }


    /**
     * 添加附件
     * @param int $at_id 附件id
     * @param int $m_uid 添加用户id
     * @param int $nvote_id 投票id
     * @param int $nvote_option_id 投票选项id
     * @return bool
     */
    public function add($at_id, $m_uid, $nvote_id, $nvote_option_id = 0) {
        $at_id = rintval($at_id);
        if (empty($at_id) || $at_id < 1) {
            return false;
        }
        $attach['at_id'] = $at_id;
        $attach['nvote_id'] = $nvote_id;
        $attach['nvote_option_id'] = $nvote_option_id;
        $attach['m_uid'] = $m_uid;

        return $this->insert($attach);
    }

    /**
     * 获取附件地址
     * @param int $nvote_id 投票id
     * @param int $nvote_option_id 投票选项id
     * @return bool
     */
    public function at_id_by_novte_opton_id($nvote_id, $nvote_option_id = 0) {
        $nvote_id = rintval($nvote_id);
        $nvote_option_id = rintval($nvote_option_id);
        if (empty($nvote_id) || $nvote_id < 1) {
            return null;
        }

        if (empty($nvote_id) || $nvote_id < 1) {
            $nvote_option_id = 0;
        }

        $attach = $this->get_by_conds(array('nvote_id = ?' => $nvote_id, 'nvote_option_id =?' => $nvote_option_id));
        return !empty($attach['at_id']) ? $attach['at_id'] : '';
    }
}
