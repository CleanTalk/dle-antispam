<?php

if (!defined('DATALIFEENGINE')) {
    die("Hacking attempt!");
}

require_once ENGINE_DIR . '/modules/cleantalk/ct_functions.php';
list($ct_config, $ct_config_serialized) = ct_get_config($db);
if (empty($reg_error) && $ct_config['ct_enable_mod']) {
    require_once ENGINE_DIR . '/modules/cleantalk/cleantalk.class.php';
    require_once ENGINE_DIR . '/modules/cleantalk/ct_functions.php';
    list($ct_config, $ct_config_serialized) = ct_get_config($db);
    $ct_lang = get_ct_lang();

    $ct_submit_register_time = time() - $_SESSION['ct_submit_register_time'];

    $ct_post_checkjs = $parse->process(htmlspecialchars(trim($_POST['ct_checkjs'])));

    if (!isset($_POST['ct_checkjs']) || !isset($_SESSION['ct_check_key'])) {
        $checkjs = null;
    } elseif ($ct_post_checkjs == $_SESSION['ct_check_key']) {
        $checkjs = 1;
    } else {
        $checkjs = 0;
    }

    $refferrer = null;
    if (isset($_SERVER['HTTP_REFERER'])) {
        $refferrer = htmlspecialchars((string) $_SERVER['HTTP_REFERER']);
    }

    $user_agent = null;
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $user_agent = htmlspecialchars((string) $_SERVER['HTTP_USER_AGENT']);
    }

    $ct_cms_lang = ($config['langs'] == 'Russian') ? 'ru' : 'en';
    
    $sender_info = array(
        'cms_lang' => $ct_cms_lang,
        'REFFERRER' => $refferrer,
        'post_url' => $refferrer,
        'USER_AGENT' => $user_agent
    );
    $sender_info = json_encode($sender_info);

    $ct = new Cleantalk();
    $ct_request = new CleantalkRequest();

    $ct_request->auth_key = $ct_config['ct_key'];
    $ct_request->sender_email = $email;
    $ct_request->sender_nickname = $name;
    $ct_request->sender_ip = $ct->ct_session_ip($_SERVER['REMOTE_ADDR']);
    $ct_request->agent = 'dle-'.$ct_config['ct_version'];
    $ct_request->submit_time = $ct_submit_register_time;
    $ct_request->js_on = $checkjs;
    $ct_request->sender_info = $sender_info;

    $ct->work_url = $ct_config['ct_work_url'];
    $ct->server_url = $ct_config['ct_server_url'];
    $ct->server_ttl = $ct_config['ct_server_ttl'];
    $ct->server_changed = $ct_config['ct_server_changed'];
    $ct->data_codepage = isset($config['charset']) && $config['charset'] != 'utf-8' ? $config['charset'] : null;

    $ct_result = $ct->isAllowUser($ct_request);

    if ($ct_result->errno == 0) {
        if ($ct_result->allow == 0) {
            $ct_fill_field = true;
            $reg_error .= $ct_result->comment;
	    $stopregistration = TRUE;
        }
        // If the server has changed, is changing the config
        if ($ct->server_change) {
            ct_set_config('ct_work_url', $ct->work_url);
            ct_set_config('ct_server_ttl', $ct->server_ttl);
            ct_set_config('ct_server_changed', time());
        }
    } else {
        feedback_admin($ct_config['ct_server_url'], $ct_result->errstr);
    }
}
?>
