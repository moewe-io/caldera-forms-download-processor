<?php
/*
  Plugin Name: Caldera Forms: Download Processor
  Plugin URI: https://www.moewe.io
  Description: Processor to provide a download on form submission
  Author: MOEWE
  Author URI: https://www.moewe.io
  Version: 1.1.0
  Text Domain: caldera-forms-download-processor
*/


class MOEWE_Caldera_Forms_Download_Processor {

    public function __construct() {
        add_filter('caldera_forms_get_form_processors', [$this, 'register_download_processor']);

        add_action('wp_ajax_nopriv_caldera_download_file', [$this, 'provide_download']);
        add_action('wp_ajax_caldera_download_file', [$this, 'provide_download']);

        add_shortcode('caldera_download_url', [$this, 'render_shortcode_download_url']);
    }

    function register_download_processor($processors) {
        $processors['moewe_io_add_download'] = array(
            'name'           => __('Add download', 'caldera-forms-download-processor'),
            'description'    => __('Allow users to download a file after form submission', 'caldera-forms-download-processor'),
            'post_processor' => [$this, 'download_post_processor'],
            'template'       => __DIR__ . '/caldera-forms-download-processor.config.php',
            "single"         => false
        );
        return $processors;
    }

    /**
     * Process submission
     *
     * @param array $config Processor config
     * @param array $form Form config
     * @param string $process_id Unique process ID for this submission
     *
     * @return void|array
     */
    function download_post_processor($config, $form, $process_id) {

        $entry_id = Caldera_Forms::get_field_data('_entry_id', $form);
        $token = uniqid('download_file_') . $entry_id . '_';

        $value = array(
            'entry_id' => $entry_id,
            'file'     => $config['download_file']
            // TODO Max downloads?
        );

        if (set_transient($token, $value, $config['download_file_timeout'] * 60)) {
            $value = add_query_arg(array(
                'action' => 'caldera_download_file',
                'token'  => $token,
            ), admin_url('admin-ajax.php'));
        } else {
            $value = __("Error. Please contact us", 'caldera-forms-download-processor');
        }
        Caldera_Forms::set_field_data($config['download_file_field'], $value, $form, $entry_id);
        Caldera_Forms::set_field_data($config['download_file_token_field'], $token, $form, $entry_id);
    }

    function provide_download() {
        $token = isset($_GET['token']) ? trim($_GET['token']) : '';
        if (empty($token)) {
            wp_die(__('Missing token', 'caldera-forms-download-processor'), 400);
        }

        $data = get_transient($token);

        if (!$data && !is_array($data)) {
            wp_die(__("Invalid Token", 'caldera-forms-download-processor'), 404);
        }

        $upload_dir = wp_upload_dir();
        $file = $upload_dir['basedir'] . $data['file'];

        if (!file_exists($file)) {
            wp_die(__("File not found", 'caldera-forms-download-processor'), 404);
        };

        delete_transient($token);
        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($file_info, $file);

        header("Content-Disposition: attachment; filename=" . basename($file));
        header("Content-Type: $mime_type");
        header('Content-Length: ' . filesize($file));

        $fp = fopen($file, 'rb');
        fpassthru($fp);
        wp_die();
    }

    function render_shortcode_download_url($atts, $content = "Download now") {
        $atts = shortcode_atts(array(
            'class'  => '',
            'target' => '_self',
        ), $atts);

        $token = isset($_GET['download_token']) ? $_GET['download_token'] : false;

        if (!$token) {
            return __('Missing required download url', 'caldera-forms-download-processor');
        }
        $download_url = add_query_arg(array(
            'action' => 'caldera_download_file',
            'token'  => $token,
        ), admin_url('admin-ajax.php'));

        return '<a href="' . esc_attr($download_url) . '" target="' . esc_attr($atts['target']) . '" class="' . esc_attr($atts['class']) . '">' . do_shortcode($content) . '</a>';
    }
}

new MOEWE_Caldera_Forms_Download_Processor();