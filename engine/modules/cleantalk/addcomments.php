<?php

if (!defined('DATALIFEENGINE')) {
    die("Hacking attempt!");
}

require_once ENGINE_DIR . '/modules/cleantalk/cleantalk.class.php';
require_once ENGINE_DIR . '/modules/cleantalk/ct_functions.php';
list($ct_config, $ct_config_serialized) = ct_get_config($db);

$ct_lang = get_ct_lang();
if (in_array($member_id['user_group'], $ct_config['ct_groups']) && !$CN_HALT && $ct_config['ct_enable_comments']
        && $ct_config['ct_enable_mod']) {

    $ct_row_news = $db->super_query("SELECT short_story from " . PREFIX . "_post WHERE id='$post_id'");
    $ct_row_comments = $db->super_query("SELECT text from " . PREFIX . "_comments WHERE post_id='$post_id' AND approve=1 ORDER BY id DESC LIMIT 5", true);

    $ct_text = $ct_row_news['short_story'];

    foreach ($ct_row_comments as $ct_comment) {
        $ct_text .= "\n\n" . $ct_comment['text'];
    }

    $ct_text = $db->safesql($ct_text);

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
    
    $post_info = json_encode(array('post_url' => $refferrer));

    $ct_submit_time = time() - $_SESSION['ct_submit_comment_time'];

    $ct_comments = charset_from($comments, $config['charset']);
    $ct_text = charset_from($ct_text, $dle_config['charset']);
    $ct_mail = charset_from($mail, $config['charset']);
    $ct_name = charset_from($name, $config['charset']);
    
    // Decoding URL from [leech] bbcode
    if (preg_match('/(.+href=")(.+\?url=)([a-z0-9\=]+)(".+)$/i', urldecode($ct_comments), $matches)) {
        $ct_comments = $matches[1] . base64_decode($matches[3]) . $matches[4];  
    }

    $ct = new Cleantalk();
    $ct_request = new CleantalkRequest();

    $ct_request->auth_key = $ct_config['ct_key'];
    $ct_request->message = $ct_comments;
    $ct_request->sender_email = $ct_mail;
    $ct_request->sender_nickname = $ct_name;
    $ct_request->example = $ct_text;
    $ct_request->agent = 'dle-'.$ct_config['ct_version'];
    $ct_request->sender_info = $sender_info;
    $ct_request->sender_ip = $ct->ct_session_ip($_SERVER['REMOTE_ADDR']);
    $ct_request->post_info = $post_info;
    $ct_request->submit_time = $ct_submit_time;
    $ct_request->js_on = $checkjs;

    $ct->work_url = $ct_config['ct_work_url'];
    $ct->server_url = $ct_config['ct_server_url'];
    $ct->server_ttl = $ct_config['ct_server_ttl'];
    $ct->server_changed = $ct_config['ct_server_changed'];
    $ct->data_codepage = isset($config['charset']) && $config['charset'] != 'utf-8' ? $config['charset'] : null;

    // Check
    $ct_result = $ct->isAllowMessage($ct_request);
    if ($ct_result->errno == 0) {
        
        // If the server has changed, is changing the config
        if ($ct->server_change) {
            ct_set_config('ct_work_url', $ct->work_url);
            ct_set_config('ct_server_ttl', $ct->server_ttl);
            ct_set_config('ct_server_changed', time());
        }

        if ($ct_result->allow == 1) {
            /**
             * Disable premoderation
             */
            $config['allow_cmod'] = false;

            /**
             * Combine?
             */
            if ($config['allow_combine']) {
                $ct_row = $db->super_query("SELECT id, post_id, user_id, date, text, ip, is_register, approve FROM " . PREFIX . "_comments WHERE post_id = '$post_id' ORDER BY id DESC LIMIT 0,1");
                if ($ct_row['id']) {
                    if (preg_match('/\*\*\*.+\*\*\*/', $ct_row['text'])) {
                        $config['allow_combine'] = false;
                    }
                }
            }
        } else {
            if ($ct_result->stop_queue == 1) {
                $stop[] = charset($ct_result->comment, $config['charset']);
                $CN_HALT = TRUE;
            } else {
                $config['allow_cmod'] = $user_group[$member_id['user_group']]['allow_modc'] = true;
                $comments = $ct->addCleantalkComment(charset($ct_comments, $config['charset']), charset($ct_result->comment, $config['charset']));

                /**
                 * Disable combine
                 */
                $config['allow_combine'] = false;
            }
        }

    } else {
        feedback_admin($ct_config['ct_server_url'], $ct_result->errstr);
    }
}
?>
