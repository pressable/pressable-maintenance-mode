<?php
/*
Plugin Name: Pressable Maintenance Mode
Plugin URI: https://pressable.com
Description: A simple maintenance mode plugin
Author: Pressable
Version: 1.0.3
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
        </div>
      ');
    }
}

add_action('get_header', 'wp_maintenance_mode');
?>
