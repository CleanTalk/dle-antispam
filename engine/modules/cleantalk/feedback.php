<?php
if (!defined('DATALIFEENGINE')) {
    die("Hacking attempt!");
}

require_once ENGINE_DIR . '/modules/cleantalk/ct_functions.php';
list($ct_config, $ct_config_serialized) = ct_get_config($db);
if ($stop == '' && $ct_config['ct_enable_feedback']) {
   
    require_once ENGINE_DIR . '/modules/cleantalk/cleantalk.class.php';
    
    $ct_lang = get_ct_lang();

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

    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
    {
            $forwarded_for = (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) ? htmlentities($_SERVER['HTTP_X_FORWARDED_FOR']) : '';
    }
    $sender_ip = ($_IP == '127.0.0.1' && !empty($forwarded_for)) ? $forwarded_for : $_IP;
    
    $ct_check_key = ct_generation_check_key();
    $checkjs = 0;
    if (isset($_POST['ct_checkjs']) && preg_match("/$ct_check_key/", $_POST['ct_checkjs'])) {
        $checkjs = 1;
    }

    $ct_submit_register_time = time() - $_SESSION['ct_submit_register_time'];
    
    $post_info['comment_type'] = 'feedback';
    $post_info = json_encode($post_info);

    $ct_request = new CleantalkRequest();
    $ct_request->auth_key = $ct_config['ct_key'];
    $ct_request->message = $message;
    $ct_request->example = null; 
    $ct_request->sender_email = $email;
    $ct_request->sender_nickname = $name;
    $ct_request->sender_ip = $sender_ip;
    $ct_request->agent = 'dle-'.$ct_config['ct_version'];
    $ct_request->sender_info = $sender_info;
    $ct_request->js_on = $checkjs;
    $ct_request->submit_time = $ct_submit_register_time;
    $ct_request->post_info = $post_info;

    // Принудительно отключаем проверку стоп-слов, т.к. сообщения с формы обратной связи не являются публичной информацией
    $ct_request->stoplist_check = 0;
 
    $ct = new Cleantalk();
    $ct->work_url = $ct_config['ct_work_url'];
    $ct->server_url = $ct_config['ct_server_url'];
    $ct->server_ttl = $ct_config['ct_server_ttl'];
    $ct->server_changed = $ct_config['ct_server_changed'];
    $ct->data_codepage = isset($config['charset']) && $config['charset'] != 'utf-8' ? $config['charset'] : null;

    $ct_result = $ct->isAllowMessage($ct_request);

    if ($ct_result->errno == 0) {
      if ($ct_result->allow == 0 && $ct_result->stop_queue == 1) {
            $stop = charset(addslashes($ct_result->comment), $config['charset']);

            // Для совместимости с кодом DLE
            $stop = stripslashes($stop);

            $ct_time = time() + ($config['date_adjust'] * 60);
            $ct_log_extras = 'Username: ' . $name . ', email: ' . $email . '. ' . charset($ct_result->comment, $config['charset']);
            ct_log(null, $ct_time, $_SERVER['REMOTE_ADDR'], 0, $ct_log_extras);
        }
        // If the server has changed, is changing the config
        if ($ct->server_change) {
            ct_set_config('ct_work_url', $ct->work_url);
            ct_set_config('ct_server_ttl', $ct->server_ttl);
            ct_set_config('ct_server_changed', time());
        }
    } else {
        $ct_log_extras = charset($ct_result->errstr, $config['charset']);
        ct_log(null, $_TIME, $_IP, 0, $ct_log_extras);
        
        feedback_admin($ct_config['ct_server_url'], $ct_result->errstr);
    }
}
?>
