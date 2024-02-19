<?php
/*
Plugin Name: Maintenance Mode
Plugin URI: https://github.com/pressable/pressable-maintenance-mode
Description: Effortlessly enable maintenance mode on your website! When activated, non-logged-in visitors or users without editing privileges are greeted with a stylish, user-friendly notice indicating that the website is undergoing maintenance.
Author: Pressable
Version: 1.0.6
Author URI: https://pressable.com/
License: GPL2
*/


// This condition checks whether PHP is being run from the command line interface (cli).
// php_sapi_name(): This is a PHP function that returns the type of interface (Server API, SAPI) between the web server and PHP.
if ( 'cli' == php_sapi_name() ) {
    // If PHP is running from the command line, this line will immediately exit the script,
    // preventing the rest of the code from executing.
    return;
}

// Activate WordPress Maintenance Mode
function wp_maintenance_mode() {
    // Exit if requesting an administration or login page
    if ( is_admin() || strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false ) {
      return;
    }
    
    // If the current user does not have the capability to edit themes or is not logged in.
    if ( !current_user_can( 'edit_themes' ) || ! is_user_logged_in() ) {
        // Send a raw HTTP header to control the cache settings for the response being sent back to the client/browser
        header( "Cache-Control: no-store, no-cache, must-revalidate, max-age=0" );

        // The following header is specifying cache controls as well:
        // - post-check: The freshness of the content directly after a POST request, set to 0 to prevent caching post-request.
        // - pre-check: Similar to post-check but for pre-request content freshness, also set to 0 to avoid caching.
        // The second parameter, false, indicates that this header should not replace a header previously set, which can allow multiple headers of the same type to be sent.
        header( "Cache-Control: post-check=0, pre-check=0", false );

        // The "Pragma: no-cache" header is utilized to ensure backward compatibility with HTTP/1.0 caches and clients,
        // instructing them not to cache the response as well. While generally overridden by the Cache-Control header in HTTP/1.1,
        // it's often used as an extra measure to prevent caching in older clients.
        header( "Pragma: no-cache" );

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
        array('response' => 503) // Set HTTP response to 503, informing the client that service is temporarily unavailable. Commonly used in maintenance modes to notify users and search engines that the downtime is temporary.
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
add_action( 'init', 'wp_maintenance_mode' );
?>
