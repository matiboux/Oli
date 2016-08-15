<?php
foreach(glob(ABSPATH . 'includes/class/*.php') as $filename) {
    include_once $filename;
}
?>