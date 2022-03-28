<?php
/*
Plugin Name: Pressable Maintenance Mode
Plugin URI: https://pressable.com
Description: A simple maintenance mode plugin
Author: Pressable
Version: 1.0.1
Author URI: https://pressable.com/
License: GPL2
*/


if ( 'cli' == php_sapi_name() ) {
    return;
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

header("Cache-Control: post-check=0, pre-check=0", false);

header("Pragma: no-cache");

http_response_code(503);

// Activate WordPress Maintenance Mode
function wp_maintenance_mode() {
    if ( !current_user_can('edit_themes') || !is_user_logged_in() ) {
        wp_die('
        <style>
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
            }

            .press-maintenance p {
                font-size: 1.4em !important;
                margin-top: 10px !important;
                color: #ffffff;
            }

            .press-maintenance img {
                max-width: 600px;
                width: 100%;
                height: auto;
            }

            .press-maintenance > div {
                margin-right: 2em;
            }

            .press-top-line {
                background-color: rgba(0,0,0,.4);
                height: 60px;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                z-index: 50;
                display: flex;
                justify-content: center;
            }

            .press-top-line img {
                width: 100%;
                max-width: 210px;
                height: 35px;
                margin-top: 12px;
            }

            @keyframes gradient {
              0% {
                  background-position: 0% 50%;
              }

              50% {
                  background-position: 100% 50%;
              }

              100% {
                  background-position: 0% 50%;
              }
            }
        </style>

        <div class="press-top-line">
            <img src="https://149443700.v2.pressablecdn.com/wp-content/uploads/2021/11/white_logo_Big-10-300x47.png" alt="Pressable - Managed WordPress Hosting" />
        </div>
        <div class="press-maintenance">
            <div>
                <h1>We will be right back!</h1>
                <p>This website is currently running a brief maintenance.</p>
            </div>
            <img src="https://149443700.v2.pressablecdn.com/wp-content/uploads/2022/03/server-stack@2x-300x171.png" alt="Hosting Servers" />
        </div>
      ');
    }
}

add_action('get_header', 'wp_maintenance_mode');
?>
