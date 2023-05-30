<?php
/**
 * Plugin Name: TextMod - Wordpress Comments Addon Plugin
 * Plugin URI: https:/github.com/textmod/textmod-wp-comments-plugin
 * Description: This plugin extends WordPress comments with TextMod filtering capabilities.
 * Version: 1.0.0
 * Author: boris@textmod.xyz
 * Author URI: https://textmod.xyz
 */
include __DIR__ . '/vendor/autoload.php';

use TextMod\WPCommentsAddonPluginSettings;
use TextMod\WPCommentsFilter;

function textmod_wp_comments_addon_init() {
    new WPCommentsAddonPluginSettings();
    WPCommentsFilter::newInstance();
}

add_action('plugins_loaded', 'textmod_wp_comments_addon_init');
