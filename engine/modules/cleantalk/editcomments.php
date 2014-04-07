<?php
if (!defined('DATALIFEENGINE')) {
    die("Hacking attempt!");
}

require_once ENGINE_DIR . '/modules/cleantalk/cleantalk.class.php';
require_once ENGINE_DIR . '/modules/cleantalk/ct_functions.php';
list($ct_config, $ct_config_serialized) = ct_get_config($db);

$ct = new Cleantalk();

$ct_row = $db->super_query("SELECT ct_request_id FROM " . PREFIX . "_comments WHERE id='$id'");
if (!empty($ct_row['ct_request_id']) && $ct_config['ct_enable_mod']) {
    $ct_request_id = $ct_row['ct_request_id'];
    
    $allow = 1;
    $ct_request = new CleantalkRequest();
    $ct_request->feedback = $ct_request_id . ':' . $allow;
    $ct_request->auth_key = $ct_config['ct_key'];

    $ct->debug = 0;
    $ct->work_url = $ct_config['ct_work_url'];
    $ct->server_url = $ct_config['ct_server_url'];
    $ct->server_ttl = $ct_config['ct_server_ttl'];
    $ct->server_changed = $ct_config['ct_server_changed'];
    if ($ct->server_change) {
        ct_set_config('ct_work_url', $ct->work_url);
        ct_set_config('ct_server_ttl', $ct->server_ttl);
        ct_set_config('ct_server_changed', time());
    }

    $ct_result = $ct->sendFeedback($ct_request);
    
    # Хак дабы можно было корректно вырезать комментарий сервера, после преобразований текста внутри dle
    $comm_update = str_replace('\n', "\n", $comm_update);

    $comm_update = $ct->delCleantalkComment($comm_update);
}
?>
