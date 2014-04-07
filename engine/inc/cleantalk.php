<?php

if (!defined('DATALIFEENGINE') OR !defined('LOGGED_IN')) {
    die("Hacking attempt!");
}

/* if( ! $user_group[$member_id['user_group']]['admin_newsletter'] ) {
  msg( "error", $lang['index_denied'], $lang['index_denied'] );
  } */


require_once ENGINE_DIR . '/modules/cleantalk/ct_functions.php';


if (file_exists(ROOT_DIR . '/language/' . $selected_language . '/cleantalk.lng')) {
    require_once (ROOT_DIR . '/language/' . $selected_language . '/cleantalk.lng');
}

include_once(ENGINE_DIR . '/api/api.class.php');
$ct_result = $db->super_query("SELECT COUNT(*) AS c FROM " . PREFIX . "_admin_sections WHERE name='cleantalk'");

function ct_echoheader($title, $image, $menu = 0) {
    global $lang;
    echoheader($image, $title);
    if ($menu) {
        echo "<div style=\"padding-top:5px;padding-bottom:2px;\">
<table width=\"100%\">
    <tbody><tr>
        <td width=\"4\"><img width=\"4\" border=\"0\" height=\"4\" src=\"engine/skins/images/tl_lo.gif\"></td>
        <td background=\"engine/skins/images/tl_oo.gif\"><img width=\"1\" border=\"0\" height=\"4\" src=\"engine/skins/images/tl_oo.gif\"></td>
        <td width=\"6\"><img width=\"6\" border=\"0\" height=\"4\" src=\"engine/skins/images/tl_ro.gif\"></td>
    </tr>
    <tr>
        <td background=\"engine/skins/images/tl_lb.gif\"><img width=\"4\" border=\"0\" height=\"1\" src=\"engine/skins/images/tl_lb.gif\"></td>
        <td bgcolor=\"#FFFFFF\" style=\"padding:5px;\">
<table width=\"100%\">
    <tbody><tr>
        <td bgcolor=\"#EFEFEF\" height=\"29\" style=\"padding-left:10px;\"><div class=\"navigation\">{$lang['ct_module_name']} {$lang['ct_module_release']}</div></td>
    </tr>
</tbody></table>
<div class=\"unterline\"></div>
<table width=\"100%\">
    <tbody><tr>
        <td style=\"padding:2px;\">
<table width=\"100%\" height=\"35px\" style=\"text-align:center;\">
<tbody><tr style=\"vertical-align:middle;\">
 </td><td class=\"tableborder\"><a href=\"admin.php?mod=cleantalk\"><img border=\"0\" src=\"engine/skins/images/cleantalk_m.png\" title=\"{$lang['ct_module_settings']}\"><br>{$lang['ct_module_settings']}</a></td>
<td class=\"tableborder\"><a href=\"admin.php?mod=cleantalk_logs\"><img border=\"0\" src=\"engine/skins/images/cleantalk_logs_m.png\" title=\"{$lang['ct_logs_list']}\"><br>{$lang['ct_logs_list']}</a></td>
</tr>
</tbody></table>
</td>
    </tr>
</tbody></table>
</td>
        <td background=\"engine/skins/images/tl_rb.gif\"><img width=\"6\" border=\"0\" height=\"1\" src=\"engine/skins/images/tl_rb.gif\"></td>
    </tr>
    <tr>
        <td><img width=\"4\" border=\"0\" height=\"6\" src=\"engine/skins/images/tl_lu.gif\"></td>
        <td background=\"engine/skins/images/tl_ub.gif\"><img width=\"1\" border=\"0\" height=\"6\" src=\"engine/skins/images/tl_ub.gif\"></td>
        <td><img width=\"6\" border=\"0\" height=\"6\" src=\"engine/skins/images/tl_ru.gif\"></td>
    </tr>
</tbody></table>
</div>";
    }
    echo '<div style="padding-top:5px;padding-bottom:2px;">
<table width="100%">
    <tr>
        <td width="4"><img src="engine/skins/images/tl_lo.gif" width="4" height="4" border="0"></td>
        <td background="engine/skins/images/tl_oo.gif"><img src="engine/skins/images/tl_oo.gif" width="1" height="4" border="0"></td>
        <td width="6"><img src="engine/skins/images/tl_ro.gif" width="6" height="4" border="0"></td>
    </tr>
    <tr>
        <td background="engine/skins/images/tl_lb.gif"><img src="engine/skins/images/tl_lb.gif" width="4" height="1" border="0"></td>
        <td style="padding:5px;" bgcolor="#FFFFFF">
<table width="100%">
    <tr>
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">' . $title . '</div></td>
    </tr>
</table>
<div class="unterline"></div>';
    ;
}

function ct_echofooter() {
    echo '</td>
        <td background="engine/skins/images/tl_rb.gif"><img src="engine/skins/images/tl_rb.gif" width="6" height="1" border="0"></td>
    </tr>
    <tr>
        <td><img src="engine/skins/images/tl_lu.gif" width="4" height="6" border="0"></td>
        <td background="engine/skins/images/tl_ub.gif"><img src="engine/skins/images/tl_ub.gif" width="1" height="6" border="0"></td>
        <td><img src="engine/skins/images/tl_ru.gif" width="6" height="6" border="0"></td>
    </tr>
</table>
</div>';
    echofooter();
}

function ct_options_form($options, $selected) {
    $output = null;
    if (is_array($options)) {
        foreach ($options as $value => $name) {
            $output .= '<option value="' . $value . '"';
            if ($value == $selected) {
                $output .= ' selected';
            }
            $output .= '>' . $name . '</option>';
        }
    }

    return $output;
}

/**
 * Установлен модуль?
 */
if (!$ct_result['c']) {

    /**
     * Установка
     */
    if (isset($_GET['install']) || !$ct_result['c']) {

        require_once ENGINE_DIR . '/modules/cleantalk/vqmod.php';


        $ct_config = ct_get_config($db);
        $installed_version = (isset($ct_config[0]['ct_version'])) ? $ct_config[0]['ct_version'] : null;

        $vqmod = new VQMod(getcwd(), $installed_version);
        $vqmod->test = true;
        if ($vqmod->run() == true) {
            $vqmod->test = false;
            $vqmod->run();
        }

        if ($vqmod->version_to_delete != null) {
            $dle_api->uninstall_admin_module('cleantalk');
            $dle_api->uninstall_admin_module('cleantalk_logs');
            $db->query("DROP TABLE IF EXISTS  `" . PREFIX . "_ct_config`");
            $db->query("DROP TABLE IF EXISTS  `" . PREFIX . "_ct_logs`");
            $db->query("ALTER TABLE `" . PREFIX . "_users` DROP `ct_request_id`");
            $db->query("ALTER TABLE `" . PREFIX . "_comments` DROP `ct_request_id`");
        }
        /**
         * @TODO
         * LOG FILE COULD NOT BE WRITTEN
         */
        ct_echoheader($lang['ct_module_name'] . ' ' . $lang['ct_module_release'], "cleantalk");
        if (empty($vqmod->errors)) {

echo <<<HTML
   <p>{$lang['ct_install_patches']}:</p>
HTML;

            foreach ($vqmod->patched_files as $file) {
            echo <<<HTML
            <li>$file&nbsp;<span style="color: #49C73B; font-weight: bold;">OK</span></li>
HTML;
            
            }
echo <<<HTML
   <p>{$lang['ct_setup_db']}</p>
HTML;

            $installed_version = $vqmod->version_to_install;

            $dle_api->install_admin_module('cleantalk', $lang['ct_module_name'], $lang['ct_module_about'], 'cleantalk.png', '1');
            $dle_api->install_admin_module('cleantalk_logs', $lang['ct_log_module_name'], $lang['ct_module_logs_about'], 'cleantalk_logs.png', '1');

            $ct_table_charset = ($config['charset'] == 'windows-1251') ? 'cp1251' : 'utf8';

            $db->query("CREATE TABLE IF NOT EXISTS `" . PREFIX . "_ct_logs` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `name` varchar(40) NOT NULL DEFAULT '',
                    `date` int(11) unsigned NOT NULL DEFAULT '0',
                    `ip` varchar(16) NOT NULL DEFAULT '',
                    `action` int(11) NOT NULL DEFAULT '0',
                    `extras` text NOT NULL,
                    PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET={$ct_table_charset};");

            $db->query("CREATE TABLE IF NOT EXISTS `" . PREFIX . "_ct_config` (
                    `key` varchar(32) NOT NULL,
                    `value` text NOT NULL,
                    `serialized` tinyint(1) NOT NULL DEFAULT '0',
                    PRIMARY KEY(`key`)
	) ENGINE=MyISAM DEFAULT CHARSET={$ct_table_charset};");

            if ($vqmod->version_to_delete != null) {
                $db->query("INSERT IGNORE INTO `" . PREFIX . "_ct_config` (`key`, `value`, `serialized`) VALUES
			('ct_groups', '" . serialize($ct_config[0]['ct_groups']) . "', '1'),
			('ct_enable_feedback', '" . $ct_config[0]['ct_enable_feedback'] . "', '0'),
			('ct_enable_comments', '" . $ct_config[0]['ct_enable_comments'] . "', '0'),
			('ct_key', '" . $ct_config[0]['ct_key'] . "', '0'),
			('ct_partner_id', '" . $ct_config[0]['ct_partner_id'] . "', '0'),
			('ct_enable_mod', '" . $ct_config[0]['ct_enable_mod'] . "', '0'),
                        ('ct_show_partner_link', '" . $ct_config[0]['ct_show_partner_link'] . "', '0'),
			('ct_work_url', '" . $ct_config[0]['ct_work_url'] . "', '0'),
			('ct_server_ttl', '" . $ct_config[0]['ct_server_ttl'] . "', '0'),
			('ct_server_changed', '" . $ct_config[0]['ct_server_changed'] . "', '0'),
			('ct_version', '{$installed_version}', '0'),
			('ct_server_url', 'http://moderate.cleantalk.ru', '0')");
            } else {
                $db->query("INSERT IGNORE INTO `" . PREFIX . "_ct_config` (`key`, `value`, `serialized`) VALUES
			('ct_groups', 'a:1:{i:0;s:1:\"5\";}', '1'),
			('ct_enable_feedback', '1', '0'),
			('ct_enable_comments', '1', '0'),
			('ct_key', '', '0'),
			('ct_partner_id', '', ''),
			('ct_enable_mod', '1', '0'),
                        ('ct_show_partner_link', '', '0'),
			('ct_work_url', '', '0'),
			('ct_server_ttl', '', '0'),
			('ct_server_changed', '', '0'),
			('ct_version', '{$installed_version}', '0'),
			('ct_server_url', 'http://moderate.cleantalk.ru', '0')");
            }
            $db->query("ALTER TABLE `" . PREFIX . "_users` ADD `ct_request_id` VARCHAR( 32 ) NOT NULL");
            $db->query("ALTER TABLE `" . PREFIX . "_comments` ADD `ct_request_id` VARCHAR( 32 ) NOT NULL");

            echo <<<HTML
            <br />
            <form method="POST" action="admin.php?mod=cleantalk">
                    <center>
                    <h3>{$lang['ct_installed']}</h3>

                <input type="submit" value="{$lang['ct_install_next']}">
                <center></form>
HTML;

        } else {

            echo <<<HTML
   <h2>Errors</h2>
{$vqmod->errors}
HTML;

            echo <<<HTML
            <br /><br />
        <h3>
{$lang['ct_not_installed']}
        </h3>
HTML;
        }

        ct_echofooter();
        exit();
    }
}

/**
 * Удаление модуля
 */
if (isset($_POST['ct_uninstall'])) {
    require_once ENGINE_DIR . '/modules/cleantalk/vqmod.php';

    $ct_config = ct_get_config($db);
    $installed_version = (isset($ct_config[0]['ct_version'])) ? $ct_config[0]['ct_version'] : null;


    $vqmod = new VQMod(getcwd(), $installed_version, true);

    $vqmod->remove($installed_version);
    
    ct_echoheader($lang['ct_module_name'] . ' ' . $lang['ct_module_release'], "cleantalk");
    $dle_api->uninstall_admin_module('cleantalk');
    $dle_api->uninstall_admin_module('cleantalk_logs');
    $db->query("DROP TABLE IF EXISTS  `" . PREFIX . "_ct_config`");
    $db->query("DROP TABLE IF EXISTS  `" . PREFIX . "_ct_logs`");
    $db->query("ALTER TABLE `" . PREFIX . "_users` DROP `ct_request_id`");
    $db->query("ALTER TABLE `" . PREFIX . "_comments` DROP `ct_request_id`");

    echo <<<HTML
        <form method="POST" action="admin.php">
                <center>
        {$lang['ct_uninstalled']}<br><br>
            <input type="submit" value="{$lang['ct_return_to_cp']}">
            <center></form>
HTML;


    if (!empty($vqmod->errors)) {
        echo <<<HTML
   <h2>Errors</h2>
{$vqmod->errors}
HTML;
    }

    ct_echofooter();
    
    exit();
}
/**
 * Конфиг
 */
list($ct_config, $ct_config_serialized) = ct_get_config($db);

if (isset($_GET['update'])) {
    $dle_api->install_admin_module('cleantalk_logs', $lang['ct_log_module_name'], $lang['ct_module_logs_about'], 'cleantalk_logs.png', '1');

    $ct_table_charset = ($config['charset'] == 'windows-1251') ? 'cp1251' : 'utf8';

    $db->query("CREATE TABLE IF NOT EXISTS `" . PREFIX . "_ct_logs` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(40) NOT NULL DEFAULT '',
                `date` int(11) unsigned NOT NULL DEFAULT '0',
                `ip` varchar(16) NOT NULL DEFAULT '',
                `action` int(11) NOT NULL DEFAULT '0',
                `extras` text NOT NULL,
                PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET={$ct_table_charset};");

    $db->query("INSERT IGNORE INTO `" . PREFIX . "_ct_config` (`key`, `value`, `serialized`) VALUES
            ('ct_partner_id', '', ''),
            ('ct_enable_mod', '1', '0'),
            ('ct_show_partner_link', '', '0')");

    $db->query("ALTER TABLE `" . PREFIX . "_users` ADD `ct_request_id` VARCHAR( 32 ) NOT NULL");
    $db->query("ALTER TABLE `" . PREFIX . "_comments` ADD `ct_request_id` VARCHAR( 32 ) NOT NULL");

    ct_echoheader($lang['ct_module_name'] . ' ' . $lang['ct_module_release'], "cleantalk");
    echo <<<HTML
            <form method="POST" action="admin.php?mod=cleantalk">
                    <center>
            {$lang['ct_update_success']}<br><br>
                <input type="submit" value="{$lang['ct_install_next']}">
                <center></form>
HTML;
    ct_echofooter();
    exit();
}

/**
 * Сохранение изменений
 */
if (isset($_POST['ct_save'])) {
    $ct_request = array();
    $ct_request['ct_enable_feedback'] = (in_array(intval($_POST['ct_enable_feedback']), array(0, 1))) ? intval($_POST['ct_enable_feedback']) : false;
    $ct_request['ct_enable_comments'] = (in_array(intval($_POST['ct_enable_comments']), array(0, 1))) ? intval($_POST['ct_enable_comments']) : false;
    $ct_request['ct_stop_words'] = (in_array(intval($_POST['ct_stop_words']), array(0, 1))) ? intval($_POST['ct_stop_words']) : false;
    $ct_request['ct_enable_mod'] = (in_array(intval($_POST['ct_enable_mod']), array(0, 1))) ? intval($_POST['ct_enable_mod']) : false;
    $ct_request['ct_links'] = (in_array(intval($_POST['ct_links']), array(0, 1))) ? intval($_POST['ct_links']) : false;
    $ct_request['ct_language'] = (in_array($db->safesql($_POST['ct_language']), array('ru', 'en'))) ? $db->safesql($_POST['ct_language']) : false;

    $_POST['ct_key'] = substr($_POST['ct_key'], 0, 30);
    $ct_request['ct_key'] = $db->safesql($_POST['ct_key']);
    $ct_request['ct_show_partner_link'] = $db->safesql($_POST['ct_show_partner_link']);
    $ct_request['ct_partner_id'] = $db->safesql($_POST['ct_partner_id']);

    if (is_array($_POST['ct_groups'])) {
        $ct_real_post_ct_groups = array();
        foreach ($_POST['ct_groups'] as $ct_post_groups) {
            $ct_post_groups = intval($ct_post_groups);
            if ($ct_post_groups == 0 || $ct_post_groups > 100)
                continue;
            $ct_real_post_ct_groups[] = $ct_post_groups;
        }
        $ct_request['ct_groups'] = $ct_real_post_ct_groups;
    } else {
        $ct_request['ct_groups'] = array();
    }

    /**
     * Запись в бд
     */
    if (is_array($ct_request)) {
        foreach ($ct_request as $key => $value) {
            if ($value !== false && array_key_exists($key, $ct_config)) {
                if ($ct_config_serialized[$key] == 1)
                    $value = serialize($value);

                $db->query("UPDATE  `" . PREFIX . "_ct_config` SET `value`='{$value}'  WHERE `key`='{$key}'");
            }
        }

    ct_echoheader($lang['ct_module_name'] . ' ' . $lang['ct_module_release'], "cleantalk");
    echo <<<HTML
<center>
<br>
{$lang['opt_sysok_1']}
<br><br>
<a href="admin.php?mod=cleantalk">{$lang['func_msg']}</a>
<br><br>
</center>
HTML;
    ct_echofooter();
    exit();
    }
}

/**
 * Главное окно
 */
$ct_group_list = get_groups($ct_config['ct_groups']);

$ct_options_enable_mod = ct_options_form(
        array(0 => $lang['ct_off'], 1 => $lang['ct_on']), $ct_config['ct_enable_mod']
);
$ct_options_enable_comments = ct_options_form(
        array(0 => $lang['ct_off'], 1 => $lang['ct_on']), $ct_config['ct_enable_comments']
);
$ct_options_enable_feedback = ct_options_form(
        array(0 => $lang['ct_off'], 1 => $lang['ct_on']), $ct_config['ct_enable_feedback']
);

ct_echoheader($lang['ct_module_settings'], "cleantalk", 1);
$ct_checked_1 = ($ct_config['ct_show_partner_link'] == 1) ? ' checked' : '';
echo <<<HTML
        <form method="POST" action=""><input type="hidden" name="ct_save" value="1" >
<table width="100%">
    <tr>
        <td width="50%">&nbsp;<b>{$lang['ct_main_setings']}</b></td>
        <td width="50%">&nbsp;<b>{$lang['ct_server_setings']}</b></td>
    </tr>
    <tr>
        <td colspan="2"><div class="unterline"></div></td>
    </tr>
    <tr>
        <td width="50%" valign="top">
            <table width="100%">
                <tr>
                    <td style="padding:6px;">{$lang['ct_enable_mod']}</td>
                    <td><select name="ct_enable_mod">
                    {$ct_options_enable_mod}</select></td>
                </tr>
                <tr>
                    <td style="padding:6px;">{$lang['ct_enable_comments']}</td>
                    <td><select name="ct_enable_comments">
                    {$ct_options_enable_comments}</select></td>
                </tr>
                <tr>
                    <td style="padding:6px;">{$lang['ct_enable_feedback']}</td>
                    <td><select name="ct_enable_feedback">
                    {$ct_options_enable_feedback}</select></td>
                </tr>
                <tr>
                    <td width="220" style="padding:6px;">{$lang['ct_groups']}</td>
                    <td><select name="ct_groups[]" size="6" multiple>
                    {$ct_group_list}
                            </select></td>
                </tr>
            </table>
        </td>
        <td width="50%" valign="top">
        <table width="100%">
                <tr>
                    <td style="padding:6px;">{$lang['ct_key']}</td>
                    <td><input name="ct_key" value="{$ct_config['ct_key']}" ></td>
                </tr>
                <tr>
                    <td style="padding:6px;">{$lang['ct_show_partner_link']}</td>
                    <td><input type="hidden" name="ct_show_partner_link" value="0"><input type="checkbox" name="ct_show_partner_link" value="1"{$ct_checked_1} ></td>
                </tr>
                <tr>
                    <td style="padding:6px;">{$lang['ct_partner_id']}</td>
                    <td><input name="ct_partner_id" value="{$ct_config['ct_partner_id']}" >
                        <br><em style="color: grey;">{$lang['ct_partner_id_example']}</em></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr><td align="center" colspan="2"><br><input type="submit" value="{$lang['ct_save_button']}"></td></tr>
</table>
</form>

<form method="POST" action="" name="form_module_delete">
                    <input type="hidden" value="1" name="ct_uninstall">
<br><br><div style="float: right; color: red; cursor: pointer;" onclick="if (confirm('{$lang['ct_uninstall_confirm']}')) { document.form_module_delete.submit(); } event.returnValue = false; return false;"><img src="pic/delete.png" border="0" style="vertical-align: middle;"> {$lang['ct_uninstall_button']}</div>
</form>
HTML;
ct_echofooter();
?>
