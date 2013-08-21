<?php
/*
Plugin Name: Global Giving Projects
Plugin URI: http://github.com/dtheweather9/[TBD]
Description: A plugin for wordpress which connects designated pages with global giving projects and microprojects and reports as to integrate websites.<br>Example usage: [gg-project projectid="{Project-1 #},{Project-2 #}" giveforyouth="false" reports="true" projectdata="true" images="true" bpgg="true"] {Widget HTML} [/gg-project]<br>'Projects' are required, and use the Global Giving Project Number.  The first project (comma seperated) will be displayed, and reports and (eventually) images will be displayed from all of the projects. Currently report images from 2nd and more reports are not being brought in. 'giveforyouth' (default is false) is for use with projects and miniprojects which are listed on giveforyouth.org.  It is not required and will default to false (i.e. the project is on global giving).  'Reports' (default is true) will display reports for all of the projects requested by the shortcode.  'projectdata' (default is true) when set to false will hide the general fields, allowing you to have shortcodes for only reports or only images.  'images' (default is true) will display images from projects and reports.  'bpgg' (default is true) will display the global giving donate sidebox.  Version: 0.02
Author: Dan Pastuf
Author URI: http://www.danpastuf.com
License: GPL2
*/

//Add menu section for the general options
require_once(ABSPATH . '/wp-admin/includes/plugin.php');
require_once(ABSPATH . 'wp-content/plugins/global-giving-display/adminpage.php');
require_once(ABSPATH . WPINC . '/pluggable.php');
add_options_page(__('Global Giving Display Settings','menu-ggdisplay'), __('Global Giving Display General','menu-ggdisplayi'), 'manage_options', 'ggdisplaysettings', 'ggdisplay_settings_page');

//Load shortcodes
require_once(ABSPATH . 'wp-content/plugins/global-giving-display/ggshortcode.php');

//TODO: Add Generic css on load