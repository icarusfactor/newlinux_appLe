<?php
/**
 * @package New Linux Kernel AppLe 
 */
/*
Plugin Name: New Linux Kernel RSS Feed App 
Plugin URI: http://userspace.org
Description: This app gathers RSS feed data from New linux kernel site and requires the AppLepie project plugin.  
Version: 1.0.0
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

defined( 'ABSPATH' ) or die( 'Hey, what are you doing here? You silly human!' );

if ( !class_exists( 'newlinuxAppLe' ) && class_exists( 'AppLePiePlugin' )  ) {

	class newlinuxAppLe
	{

		public $plugin;

		function __construct() {
			$this->plugin = plugin_basename( __FILE__ );
		}

		function activate() {


                        // Require parent plugin
                        if ( ! is_plugin_active( 'applepie_plugin/applepie-plugin.php' ) and current_user_can( 'activate_plugins' ) )
                        {
                        // Stop activation redirect and show error
                        wp_die('Sorry, but this plugin requires the Parent Plugin to be installed and active. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
                        }

			require_once plugin_dir_path( __FILE__ ) . 'inc/newlinux-app-activate.php';
			newlinuxAppActivate::activate();
		}

                // Place modification scripts here for Applepie plugin. Hardcoded to first item only currently.

               function mainlineFilter($string) {

                               $pos = strpos($string, "mainline");
                                if ($pos === false) {
                                   return; 
                                } else {
                                   return $string;
                               }

                                }

               function stableFilter($string) {
                                $pos = strpos($string, "stable");
                                if ($pos === false) { return;}
                                else { return $string; }
                                }

               function versionFilter($string) {
                               return preg_replace("/[^0-9.]/", "", $string);
                               }


               function maxVersion( $array ) {
                               $max = null;
                               $item = 0;
                               $mi=0; 
                               //remove any text only keep digits and dots.
                               $data = array_filter($array, array( $this , "versionFilter" ) );              

                               foreach ($data as $version) {
                               if (version_compare($max, $version) === -1) {
                               $max = $version;
                               $mi = $item;  
                               }
                               $item++;
                               }
                               return  $max;
                               } 


		function  start_up() {
			$ApplepiePlugin = new AppLePiePlugin();
                        $i=1;$j=0;
			$Content .= $ApplepiePlugin->feed_generate_header();
                        $Content .= "<div style=\"position: relative; width: 0; height: 0\"><div id=\"content-linux-kernel\"></div></div>";       


			 list( $permrss, $titlerss , $daterss , $contentrss ) = $ApplepiePlugin->feed_generate_process("https://www.kernel.org/feeds/kdist.xml", 12 );             

               //Find relevant pattern for newst stable version.  
              $data = array_filter($titlerss, array( $this , "stableFilter" ) ); 
              $pattern_stable = $this->maxVersion( $data );
              //Find relevant pattern for mainline version. Should be only one version no need to find max.
              $data = array_filter($titlerss, array( $this , "mainlineFilter" ) ); 
              $pattern_mainline = array_pop( $data ); //Convert type from array to string 
 
			 $Content .= "<span style=\"font-size:22\"><a target = \'_blank\' href=\"http://www.kernel.org\" >KERNELSPACE</a></span></br></br>"; 
			  //Place how to compile kernel for many distros.
			  $Content .= $ApplepiePlugin->feed_generate_headtofoot();
	          $Content .= "<span style=\"font-size:22\"><a  target = \'_blank\' href=\"https://www.kernel.org/doc/html/latest/\" >[DOCUMENTATION]</a></span>"; 
			  $Content .= "<span style=\"font-size:22\"><a  target = \'_blank\' href=\"http://www.lkml.org\" >[MAILLING LIST]</a></span></br>"; 
			  $Content .= " BUILD DISTRIBUTION KERNELS FOR:</BR>"; 
			  $Content .= " <a   target = \'_blank\' href=\"https://kernel-team.pages.debian.net/kernel-handbook/ch-common-tasks.html#s-common-official\">[DEBIAN]</a>"; 
			  $Content .= " <a  target = \'_blank\'  href=\"https://fedoraproject.org/wiki/Building_a_custom_kernel\">[FEDORA]</a>"; 
			  $Content .= " <a  target = \'_blank\'  href=\"https://wiki.ubuntu.com/Kernel/BuildYourOwnKernel\">[UBUNTU]</a>"; 
			  $Content .= " <a  target = \'_blank\'  href=\"https://wiki.centos.org/HowTos/Custom_Kernel\">[CENTOS]</a>"; 
			  $Content .= " <a  target = \'_blank\'  href=\"https://www.suse.com/c/compiling-de-linux-kernel-suse-way/\">[OPENSUSE]</a>"; 
			  $Content .= " <a  target = \'_blank\'  href=\"https://wiki.archlinux.org/index.php/Kernel/Arch_Build_System\">[ARCH]</a>"; 
			  $Content .= " <a target = '_blank\'  href=\"https://www.raspberrypi.org/documentation/linux/kernel/building.md\">[RASPIAN]</a>"; 
              
              $i=1;$j=1; 
  			while(  $i  <= count($titlerss)  ) {                         
                         if (   strcmp( $pattern_stable , $titlerss[ $i ]  ) == 0  ) {     
                        //now convert links to open new page.                       
                         $contentrss[ $i ] = str_replace("<a href=" ,  "<a target = '_blank'  href="  ,   $contentrss[ $i ] );                      
 
              $Content .= "<!-- Now the Stable Kernel -->";
              $Content .= "</BR></BR>Latest Stable Kernel:</BR>";
			            $Content .= $contentrss[ $i ];                             
			 } //end of filter 
                        $i++;
                }
            while(  $j  <= count($titlerss)  ) {            
     	    if (   strcmp( $pattern_mainline , $titlerss[ $j ]  ) == 0  ) {           
                  //now convert links to open new page.      
                  $contentrss[ $j ] = str_replace("<a href=" ,  "<a target = '_blank'  href="  ,   $contentrss[ $j ] );
                        $Content .= "<!-- Now the Mainline Kernel -->";
                        $Content .= "</BR>Current Mainline Kernel:</BR>";   
			 $Content .= $contentrss[$j];     
			 } //end of filter 
                        $j++;       
             }
              
			 $Content .= "<!-- Stop looping through each item once we've gone through all of them. -->";
			 $Content .= "<!-- From here on, we're no longer using data from the feed. -->";


			$Content .= $ApplepiePlugin->feed_generate_footer();

			return $Content;
		}  

	
	
	}

	$newlinuxApp = new newlinuxAppLe();

	// activation
	register_activation_hook( __FILE__, array( $newlinuxApp, 'activate' ) );

	// deactivation
	require_once plugin_dir_path( __FILE__ ) . 'inc/newlinux-app-deactivate.php';
	register_deactivation_hook( __FILE__, array( 'newlinuxAppDeactivate', 'deactivate' ) );
  
	//Use hooks from parent plugin.  
        add_shortcode('NewlinuxApp', array( $newlinuxApp ,'start_up') );

}
