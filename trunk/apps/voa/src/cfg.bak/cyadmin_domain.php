<?php
/**
 * cyadmin_domain.php
 * 总后台CRM相关
 * Created by zhoutao.
 * Created Time: 2015/8/13  18:08
 */

// 总后台地址
$conf['domain_url'] = 'cy.admin.vchangyi.com';

// UC地址
$conf['uc_domain'] = 'uc.vhcnagyi.com';

// ref标记
$conf['ref_domain'] = 'cyadmin';

// 免费应用
$conf['free_plugin'] = array('addressbook', 'sign', 'project', 'news', 'invite');

// 免费使用的人数上限
$conf['free_use_number'] = 30;

// 试用期时间 保险配置
$conf['free_time'] = 15;

// 免费用户提示语
$conf['free_message'] = sprintf('没有超过免费人数(%d),为免费用户', $conf['free_use_number']);