<?php
foreach(glob(ABSPATH . 'includes/functions/*.php') as $filename) {
    include_once $filename;
}
?>