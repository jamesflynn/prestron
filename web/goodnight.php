<?php
    require('pres/webhooks.php');
    ifttt_webhook(true,'all_lights_off');
    smartthings_webhook(true,'lock');
?>

