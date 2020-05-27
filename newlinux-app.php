<?php

/**
 * @package New Linux Kernel AppLe
 */

/*
 Plugin Name: New Linux Kernel RSS Feed App
 Plugin URI: http://userspace.org
 Description: This app gathers RSS feed data from New linux kernel site and requires the AppLepie project plugin.
 Version: 0.8.0
 Author: Daniel Yount IcarusFactor
 Author URI: http://userspace.org
 License: GPLv2 or later
 Text Domain: newlinux-appLe
 */

/*
 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
defined('ABSPATH')or die('Hey, what are you doing here? You silly human!');
if(!class_exists('newlinuxAppLe')&& class_exists('AppLePiePlugin')) {

    class newlinuxAppLe {
        public $plugin;

        function __construct() {
            $this->plugin = plugin_basename(__FILE__);
        }

        function activate() {
            // Require parent plugin
            if(!is_plugin_active('applepie_plugin/applepie-plugin.php')and current_user_can('activate_plugins')) {
                // Stop activation redirect and show error
                wp_die('Sorry, but this plugin requires the Parent Plugin to be installed and active. <br><a href="' . 
                       admin_url('plugins.php'). 
                                 '">&laquo; Return to Plugins</a>');
            }
            require_once plugin_dir_path(__FILE__). 'inc/newlinux-app-activate.php';
            newlinuxAppActivate::activate();
        }
        
        // Place modification scripts here for Applepie plugin. Hardcoded to first item only currently.
        function start_up() {
            $ApplepiePlugin = new AppLePiePlugin();
            $i = 1;
            $j = 0;
            $Content = "<!-- Filter Starts Here -->";
            $pattern = "/^(.*?)(\bmainline\b)(.*)$/";
            $Content .= $ApplepiePlugin->feed_generate_header();
            $Content .= "<div style=\"position: relative; width: 0; height: 0\"><div id=\"content-linux-kernel\"></div></div>";
            list($permrss, $titlerss, $daterss, $contentrss)= $ApplepiePlugin->feed_generate_process("https://www.kernel.org/feeds/kdist.xml", 10);

            foreach($titlerss as $item)
                : if(preg_match($pattern, $item, $matches)&& $j == 0) {
                $Content .= "<span style=\"font-size:22\"><a href=\"http://www.kernel.org\" >KERNELSPACE</a></span></br></br>";
                //Place how to compile kernel for many distros.
                $Content .= $ApplepiePlugin->feed_generate_headtofoot();
                $Content .= "<span style=\"font-size:22\"><a href=\"https://www.kernel.org/doc/html/latest/\" >[DOCUMENTATION]</a></span>";
                $Content .= "<span style=\"font-size:22\"><a href=\"http://www.lkml.org\" >[MAILLING LIST]</a></span></br>";
                $Content .= " BUILD DISTRIBUTION KERNELS FOR:</BR>";
                $Content .= " <a href=\"https://kernel-team.pages.debian.net/kernel-handbook/ch-common-tasks.html#s-common-official\">[DEBIAN]</a>";
                $Content .= " <a href=\"https://fedoraproject.org/wiki/Building_a_custom_kernel\">[FEDORA]</a>";
                $Content .= " <a href=\"https://wiki.ubuntu.com/Kernel/BuildYourOwnKernel\">[UBUNTU]</a>";
                $Content .= " <a href=\"https://wiki.centos.org/HowTos/Custom_Kernel\">[CENTOS]</a>";
                $Content .= " <a href=\"https://www.suse.com/c/compiling-de-linux-kernel-suse-way/\">[OPENSUSE]</a>";
                $Content .= " <a href=\"https://wiki.archlinux.org/index.php/Kernel/Arch_Build_System\">[ARCH]</a>";
                $Content .= " <a href=\"https://www.raspberrypi.org/documentation/linux/kernel/building.md\">[RASPIAN]</a>";
                $Content .= $contentrss[$i];
                $j ++;
            }
            //end of filter 
            $i ++;
            endforeach;
            $Content .= "<!-- Stop looping through each item once we've gone through all of them. -->";
            $Content .= "<!-- From here on, we're no longer using data from the feed. -->";
            $Content .= $ApplepiePlugin->feed_generate_footer();
            return $Content;
        }
    }
    $newlinuxApp = new newlinuxAppLe();
    // activation
    register_activation_hook(__FILE__, array($newlinuxApp, 'activate'));
    // deactivation
    require_once plugin_dir_path(__FILE__). 'inc/newlinux-app-deactivate.php';
    register_deactivation_hook(__FILE__, array('newlinuxAppDeactivate', 'deactivate'));
    //Use hooks from parent plugin.  
    add_shortcode('NewlinuxApp', array($newlinuxApp, 'start_up'));
}
