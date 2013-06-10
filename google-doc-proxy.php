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
    add_action('init', array($this, 'widget_textdomain'));

    parent::__construct(
      'google-doc-proxy',
      __('Google Doc Proxy'),
      array(
        'classname'   =>  'google-doc-proxy',
        'description' =>  __('This plugin provides to manage to display Google Docs via Gogle Doc Proxy.')
      )
    );

    // Register admin menu
    if (is_admin()) {
      add_action('admin_menu', array($this, 'admin_menu'));
      add_action('admin_init', array($this, 'register_settings'));
    }
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
  public function widget($args, $instance) {

    extract($args, EXTR_SKIP);

    echo $before_widget;

    // Display Google Doc
    require_once 'gdoc-prox/gdoc_prox.php';
    $gdoc_prox = new gdoc_prox;

    $gdoc_prox->baseUrl = 'http://google-doc-proxy.hironozu.com/gdocprox';

    $options = array(
      'query' => array(
        'c' => get_option('client_id'),
        's' => get_option('client_secret'),
        't' => get_option('token'),
      ),
    );


    // var_dump($gdoc_prox->getCachedDocuments($options));
    // var_dump($gdoc_prox->deleteDocument($instance['document_id'], $options));
    // var_dump($gdoc_prox->deleteData($options));

    // var_dump($gdoc_prox->getList($options));

    // echo $gdoc_prox->show($instance['document_id'], $options);

    $result = $gdoc_prox->get($instance['document_id'], $options);

    if ($result->error) {
      echo $result->message;
    } else {
      $content = $result->content;
      echo $content->title;
      echo $content->body;
    }

    echo $after_widget;

  } // end widget

  /**
   * Processes the widget's options to be saved.
   *
   * @param array new_instance  The previous instance of values before the update.
   * @param array old_instance  The new instance of values to be generated via the update.
   */
  public function update($new_instance, $old_instance) {

    $instance = $old_instance;

    $instance['document_id'] = strip_tags($new_instance['document_id']);

    return $instance;

  } // end widget

  /**
   * Generates the administration form for the widget.
   *
   * @param array instance  The array of keys and values for the widget.
   */
  public function form($instance) {

    $defaults = array(
      'document_id' => '',
    );
    $instance = wp_parse_args(
      (array) $instance,
      $defaults
    );

    // Display the admin form, or not
    if (get_option('client_id') and get_option('client_id') and get_option('client_id')) {
?>
<div>
  <p><a target="_blank" href="#">Select Document</a></p>
  <label for="<?php echo $this->get_field_id('document_id'); ?>">Document ID:</label>
  <input id="<?php echo $this->get_field_id('document_id'); ?>" name="<?php echo $this->get_field_name('document_id'); ?>"  value="<?php echo $instance['document_id']; ?>" />
</div>
<?php
    } else {
?>
<p>You need to setup Google Doc Proxy before placing widget.</p>
<p><a href="options-general.php?page=<?php echo plugin_dir_path(__FILE__); ?>google-doc-proxy.php">Setup Google Doc Proxy</a></p>
<?php
    }
  } // end form

  /*--------------------------------------------------*/
  /* Public Functions
  /*--------------------------------------------------*/

  /**
   * Loads the Widget's text domain for localization and translation.
   */
  public function widget_textdomain() {

    // TODO be sure to change 'widget-name' to the name of *your* plugin
    load_plugin_textdomain('google-doc-proxy', false, plugin_dir_path(__FILE__) . '/lang/');

  } // end widget_textdomain

  /**
   * Display admin menu.
   */
  public function admin_menu() {
    add_options_page('Google Doc Proxy Plugin Options', 'Google Doc Proxy', 8, __FILE__, array($this, 'settings_page'));
  } // end my_admin_menu

  /**
   * Register settings.
   */
  public function register_settings() {
    register_setting('google_doc_proxy', 'client_id');
    register_setting('google_doc_proxy', 'client_secret');
    register_setting('google_doc_proxy', 'token');
  } // end register_settings

  /**
   * Settings page.
   */
  public function settings_page() {

    // Display the admin form
    $header = __('Google Doc Proxy Settings');
?>
<div class="wrap">

  <div id="icon-options-general" class="icon32"><br></div><h2><?php echo $header; ?></h2>

  <p>
    This widget requires a setup at Google Doc Proxy.<br />
    You can get your setting <a target="_blank" href="http://google-doc-proxy.hironozu.com/code">here</a> (Just go through the instruction, submit the form and you will see these three values).
  </p>

  <form method="post" action="options.php">

    <?php settings_fields('google_doc_proxy'); ?>

    <table class="form-table">
    <tbody>
      <tr valign="top">
        <th scope="row"><label for="client_id">Client ID</label></th>
        <td><input class="regular-text" id="client_id" name="client_id" type="text" value="<?php echo get_option('client_id'); ?>"></td>
      </tr>
      <tr valign="top">
        <th scope="row"><label for="client_secret">Client secret</label></th>
        <td><input class="regular-text" id="client_secret" name="client_secret" type="text" value="<?php echo get_option('client_secret'); ?>"></td>
      </tr>
      <tr valign="top">
        <th scope="row"><label for="token">Client secret</label></th>
        <td><input class="regular-text" id="token" name="token" type="text" value="<?php echo get_option('token'); ?>"></td>
      </tr>
    </tbody>
    </table>

    <?php submit_button(); ?>

  </form>

</div>
<?php
  } // end settings_page
}

add_action('widgets_init', create_function('', 'register_widget("GoogleDocProxyWidget");'));
