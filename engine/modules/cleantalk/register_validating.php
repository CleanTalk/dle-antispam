<?php

if (!isset($_REQUEST['ct_checkjs'])) {
    require_once ENGINE_DIR . '/modules/cleantalk/ct_functions.php';
    $ct_check_value = ct_generation_check_key();
    $_SESSION['ct_check_key'] = $ct_check_value;

    echo '<html xmlns="http://www.w3.org/1999/xhtml">    
  <head> 
    <meta http-equiv="refresh" content="2;URL=\'' . $_SERVER['REQUEST_URI'] . '&ct_checkjs=0\'" />    
    <script type="text/javascript">
    document.location = "' . $_SERVER['REQUEST_URI'] . '&ct_checkjs=' . $ct_check_value . '";
    </script>
  </head>    
  <body> 
  </body>  
</html>';
    exit();
}

$ct_post_checkjs = $_POST['ct_checkjs'] = $_REQUEST['ct_checkjs'];

?>
