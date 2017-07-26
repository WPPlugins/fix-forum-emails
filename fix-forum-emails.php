<?php
/**
 * @author            Kona Macphee <kona@fidgetylizard.com>
 * @since             1.0
 * @package           Fix_Forum_Emails
 *
 * @wordpress-plugin
 * Plugin Name:       Fix Forum Emails 
 * Plugin URI:        https://wordpress.org/plugins/fix-forum-emails/
 * Description:       Fixes unwanted HTML entities in bbPress notification email subject lines and message bodies. 
 * Version:           1.0
 * Author:            Fidgety Lizard
 * Author URI:        http://www.fidgetylizard.com
 * Contributors:      fliz, kona
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       fix-forum-emails
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}


if ( ! function_exists( 'get_plugin_data' ) ) {
  require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
// This file contains the fixed versions of bbPress email functions
include_once('includes/bbp-fixes.php');


if ( ! class_exists( 'Fix_Forum_Emails' ) )
{
  class Fix_Forum_Emails
  {
		/**
		 * Some handy constants 
		 */
    const BBP_PLUGIN = 'bbpress/bbpress.php';
    const BBP_CLASS = 'bbPress';
    const BBP_MINVER = '2.5.8';

    /**
     * Construct the plugin object
     */
    public function __construct()
    {
      // Need to do our initialisation after all other plugins are loaded
      add_action( 'plugins_loaded', array( $this, 'plugin_init' ) );

      // Prepare for i18n translations
      add_action( 'plugins_loaded', array( $this, 'load_my_textdomain' ) );
    } // END public function __construct


	 /**
    * Initialise plugin and set up actions.
    * This needs to be run AFTER plugins have been loaded so that
    * dependencies can be checked by bbpress_is_running().
    */
    public function plugin_init()
    {
      //Check first that bbPress is active
      if ( TRUE === $this->bbpress_is_running() ) {

        // Yes it is - check version
        $version = $this->bbpress_get_version();

        // BBP version needs to be >= our minimum version
        if ( version_compare( $version, self::BBP_MINVER ) >= 0 ) {
					// Version is ok
          // Replace email sending functions with tweaked versions
          remove_action ('bbp_new_reply', 
              'bbp_notify_topic_subscribers',11);
          add_action ('bbp_new_reply', 
              'flizfix_bbp_notify_topic_subscribers_2_5_8', 11, 5);
          remove_action ('bbp_new_topic', 
               'bbp_notify_forum_subscribers', 11);
          add_action ('bbp_new_topic', 
              'flizfix_bbp_notify_forum_subscribers_2_5_8', 11, 4);
        }
        else {
					// Version is not ok
           add_action( 'admin_notices',
                array( $this, 'bbpress_version_error_notice') );
        }
      }
    } // END public function __construct


   /**
    * Print a notification message that the bbPress version installed 
    * is incompatible with this plugin.
    */
    public function bbpress_version_error_notice()
    {
      // Check if we have a suitably qualified user to see the notice
      if ( current_user_can( 'install_plugins' ) ) {
        $version = $this->bbpress_get_version();
        echo "<div class='error'><p><b>";
        esc_html_e( "Notice from Fix Forum Emails:",
                      'fix-forum-emails' );
        echo "</b></p><p>";
        printf( esc_html__( "The Fix Forum Emails plugin only works with bbPress versions %s and above. Your bbPress version is %s.",
                'fix-forum-emails' ),
          self::BBP_MINVER,
          $version
        );
        echo "</p></div>";
      }
    }
    // END public function bbpress_version_error_notice


   /**
    * Check if bbPress is installed and active.
    * @return bool
    */
    private function bbpress_is_running()
    {
      $active = FALSE;
      $single = FALSE;
      $multi = FALSE;
      if ( class_exists( self::BBP_CLASS ) ) {
        $active = TRUE;
      }
      else {
        // Double check in case bbPress is active but not yet available
        if ( is_multisite() ) {
          $plugins = get_site_option( 'active_sitewide_plugins' );
          if ( isset( $plugins[ self::BBP_PLUGIN ] ) ) {
            $multi = TRUE;
          }
        }
        if ( FALSE === $multi ) {
          $single = in_array(
              self::BBP_PLUGIN, get_option( 'active_plugins' ) );
        }
      }
      return ( $active || $multi || $single ); // True if any is true, otw false
    } // END private function bbpress_is_running()


   /**
    * Check if bbPress is installed and active.
    * @return string
    */
    private function bbpress_get_version()
    {
      $data = get_plugin_data( 
          ABSPATH . 'wp-content/plugins/' . self::BBP_PLUGIN, 
          false, false 
      );
      if ( $data ) {
        return $data['Version']; 
      }
      return "0.0"; // Return too-low dummy value just to be sure
    }
    // END private function bbpress_get_version


    /**
     * Activate the plugin
     */
    public static function activate()
    {
      // Nothing to do here
    } // END public static function activate


    /**
     * Deactivate the plugin
     */
    public static function deactivate()
    {
      // Nothing to do here
    } // END public static function deactivate


    /**
     * Set things up for i18n
     */
    public function load_my_textdomain() 
    {
      load_plugin_textdomain( 
        'fix-forum-emails', 
        FALSE, 
        basename( dirname( __FILE__ ) ) . '/languages/' 
      );
    }

  } // END class Fix_Forum_Emails
} // END if ( ! class_exists( 'Fix_Forum_Emails' ) )


if ( class_exists( 'Fix_Forum_Emails' ) )
{
  // Installation and uninstallation hooks
  register_activation_hook(
    __FILE__, 
    array( 'Fix_Forum_Emails', 'activate' )
  );
  register_deactivation_hook(
    __FILE__, 
    array( 'Fix_Forum_Emails', 'deactivate' )
  );
  // instantiate the plugin class
  $wp_plugin_template = new Fix_Forum_Emails();
}
?>
