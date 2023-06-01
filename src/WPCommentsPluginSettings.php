<?php
namespace TextMod;

class WPCommentsPluginSettings {
    public function __construct() {
        add_action('admin_menu', array($this, 'registerSettingsPage'));
        add_action('admin_init', array($this, 'initializeSettings'));
    }

    // Register the settings page
    public function registerSettingsPage() {
        add_options_page(
            'TextMod WP Comments Plugin Settings',
            'TextMod WP Comments Plugin',
            'manage_options',
            'textmod_wp_comments_settings',
            array($this, 'renderSettingsPage')
        );
    }

    // Render the settings page content
    public function renderSettingsPage() {
        ?>
        <div class="wrap">
            <h1>TextMod WP Comments Plugin Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('textmod_wp_comments_settings_group');
                do_settings_sections('textmod_wp_comments_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    // Register and initialize the plugin settings
    public function initializeSettings() {
        register_setting(
            'textmod_wp_comments_settings_group',
            'textmod_wp_comments_settings',
            array($this, 'sanitizeSettings')
        );

        add_settings_section(
            'textmod_wp_comments_sentiments_section',
            'Sentiments Configuration',
            array($this, 'sentimentsSectionCallback'),
            'textmod_wp_comments_settings'
        );

        add_settings_field(
            'textmod_wp_comments_sentiments',
            'Filter Sentiments',
            array($this, 'sentimentsFieldCallback'),
            'textmod_wp_comments_settings',
            'textmod_wp_comments_sentiments_section'
        );

        add_settings_field(
            'textmod_wp_comments_action',
            'Action for Filtered Comments',
            array($this, 'actionFieldCallback'),
            'textmod_wp_comments_settings',
            'textmod_wp_comments_sentiments_section'
        );

        add_settings_field(
            'textmod_wp_comments_textmod_api_key',
            'TextMod API Key',
            array($this, 'textmodApiKeyFieldCallback'),
            'textmod_wp_comments_settings',
            'textmod_wp_comments_sentiments_section'
        );
    }

    // Sanitize the plugin settings
    public function sanitizeSettings($settings) {
        // Sanitize and validate the settings
        foreach ($settings as $key => $value) {
            $settings[$key] = sanitize_text_field($value);
        }

        return $settings;
    }

    // Render the sentiments field
    public function sentimentsFieldCallback() {
        $settings = get_option('textmod_wp_comments_settings');
        $sentiments = [
            TextMod::SPAM,
            TextMod::SELF_PROMOTING,
            TextMod::HATE,
            TextMod::TERRORISM,
            TextMod::EXTREMISM,
            TextMod::PORNOGRAPHIC,
            TextMod::THREATENING,
            TextMod::SELF_HARM,
            TextMod::SEXUAL,
            TextMod::SEXUAL_MINORS,
            TextMod::VIOLENCE,
            TextMod::VIOLENCE_GRAPHIC,
        ];

        foreach ($sentiments as $sentiment) {
            $checked = isset($settings[$sentiment]) && $settings[$sentiment] === 'on' ? 'checked' : '';
            echo "<label><input type='checkbox' name='textmod_wp_comments_settings[$sentiment]' $checked> $sentiment</label><br>";
        }
    }

    // Render the sentiments section description
    public function sentimentsSectionCallback() {
        echo 'Choose the sentiments that should trigger comment filtering.';
    }

    // Render the Action for Filtered Comments field
    public function actionFieldCallback() {
        $settings = get_option('textmod_wp_comments_settings');
        $action = $settings['textmod_action'] ?? 'spam';

        echo "<select name='textmod_wp_comments_settings[textmod_action]'>
                <option value='spam' " . selected('spam', $action, false) . ">Mark as Spam</option>
                <option value='pending' " . selected('pending', $action, false) . ">Mark for Pending</option>
                <option value='trash' " . selected('trash', $action, false) . ">Move to Trash</option>
              </select>";
    }

    // Render the TextMod API Key field
    public function textmodApiKeyFieldCallback() {
        $settings = get_option('textmod_wp_comments_settings');
        $textmodApiKey = $settings['textmod_api_key'] ?? '';
        echo "<p>Get your API key from <a href='https://textmod.xyz' target='_blank'>https://textmod.xyz</a></p>";
        echo "<input type='text' name='textmod_wp_comments_settings[textmod_api_key]' value='$textmodApiKey'>";
    }
}
