<?php
/*
Plugin Name: Google Doc Proxy
Plugin URI: http://google-doc-proxy.hironozu.com/
Description: This plugin provides to manage to display Google Docs via Gogle Doc Proxy.
Version: 0.2
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

  private function add_script($name, $file_path) {
      $url = WP_PLUGIN_URL . $file_path;
      $file = WP_PLUGIN_DIR . $file_path;
      wp_register_script($name, $url);
      wp_enqueue_script($name);
  }
  public function __construct() {

    // load plugin text domain
    add_action('init', array($this, 'widget_textdomain'));

    $name = 'google-doc-proxy';

    parent::__construct(
      $name,
      __('Google Doc Proxy'),
      array(
        'classname'   =>  'google-doc-proxy',
        'description' =>  __('This plugin provides to manage to display Google Docs via Gogle Doc Proxy.')
      )
    );

    $dir_name = array_reverse(explode('/', dirname(__FILE__)));

    // Register admin menu
    if (is_admin()) {

      add_action('admin_menu', array($this, 'admin_menu'));
      add_action('admin_init', array($this, 'register_settings'));

      // AJAX callback (AJAX callback has to be defined here as is_admin is always true when request is made via AJAX)
      add_action('wp_ajax_search_document', array($this, 'search_document_callback'));
      add_action('wp_ajax_download', array($this, 'download_callback'));

      $file_path = '/' . $dir_name[0] . '/js/admin.js';

      $this->add_script($name, $file_path);

    } else {
      // Add non-Ajax front-end action hooks here
      /*
      if ( ! defined(GDOC_PROXY_JS_LOADED)) {

        define('GDOC_PROXY_JS_LOADED', 1);

        wp_enqueue_script('jquery');

        add_action('wp_ajax_download', array($this, 'download_callback'));
        $file_path = '/' . $dir_name[0] . '/js/site.js';

        $this->add_script($name, $file_path);

        // Enable to refer ajaxurl
        wp_localize_script($name, 'gdoc_prox', array('ajaxurl' => admin_url('admin-ajax.php')));
      }
      */
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

    if ($instance['document_id']) {

      // Display Google Doc
      $gdoc_prox = $this->get_gdoc_prox();
      $options = $this->get_gdoc_options();

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
    }
    if ($instance['show_pdf_link']) {
      $widgetid = str_replace('google-doc-proxy-', '', $args['widget_id']);
      $url = admin_url('admin-ajax.php') . '?action=download&type=pdf&id=' . $widgetid;
      // echo '<a class="gdoc-prox-pdf" href="#" data-id="' . $widgetid . '">' . __('Download PDF') . '</a>';
      echo '<a href="' . $url . '">' . __('Download PDF') . '</a>';
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

    $instance['document_id'] = $new_instance['document_id'];
    $instance['show_content'] = $new_instance['show_content'];
    $instance['show_pdf_link'] = $new_instance['show_pdf_link'];
    $instance['data'] = $new_instance['data'];

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
      'show_content' => true,
      'show_pdf_link' => false,
      'data' => '',
    );
    $instance = wp_parse_args(
      (array) $instance,
      $defaults
    );

    // Display the admin form, or not
    if (get_option('client_id') and get_option('client_secret') and get_option('token')) {

      $element_id = $this->get_field_id('container');
?>
<div>
  <p id="<?php echo $element_id; ?>" class="gdox-prox-container">
    <label for="<?php echo $this->get_field_id('search'); ?>">Search Document:</label>
    <input class="gdox-prox-search" id="<?php echo $this->get_field_id('search'); ?>" name="<?php echo $this->get_field_name('search'); ?>" value="" />
    <input class="button" type="button" onclick="gdocProxAdminMgr.onSearch(this);" class="gdoc-prox-search-submit" name="<?php echo $this->get_field_name('search_submit'); ?>" value="Search" />
  </p>

  <p>
    <label for="<?php echo $this->get_field_id('document_id'); ?>">Document ID:</label>
    <input class="document-id" id="<?php echo $this->get_field_id('document_id'); ?>" name="<?php echo $this->get_field_name('document_id'); ?>" value="<?php echo $instance['document_id']; ?>" />
  </p>

  <h4>What to display</h4>
  <p>
    <label for="<?php echo $this->get_field_id('show_content'); ?>">Content:</label>
    <input type="checkbox" class="show_content" id="<?php echo $this->get_field_id('show_content'); ?>" name="<?php echo $this->get_field_name('show_content'); ?>"<?php echo $instance['show_content'] ? ' checked' : ''; ?> />
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('show_pdf_link'); ?>">PDF Link:</label>
    <input type="checkbox" class="show_pdf_link" id="<?php echo $this->get_field_id('show_pdf_link'); ?>" name="<?php echo $this->get_field_name('show_pdf_link'); ?>"<?php echo $instance['show_pdf_link'] ? ' checked' : ''; ?> />
  </p>

  <input type="hidden" class="godc_data" id="<?php echo $this->get_field_id('data'); ?>" name="<?php echo $this->get_field_name('data'); ?>" value="<?php echo $instance['data']; ?>" />
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
   * Gets Google Doc Proxy Object
   */
  public function get_gdoc_options() {
    return array(
      'query' => array(
        'c' => get_option('client_id'),
        's' => get_option('client_secret'),
        't' => get_option('token'),
      ),
    );
  }
  public function get_gdoc_prox() {

    require_once 'gdoc-prox/gdoc_prox.php';
    $gdoc_prox = new gdoc_prox;

    $gdoc_prox->baseUrl = get_option('base_url', 'http://google-doc-proxy.hironozu.com/gdocprox');

    return $gdoc_prox;
  }

  /**
   * Loads the Widget's text domain for localization and translation.
   */
  public function widget_textdomain() {

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
    register_setting('google_doc_proxy', 'base_url');
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
        <th scope="row"><label for="token">Token</label></th>
        <td><input class="regular-text" id="token" name="token" type="text" value="<?php echo get_option('token'); ?>"></td>
      </tr>
      <tr valign="top">
        <th scope="row">&nbsp;</th>
        <td><?php

        if (get_option('client_id') and get_option('client_secret') and get_option('token')) {
          $params = array(
            'clientId' => get_option('client_id'),
            'secret' => get_option('client_secret'),
            'refreshToken' => get_option('token'),
          );
          $link = str_replace('gdocprox', 'cache-man/login?' . http_build_query($params), get_option('base_url'));
          echo '<a target="_gocprox" href="' . $link . '">' . __('Cache Manager') . '</a>';
        }

      ?></td>
      </tr>
    </tbody>
    </table>

    <h3>Advanced Settings</h3>

    <table class="form-table">
    <tbody>
      <tr valign="top">
        <th scope="row"><label for="base_url" title="Leave this field unless you are asked to use">Base URL</label></th>
        <td><input class="regular-text" id="base_url" name="base_url" type="text" value="<?php echo get_option('base_url'); ?>"></td>
      </tr>
    </tbody>
    </table>

    <?php submit_button(); ?>

  </form>

</div>
<?php
  } // end settings_page

  /**
   * Callback for Search button at Admin side.
   */
  function search_document_callback() {

    // global $wpdb; // this is how to get access to the database

    $data = array(
      'error' => 0,
      'message' => '',
      'list' => array(),
    );

    $search_text = empty($_REQUEST['search_text']) ? '' : $_REQUEST['search_text'];

    $gdoc_prox = $this->get_gdoc_prox();
    $options = $this->get_gdoc_options();

    $result = $gdoc_prox->getList($options);

    if ($result->error) {
      $data['error'] = 1;
      $data['message'] = $result->message;
    } else {
      foreach ($result->list as $index => $document) {
        if (preg_match("#{$search_text}#i", $document->title)) {
          $data['list'][] = array($document->id => $document->title);
          $data['data'][] = json_encode($document);
        }
      }
    }

    echo json_encode($data);

    die();
  } // end search_document_callback

  /**
   * Callback for download link/button at Site side.
   */
  function download_callback() {

    if (empty($_REQUEST['id'])) die();
    if (empty($_REQUEST['type'])) die();

    $id = (int) $_REQUEST['id'];
    $dummy = new GoogleDocProxyWidget;
    $settings = $dummy->get_settings();
    if (empty($settings[$id])) die();

    $s = $settings[$id];

    $type = $_REQUEST['type'];
    switch ($type) {
      case 'pdf':
        if (empty($s['show_pdf_link'])) die();
        break;
      case 'word':
        if (empty($s['show_word_link'])) die();
        break;
      case 'rtf':
        if (empty($s['show_rtf_link'])) die();
        break;
      default:
        die();
    }

    $document_id = $s['document_id'];

    $gdoc_prox = $this->get_gdoc_prox();
    $options = $this->get_gdoc_options();

    $options['query']['type'] = $type;

    $content = $gdoc_prox->download($document_id, $options);
    $data = json_decode($s['data']);

    // Send header
    switch ($type) {
      case 'pdf':
        header('Content-type: application/pdf');
        $file = $data->title . '.pdf';
        break;
      case 'word':
        header('Content-type: application/vnd.ms-word');
        $file = $data->title . '.docx';
        break;
      case 'rtf':
        header('Content-type: application/rtf');
        $file = $data->title . '.rtf';
        break;
      default:
        die();
    }

    header('Content-Length: ' . filesize($content));
    header('Content-Disposition: attachment;Filename=' . $file);
    echo $content;

    die();
  }
}

add_action('widgets_init', create_function('', 'register_widget("GoogleDocProxyWidget");'));
