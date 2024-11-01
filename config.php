<?php
/**
 *
 * @copyright   Copyright (c) 2013, SOTMARKET.RU
 * @version     3.0.9 от 03.10.2013
 * @author      автор плагина ( k-v-n@inbox.ru )
 */

$config = array(
    'dumpfile' => array(
        'fileName' => 'wp-content/plugins/sotmarket-affiliate-plugin/_data/' . 'dumpfile.log'
    ),
    'rpc'      => array(
        'serverUrl' => 'http://update.sotmarket.ru/api/rpc.php',
        'encoding'  => 'utf-8',
        'tmpPath'   => 'wp-content/plugins/sotmarket-affiliate-plugin/_data/tmp/',
        'data'      => 'wp-content/plugins/sotmarket-affiliate-plugin/_data/',
        //'imgPath' => 'sotm_images/',
        'tmpExpire' => 48
    )
);
?>