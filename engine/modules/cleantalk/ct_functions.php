<?php

if (!defined('DATALIFEENGINE')) {
    die("Hacking attempt!");
}

/**
 * Параметры модуля
 * @return array Config array
 */
function ct_get_config($db) {
    $test = $db->query("SHOW TABLES LIKE '" . PREFIX . "_ct_config'", false);
    if ($test->num_rows == 1) {
        $ct_config = $db->super_query("SELECT * FROM " . PREFIX . "_ct_config", true);

        $config = array();
        $config_serialized = array();
        foreach ($ct_config as $_config) {
            if ($_config['serialized'] == 0) {
                $config[$_config['key']] = $_config['value'];
                $config_serialized[$_config['key']] = 0;
            } else {
                $config[$_config['key']] = unserialize($_config['value']);
                $config_serialized[$_config['key']] = 1;
            }
        }

        return array($config, $config_serialized);
    } else {
        return null;
    }
}

function ct_set_config($key, $value) {
    global $db;
    $db->query("UPDATE  `" . PREFIX . "_ct_config` SET `value`='{$value}'  WHERE `key`='{$key}'");
}

/*
 * ct_server_changed
  ct_server_ttl
  ct_work_url
 */

/**
 * Список доступных языков
 * @return array Массив языков
 */
function ct_get_languages() {
    $language_list = array();
    $dirs = opendir(ROOT_DIR . '/language/');
    while ($dir = readdir($dirs)) {
        if (!is_file($dir) && $dir != '.' && $dir != '..') {
            if (file_exists(ROOT_DIR . '/language/' . $dir . '/cleantalk.lng')) {
                $language_list[$dir] = $dir;
            }
        }
    }

    return $language_list;
}

function debug($var, $stop = 0) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';

    if (!empty($stop)) {
        exit();
    }
}

function charset($items, $charset) {
    if ($charset == 'utf-8') {
        return $items;
    }

    if (is_array($items)) {
        foreach ($items as $key => &$item) {
            if (is_array($item)) {
                charset($item, $charset);
            } else {
                $item = iconv("UTF-8", $charset, $item);
            }
        }

        return $items;
    } else {
        $items = iconv("UTF-8", $charset, $items);
        return $items;
    }
}

function charset_from($items, $charset) {
    if ($charset == 'utf-8') {
        return $items;
    }

    if (is_array($items)) {
        foreach ($items as $key => &$item) {
            if (is_array($item)) {
                charset($item, $charset);
            } else {
                $item = iconv($charset, "UTF-8", $item);
            }
        }

        return $items;
    } else {
        $items = iconv($charset, "UTF-8", $items);
        return $items;
    }
}

function get_ct_lang() {
    global $config;
    $lang = array();
    $selected_language = $config['langs'];
    if (file_exists(ROOT_DIR . '/language/' . $selected_language . '/cleantalk.lng')) {
        require_once (ROOT_DIR . '/language/' . $selected_language . '/cleantalk.lng');
    }
    return $lang;
}

function feedback_admin($subject, $message) {
    global $db, $config;
    $admins = $db->super_query("SELECT email FROM " . USERPREFIX . "_users WHERE user_group='1' AND allow_mail = '1'");

    include_once ENGINE_DIR . '/classes/mail.class.php';
    $mail = new dle_mail($config);

    foreach ($admins as $email) {
        $mail->send($email, $subject, $message);
    }
}

function ct_generation_check_key() {
    global $config, $ct_config;

    return md5($ct_config['ct_key'] . '+' . $config['admin_mail'] . '+' . date("d"));
}

?>
