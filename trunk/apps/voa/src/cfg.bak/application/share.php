<?php
/**
 * askoff.php
 * 请假菜单设置
 * Create By Deepseath
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
    'list' => array(
        'icon' => 'fa-list', 'name' => '投稿列表', 'display' => 1,
        'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
    ),
    'set' => array(
        'icon' => 'fa-gear', 'name' => '投稿设置', 'display' => 1,
        'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 0,
    ),
);


