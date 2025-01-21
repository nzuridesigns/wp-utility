<?php

namespace Jcodify\CarRentalTheme\Wordpress\MetaBox;

// the class name is clash with the react another class let make it unique
class RJSMetaBox
{
    /**
     * Create a new ReactMetabox
     *
     * @param string $id
     * @param string $title
     * @param string $screen
     * @param array $fields
     * @param array $config array('regisiter_save' => true, 'folder_path_to_build' => 'meta-boxes/rental-price-metabox')
     * @param array $additionalLocalizedData used to pass additional data to React
     */
    public function __construct(
        private $id,
        private $title,
        private $screen = 'post',
        private $fields = [],
        private array $config = array('regisiter_save' => true),
        private $additionalLocalizedData = array()
    ) {

        $this->id = $id;
        $this->title = $title;
        $this->screen = $screen;
        $this->fields = $fields;
        $this->config = $config;
        $this->additionalLocalizedData = $additionalLocalizedData;

        // if folder path to build is not set use raise an error
        if (!isset($this->config['folder_path_to_build'])) {
            throw new \Exception('Folder path to build is not set');
        }
        // Hook into WordPress actions
        add_action('add_meta_boxes', [$this, 'addMetabox']);
        if (isset($this->config['regisiter_save']) && $this->config['regisiter_save']) {
            add_action('save_post', [$this, 'saveMetabox']);
        }
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
    }

    // Register the metabox with WordPress
    public function addMetabox()
    {
        add_meta_box(
            $this->id,
            $this->title,
            [$this, 'renderMetabox'],
            $this->screen,
            'normal',
            'high'
        );
    }

    public function getRootId()
    {
        return $this->id . '-root';
    }
    // Render the metabox
    public function renderMetabox($post)
    {
        // Output a div that React will hook into
        echo '<div id="' . $this->getRootId() . '">Loading...</div>';
    }

    // Enqueue React script and styles
    public function enqueueScripts($hook)
    {
        $folder_path_to_build = $this->config['folder_path_to_build'];

        if ($hook === 'post.php' || $hook === 'post-new.php') {
            //Todo: Add dependencies css
            $assets = require get_theme_file_path("/$folder_path_to_build/build/index.asset.php");
            $script_name = '' . $this->id . '-script';

            wp_enqueue_script(
                $script_name,
                get_theme_file_uri("/$folder_path_to_build/build/index.js"),
                $assets['dependencies'],
                $assets['version'],
                true
            );

            // Localize post meta data to pass to React
            $unique_meta_box_id = "$this->id" . '_MetaBoxData';
            $localizedData = array_merge([
                'postId' => get_the_ID(),
                'fields' => $this->getMetaValues(get_the_ID()),
                'rootId' => $this->getRootId(),
                'fieldSchema' => $this->fields,
            ], $this->additionalLocalizedData);

            wp_localize_script($script_name, $unique_meta_box_id, $localizedData);

            // file exists
            if (file_exists(get_theme_file_path("/$folder_path_to_build/build/style-index.css"))) {
                wp_enqueue_style(
                    '' . $this->id . '-style',
                    get_theme_file_uri("/$folder_path_to_build/build/style-index.css"),
                    [],
                    $assets['version']
                );
            }
        }
    }

    public function saveMetabox($post_id)
    {
        foreach ($this->fields as $field) {
            if (isset($_POST[$field['name']])) {
                $value = $_POST[$field['name']];
                if (is_array($value)) {
                    // Sanitize each element in the array
                    $sanitized_values = array_map('sanitize_text_field', $value);
                    update_post_meta($post_id, $field['name'], $sanitized_values);
                } else {
                    update_post_meta($post_id, $field['name'], sanitize_text_field($value));
                }
            }
        }
    }

    // Retrieve the saved meta values, supporting arrays
    private function getMetaValues($post_id)
    {
        $values = [];
        foreach ($this->fields as $field) {
            $meta_value = get_post_meta($post_id, $field['name'], true);
            $values[$field['name']] = is_array($meta_value) ? $meta_value : [$meta_value];
        }
        return $values;
    }
}
