<?php
/**
 * Plugin Name: TextMod - Wordpress Comments Plugin
 * Plugin URI: https:/github.com/textmod/textmod-wp-comments-plugin
 * Description: This plugin extends WordPress comments with TextMod filtering capabilities.
 * Version: 1.0.0
 * Author: boris@textmod.xyz
 * License: GPL3
 * License URI: http://www.gnu.org/licenses/gpl.html
 * Author URI: https://textmod.xyz
 */
include __DIR__ . '/vendor/autoload.php';

use TextMod\WPCommentsPluginSettings;
use TextMod\WPCommentsFilter;

function textmod_wp_comments_plugin_init()
{
    try {

        new WPCommentsPluginSettings();
        WPCommentsFilter::newInstance();

    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}

add_action('plugins_loaded', 'textmod_wp_comments_plugin_init');
