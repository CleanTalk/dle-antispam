<?php
if (!defined('DATALIFEENGINE')) {
    die("Hacking attempt!");
}

require_once ENGINE_DIR . '/modules/cleantalk/cleantalk.class.php';
require_once ENGINE_DIR . '/modules/cleantalk/ct_functions.php';
list($ct_config, $ct_config_serialized) = ct_get_config($db);

if ($ct_config['ct_enable_mod']) {
    $ct = new Cleantalk();

    // Хак дабы можно было корректно вырезать комментарий сервера, после преобразований текста внутри dle
    $comments = str_replace('\n', "\n", $comments);

    $comments = $ct->delCleantalkComment($comments);
}
?>
