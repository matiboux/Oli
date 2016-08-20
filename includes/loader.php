<?php
/** ------------------- */
/** LOAD INCLUDES FILES */
/** ------------------- */

/** Include Oli files */
foreach(array_merge(glob(INCLUDEPATH . '*.php'), glob(INCLUDEPATH . '*.php')) as $filename) {
    require_once $filename;
}

/** Load Addons files */
foreach(array_merge(glob(ADDONSPATH . '*.php'), glob(ADDONSPATH . '*/*.php')) as $filename) {
    include_once $filename;
}
?>