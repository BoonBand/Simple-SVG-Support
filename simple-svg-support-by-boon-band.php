<?php
/*
Plugin Name: Simple SVG Support by Boon.Band
Plugin URI: https://boon.band/
Description: This plugin adds support for uploading and displaying SVG files in WordPress, developed by <a href="https://boon.band/" target="_blank">Boon.Band</a>.
Version: 1.0.1
Author: Boon.Band
Author URI: https://boon.band/
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class BoonBand_SimpleSVGSupport
{
    public function __construct()
    {
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        if (get_option('boon_band_simple_svg_support_enabled', true)) {
            add_filter('upload_mimes', [$this, 'svg_upload_allow']);
            add_filter('wp_prepare_attachment_for_js', [$this, 'show_svg_in_media_library']);
            add_filter('wp_check_filetype_and_ext', [$this, 'fix_svg_mime_type'], 10, 5);
        }

        add_action('admin_menu', [$this, 'register_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function activate()
    {
        add_option('boon_band_simple_svg_support_enabled', true);
    }

    public function deactivate()
    {
        delete_option('boon_band_simple_svg_support_enabled');
    }

    public function register_settings_page()
    {
        add_options_page(
            'Simple SVG Support',
            'Simple SVG Support',
            'manage_options',
            'simple-svg-support',
            [$this, 'settings_page_callback']
        );
    }

    public function register_settings()
    {
        register_setting('simple_svg_support', 'boon_band_simple_svg_support_enabled');

        add_settings_section(
            'simple_svg_support_section',
            'Enable/Disable',
            null,
            'simple_svg_support'
        );

        add_settings_field(
            'boon_band_simple_svg_support_enabled',
            'Enable SVG Support',
            [$this, 'settings_field_callback'],
            'simple_svg_support',
            'simple_svg_support_section'
        );
    }

    public function settings_page_callback()
    {
        echo '<div class="wrap">';
        echo '<h1><a href="https://github.com/BoonBand/Simple-SVG-Support" target="_blank">Simple SVG Support</a> by Boon.Band</h1>';
        echo '<form action="options.php" method="post">';

        settings_fields('simple_svg_support');

        echo '<div class="simple-svg-support-container">';
        echo '<h2>Enable/Disable</h2>';
        echo '<p>To enable or disable SVG support, check or uncheck the following checkbox and save the changes:</p>';
        echo '<table class="form-table" role="presentation">';
        echo '<tbody>';
        echo '<tr>';
        echo '<th scope="row">Enable SVG Support</th>';
        echo '<td><input type="checkbox" name="boon_band_simple_svg_support_enabled" value="1" ' . checked(1, get_option('boon_band_simple_svg_support_enabled'), false) . '></td>';
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';
        submit_button();
        echo '</div>';

        echo '</form>';
        echo '<strong>Designed with <img draggable="false" role="img" class="emoji" alt="💕" src="https://s.w.org/images/core/emoji/14.0.0/svg/1f495.svg"> by <a href="https://boon.band/" target="_blank" title="Boon.Band">Boon.Band</a></strong>';
        echo '</div>';

        echo '<style>
        .simple-svg-support-container {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 20px;
        }
        h1 a {
            text-decoration: none;
            color: #23282d;
        }
        strong {
            display: block;
            margin-top: 20px;
        }
    </style>';
    }


    public function settings_field_callback()
    {
        $enabled = get_option('boon_band_simple_svg_support_enabled');
        echo '<input type="checkbox" name="boon_band_simple_svg_support_enabled" value="1" ' . checked(1, $enabled, false) . '>';
    }

    public function svg_upload_allow($mimes)
    {
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }

    public function show_svg_in_media_library($response)
    {
        if ($response['mime'] === 'image/svg+xml') {
            $response['image'] = [
                'src' => $response['url'],
            ];
        }
        return $response;
    }


  public function fix_svg_mime_type($data, $file, $filename, $mimes, $real_mime = '')
    {
        if (version_compare($GLOBALS['wp_version'], '5.1.0', '>=')) {
            $dosvg = in_array($real_mime, ['image/svg', 'image/svg+xml']);
        } else {
            $dosvg = ('.svg' === strtolower(substr($filename, -4)));
        }

        if ($dosvg) {
            if (current_user_can('manage_options')) {
                $data['ext'] = 'svg';
                $data['type'] = 'image/svg+xml';
            } else {
                $data['ext'] = $data['type'] = false;
            }
        }

        return $data;
    }
}

// Initialize the plugin
$boon_band_simple_svg_support = new BoonBand_SimpleSVGSupport();