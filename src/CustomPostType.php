<?php

namespace Jcodify\CarRentalTheme\Wordpress;

class CustomPostType
{
    private $post_type;
    private $singular;
    private $plural;
    private $args;

    public function __construct($post_type, $singular, $plural, $args = [])
    {
        $this->post_type = $post_type;
        $this->singular = $singular;
        $this->plural = $plural;
        $this->args = $args;

        add_action('init', [$this, 'registerPostType']);
    }

    public function registerPostType()
    {
        $labels = [
            'name'                  => __($this->plural, 'textdomain'),
            'singular_name'         => __($this->singular, 'textdomain'),
            'menu_name'             => __($this->plural, 'textdomain'),
            'name_admin_bar'        => __($this->singular, 'textdomain'),
            'add_new'               => __('Add New', 'textdomain'),
            'add_new_item'          => __('Add New ' . $this->singular, 'textdomain'),
            'new_item'              => __('New ' . $this->singular, 'textdomain'),
            'edit_item'             => __('Edit ' . $this->singular, 'textdomain'),
            'view_item'             => __('View ' . $this->singular, 'textdomain'),
            'all_items'             => __('All ' . $this->plural, 'textdomain'),
            'search_items'          => __('Search ' . $this->plural, 'textdomain'),
            'not_found'             => __('No ' . strtolower($this->plural) . ' found.', 'textdomain'),
            'not_found_in_trash'    => __('No ' . strtolower($this->plural) . ' found in Trash.', 'textdomain'),
        ];

        $default_args = [
            'labels'             => $labels,
            'public'             => true,
            'has_archive'        => true,
            'show_in_rest'       => true,
            'supports'           => ['title', 'editor', 'thumbnail', 'custom-fields'],
            'menu_icon'          => 'dashicons-admin-post',
            'rewrite'            => ['slug' => strtolower($this->plural)],
            'capability_type'    => 'post',
            'hierarchical'       => false,
        ];

        $args = array_merge($default_args, $this->args);

        register_post_type($this->post_type, $args);
    }
}
