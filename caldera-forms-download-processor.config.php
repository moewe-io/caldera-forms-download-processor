<?php
echo Caldera_Forms_Processor_UI::config_fields(
    array(
        array(
            'id'       => 'download_file',
            'label'    => __('File', 'caldera-forms-download-processor'),
            'desc'     => __('Full path to file, relative to WordPress upload folder', 'caldera-forms-download-processor'),
            'type'     => 'text',
            'required' => true,
            'magic'    => false,
        ),
        array(
            'id'       => 'download_file_timeout',
            'label'    => __('Timeout', 'caldera-forms-download-processor'),
            'desc'     => __('Download timeout (in minutes)', 'caldera-forms-download-processor'),
            'type'     => 'number',
            'required' => true,
            'magic'    => false,
        ),
        array(
            'id'       => 'download_file_field',
            'label'    => __('Field id', 'caldera-forms-download-processor'),
            'type'     => 'text',
            'required' => true,
            'magic'    => false,
        ),
        array(
            'id'       => 'download_file_token_field',
            'label'    => __('Field id for token', 'caldera-forms-download-processor'),
            'type'     => 'text',
            'required' => true,
            'magic'    => false,
        ),
    )
);