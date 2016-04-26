<?php

/**
 * dailyreport.php
 * 日报菜单设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */
// 前台菜单配置
$conf['menu.frontend'] = array();
// 后台菜单配置
$conf['menu.admincp'] = array(
    'main' => array(
        'icon' => 'fa-list', 'name' => '报告列表', 'display' => 1,
        'subnavdisplay' => 1, 'displayorder' => 105, 'default' => 1
    ),
     'template' => array(
        'icon' => 'fa-file-text-o', 'name' => '报告模板设置', 'display' => 1,
        'subnavdisplay' => 1, 'displayorder' => 106, 'default' => 0
    ),
    'wechat' => array(
        'icon' => 'fa-cog', 'name' => '微信菜单设置', 'display' => 1,
        'subnavdisplay' => 1, 'displayorder' => 107, 'default' => 0
    )
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
    array(
        'type' => 'view', 'name' => '新建报告', 'url' => '{domain_url}/Dailyreport/Frontend/Index/NewDailyreport',
    ),
    array(
        'name' => '我收到的', 'sub_button' => array(
            array(
                'type' => 'view', 'name' => '与我相关的', 'url' => '{domain_url}/Dailyreport/Frontend/Index/AboutMe',
            ),
            array(
                'type' => 'view', 'name' => '我负责的', 'url' => '{domain_url}/Dailyreport/Frontend/Index/Responsibles',
            ),
        ),
    ),
    array(
        'name' => '我发起的', 'sub_button' => array(
            array(
                'type' => 'view', 'name' => '草稿', 'url' => '{domain_url}/Dailyreport/Frontend/Index/Draft',
            ),
            array(
                'type' => 'view', 'name' => '已发出的', 'url' => '{domain_url}/Dailyreport/Frontend/Index/SendList',
            ),
        ),
    )
);