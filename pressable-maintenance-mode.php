<?php
/*
Plugin Name: Maintenance Mode
Plugin URI: https://github.com/pressable/pressable-maintenance-mode
Description: Effortlessly enable maintenance mode on your website with customizable access rules. When activated, non-logged-in visitors or users without editing privileges are greeted with a stylish, user-friendly notice indicating that the website is undergoing maintenance.
Author: Pressable
Version: 1.0.7
Author URI: https://pressable.com/
License: GPL2
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// This condition checks whether PHP is being run from the command line interface (cli).
if ('cli' == php_sapi_name()) {
    return;
}

// Add settings link on plugin page
function maintenance_mode_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=maintenance-mode-settings">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

// Get all allowed login URLs including those from WP Hide plugin
function maintenance_mode_get_allowed_urls() {
    $allowed_urls = array();
    
    // Get custom URLs from settings
    $options = get_option('maintenance_mode_options');
    if (!empty($options['custom_login_urls'])) {
        $custom_urls = explode("\n", $options['custom_login_urls']);
        foreach ($custom_urls as $url) {
            $url = trim($url);
            if (!empty($url)) {
                $allowed_urls[] = $url;
            }
        }
    }
    
    // Check for WP Hide & Security Enhancer plugin's custom login URL
    $whl_page = get_option('whl_page');
    if (!empty($whl_page)) {
        $allowed_urls[] = $whl_page;
    }
    
    return $allowed_urls;
}

// Register plugin settings
function maintenance_mode_register_settings() {
    register_setting('maintenance_mode_options', 'maintenance_mode_options', 'maintenance_mode_options_validate');
    
    add_settings_section(
        'maintenance_mode_main',
        'Maintenance Mode Settings',
        'maintenance_mode_section_text',
        'maintenance-mode-settings'
    );
    
    add_settings_field(
        'custom_login_urls',
        'Custom Login URLs',
        'maintenance_mode_custom_login_urls_input',
        'maintenance-mode-settings',
        'maintenance_mode_main'
    );
}

// Settings section text
function maintenance_mode_section_text() {
    echo '<p>Enter custom login URLs (one per line) that should be accessible during maintenance mode.</p>';
    
    // Show detected WP Hide login URL if available
    $whl_page = get_option('whl_page');
    if (!empty($whl_page)) {
        echo '<div class="notice notice-info inline"><p>';
        echo 'Detected WP Hide login URL: <code>' . esc_html($whl_page) . '</code> (automatically allowed)';
        echo '</p></div>';
    }
}

// Custom login URLs input field
function maintenance_mode_custom_login_urls_input() {
    $options = get_option('maintenance_mode_options');
    $value = isset($options['custom_login_urls']) ? $options['custom_login_urls'] : '';
    echo '<textarea id="custom_login_urls" name="maintenance_mode_options[custom_login_urls]" rows="5" cols="50">' . esc_textarea($value) . '</textarea>';
    echo '<p class="description">Enter one URL per line. These will be accessible during maintenance mode.</p>';
}

// Validate settings
function maintenance_mode_options_validate($input) {
    $new_input = array();
    if (isset($input['custom_login_urls'])) {
        $new_input['custom_login_urls'] = trim($input['custom_login_urls']);
    }
    return $new_input;
}

// Add settings page to menu
function maintenance_mode_add_settings_page() {
    add_options_page(
        'Maintenance Mode Settings',
        'Maintenance Mode',
        'manage_options',
        'maintenance-mode-settings',
        'maintenance_mode_settings_page'
    );
}

// Settings page content
function maintenance_mode_settings_page() {
    ?>
    <div class="wrap">
        <h1>Maintenance Mode Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('maintenance_mode_options');
            do_settings_sections('maintenance-mode-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Activate WordPress Maintenance Mode
function wp_maintenance_mode() {
    // Check if headers have already been sent
    if (headers_sent()) {
        return;
    }

    // Allow users with edit_themes capability
    if (current_user_can('edit_themes')) {
        return;
    }

    // Allow XML-RPC
    if (strpos($_SERVER['REQUEST_URI'], 'xmlrpc.php') !== false) {
        return;
    }

    // Allow Jetpack
    if (strpos($_SERVER['REQUEST_URI'], 'wp-json/jetpack') !== false) {
        return;
    }

    // Allow REST API login (optional)
    if (strpos($_SERVER['REQUEST_URI'], 'wp-json/wp/v2') !== false) {
        return;
    }

    // Allow standard WordPress login and admin
    if (strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false || 
        strpos($_SERVER['REQUEST_URI'], 'wp-admin') !== false) {
        return;
    }

    // Check all allowed URLs including those from WP Hide plugin
    $allowed_urls = maintenance_mode_get_allowed_urls();
    foreach ($allowed_urls as $url) {
        if (!empty($url) && strpos($_SERVER['REQUEST_URI'], $url) !== false) {
            return;
        }
    }

    // If the current user does not have the capability to edit themes or is not logged in
    if (!current_user_can('edit_themes') || !is_user_logged_in()) {
        // Send a raw HTTP header to control the cache settings for the response being sent back to the client/browser
        @header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        // The following header is specifying cache controls as well:
        // - post-check: The freshness of the content directly after a POST request, set to 0 to prevent caching post-request.
        // - pre-check: Similar to post-check but for pre-request content freshness, also set to 0 to avoid caching.
        // The second parameter, false, indicates that this header should not replace a header previously set, which can allow multiple headers of the same type to be sent.
        @header("Cache-Control: post-check=0, pre-check=0", false);
          // The "Pragma: no-cache" header is utilized to ensure backward compatibility with HTTP/1.0 caches and clients,
        // instructing them not to cache the response as well. While generally overridden by the Cache-Control header in HTTP/1.1,
        // it's often used as an extra measure to prevent caching in older clients.
        @header("Pragma: no-cache");

        wp_die(
            '<style>
                /* Remove body border set by WordPress core wp_die */
                body {
                    border: none;
                }
                
                #error-page {
                    margin: 0 !important;
                    width: 100%;
                    max-width: 100%;
                    height: 100vh;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    padding: 0;
                    background-color: #3454D1;
                }

                /* unvisited link */
                a:link {
                    color: #FFFFFF;
                    text-decoration: underline;
                }

                /* visited link */
                a:visited {
                    color: #FFFFFF;
                    text-decoration: underline;
                }

                /* mouse over link */
                a:hover {
                    color: #FFFFFF;
                }

                /* selected link */
                a:active {
                    color: #FFFFFF;
                    text-decoration: underline;
                }

                .press-maintenance {
                    display: flex;
                    flex-wrap: wrap;
                    justify-content: center;
                    align-items: center;
                    padding: 0 1em;
                }

                .press-maintenance h1 {
                    border: none;
                    font-size: 3em;
                    color: #ffffff;
                    text-align: center;
                }

                .press-maintenance p {
                    font-size: 1.4em !important;
                    margin-top: 10px !important;
                    color: #ffffff;
                    text-align: center;
                }

                .spinner {
                    -webkit-animation: rotate 2s linear infinite;
                            animation: rotate 2s linear infinite;
                    z-index: 2;
                    position: relative;
                    width: 50px;
                    height: 50px;
                  }
                  .spinner .path {
                    stroke: #93bfec;
                    stroke-linecap: round;
                    -webkit-animation: dash 1.5s ease-in-out infinite;
                            animation: dash 1.5s ease-in-out infinite;
                  }
                  
                  @-webkit-keyframes rotate {
                    100% {
                      transform: rotate(360deg);
                    }
                  }
                  
                  @keyframes rotate {
                    100% {
                      transform: rotate(360deg);
                    }
                  }
                  @-webkit-keyframes dash {
                    0% {
                      stroke-dasharray: 1, 150;
                      stroke-dashoffset: 0;
                    }
                    50% {
                      stroke-dasharray: 90, 150;
                      stroke-dashoffset: -35;
                    }
                    100% {
                      stroke-dasharray: 90, 150;
                      stroke-dashoffset: -124;
                    }
                  }
                  @keyframes dash {
                    0% {
                      stroke-dasharray: 1, 150;
                      stroke-dashoffset: 0;
                    }
                    50% {
                      stroke-dasharray: 90, 150;
                      stroke-dashoffset: -35;
                    }
                    100% {
                      stroke-dasharray: 90, 150;
                      stroke-dashoffset: -124;
                    }
                  }
            </style>

            <div class="press-maintenance">
                <div>
                    <h1>We will be right back!</h1>
                    <p>This website is currently running a brief maintenance.</p>
                    <div style="margin: 2em auto;text-align:center;">
                        <svg class="spinner" viewBox="0 0 50 50">
                            <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                        </svg>
                    </div>
                </div>
            </div>',
            'Maintenance Mode', // Custom title for wp_die page
            array('response' => 503)
        );
    }
}

// Attach the 'wp_maintenance_mode' function to the 'init' action hook.
// 'init' is a WordPress hook that triggers after WordPress has finished loading
// but before any headers are sent. Utilizing 'init' for maintenance mode
// means that our custom function 'wp_maintenance_mode' will be executed at
// this point in the request, allowing us to intervene early in the process,
// displaying a maintenance message and preventing further loading of WordPress
// assets and execution of queries in an effort to save resources.

// Initialize the plugin
function maintenance_mode_init() {
    // Add settings link after plugin is loaded
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'maintenance_mode_settings_link');
    
    // Add settings page
    add_action('admin_init', 'maintenance_mode_register_settings');
    add_action('admin_menu', 'maintenance_mode_add_settings_page');
    
    // Add maintenance mode check
    add_action('init', 'wp_maintenance_mode', 0); // Priority 0 to run early
}
add_action('plugins_loaded', 'maintenance_mode_init');
