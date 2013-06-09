<?php
/*
Plugin Name: Google Doc Proxy
Plugin URI: http://google-doc-proxy.hironozu.com/
Description: This plugin provides to manage to display Google Docs via Gogle Doc Proxy.
Version: 0.0
Author: Hiro Nozu
Author URI: http://hironozu.com/
License: A "Slug" license name e.g. GPL2
*/
/*
Copyright 2013 Hiro Nozu (email: contact@hironozu.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class GoogleDocProxyWidget extends WP_Widget {

  /*--------------------------------------------------*/
  /* Constructor
  /*--------------------------------------------------*/

  public function __construct() {

    // load plugin text domain
    add_action( 'init', array( $this, 'widget_textdomain' ) );

    parent::__construct(
      'google-doc-proxy',
      __( 'Google Doc Proxy', 'google-doc-proxy-locale' ),
      array(
        'classname'   =>  'google-doc-proxy',
        'description' =>  __( 'This plugin provides to manage to display Google Docs via Gogle Doc Proxy.', 'google-doc-proxy-locale' )
      )
    );

    // Register admin styles and scripts
    // add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
    // add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );

    // Register site styles and scripts
    // add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );
    // add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_scripts' ) );

  } // end constructor

  /*--------------------------------------------------*/
  /* Widget API Functions
  /*--------------------------------------------------*/

  /**
   * Outputs the content of the widget.
   *
   * @param array args    The array of form elements
   * @param array instance  The current instance of the widget
   */
  public function widget( $args, $instance ) {

    extract( $args, EXTR_SKIP );

    echo $before_widget;

    // Display Google Doc
// $instance['client_id']
// $instance['client_secret']
// $instance['token']

echo 'XXXXXX';

    include( plugin_dir_path( __FILE__ ) . '/views/widget.php' );

    echo $after_widget;

  } // end widget

  /**
   * Processes the widget's options to be saved.
   *
   * @param array new_instance  The previous instance of values before the update.
   * @param array old_instance  The new instance of values to be generated via the update.
   */
  public function update( $new_instance, $old_instance ) {

    $instance = $old_instance;

    $instance['client_id'] = strip_tags( $new_instance['client_id'] );
    $instance['client_secret'] = strip_tags( $new_instance['client_secret'] );
    $instance['token'] = strip_tags( $new_instance['token'] );

    return $instance;

  } // end widget

  /**
   * Generates the administration form for the widget.
   *
   * @param array instance  The array of keys and values for the widget.
   */
  public function form( $instance ) {

    $defaults = array(
      'client_id' => '',
      'client_secret' => '',
      'token' => '',
    );
    $instance = wp_parse_args(
      (array) $instance,
      $defaults
    );

    // Display the admin form
?>
<p>
  This widget requires a setup at Google Doc Proxy.<br />
  You can get your setting at <a target="_blank" href="http://google-doc-proxy.hironozu.com/code">here</a>.
</p>
<div>
  <label for="<?php echo $this->get_field_id( 'client_id' ); ?>">Client ID:</label>
  <input id="<?php echo $this->get_field_id( 'client_id' ); ?>" name="<?php echo $this->get_field_name( 'client_id' ); ?>"  value="<?php echo $instance['client_id']; ?>" />
</div>
<div>
  <label for="<?php echo $this->get_field_id( 'client_secret' ); ?>">Client secret:</label>
  <input id="<?php echo $this->get_field_id( 'client_secret' ); ?>" name="<?php echo $this->get_field_name( 'client_secret' ); ?>" value="<?php echo $instance['client_secret']; ?>" />
</div>
<div>
  <label style="display: block" for="<?php echo $this->get_field_id( 'token' ); ?>">Token:</label>
  <input id="<?php echo $this->get_field_id( 'token' ); ?>" name="<?php echo $this->get_field_name( 'token' ); ?>" value="<?php echo $instance['token']; ?>" />
</div>
<?php
  } // end form

  /*--------------------------------------------------*/
  /* Public Functions
  /*--------------------------------------------------*/

  /**
   * Loads the Widget's text domain for localization and translation.
   */
  public function widget_textdomain() {

    // TODO be sure to change 'widget-name' to the name of *your* plugin
    load_plugin_textdomain( 'google-doc-proxy', false, plugin_dir_path( __FILE__ ) . '/lang/' );

  } // end widget_textdomain
}

add_action('widgets_init', create_function('', 'register_widget("GoogleDocProxyWidget");'));
