<?php

namespace Jcodify\CarRentalTheme\Wordpress;

class CustomTaxonomy
{
    private $taxonomy;
    private $singular;
    private $plural;
    private $post_types;
    private $args;
    private $default_terms;

    public function __construct($taxonomy, $singular, $plural, $post_types = [], $args = [], $default_terms = [])
    {
        $this->taxonomy = $taxonomy;
        $this->singular = $singular;
        $this->plural = $plural;
        $this->post_types = $post_types;
        $this->args = $args;
        $this->default_terms = $default_terms;

        add_action('init', [$this, 'registerTaxonomy']);
        add_action('init', [$this, 'addDefaultTerms']); // Hook to add default terms
    }

    public function registerTaxonomy()
    {
        $labels = [
            'name'                       => __($this->plural, 'textdomain'),
            'singular_name'              => __($this->singular, 'textdomain'),
            'search_items'               => __('Search ' . $this->plural, 'textdomain'),
            'all_items'                  => __('All ' . $this->plural, 'textdomain'),
            'parent_item'                => __('Parent ' . $this->singular, 'textdomain'),
            'parent_item_colon'          => __('Parent ' . $this->singular . ':', 'textdomain'),
            'edit_item'                  => __('Edit ' . $this->singular, 'textdomain'),
            'update_item'                => __('Update ' . $this->singular, 'textdomain'),
            'add_new_item'               => __('Add New ' . $this->singular, 'textdomain'),
            'new_item_name'              => __('New ' . $this->singular . ' Name', 'textdomain'),
            'separate_items_with_commas' => __('Separate ' . strtolower($this->plural) . ' with commas', 'textdomain'),
            'add_or_remove_items'        => __('Add or remove ' . strtolower($this->plural), 'textdomain'),
            'choose_from_most_used'      => __('Choose from the most used ' . strtolower($this->plural), 'textdomain'),
            'not_found'                  => __('No ' . strtolower($this->plural) . ' found.', 'textdomain'),
            'menu_name'                  => __($this->plural, 'textdomain'),
        ];

        $default_args = [
            'labels'            => $labels,
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => strtolower($this->taxonomy)],
            'show_in_rest'      => true,
        ];

        $args = array_merge($default_args, $this->args);

        register_taxonomy($this->taxonomy, $this->post_types, $args);
    }

    // Method to add default terms to the taxonomy
    public function addDefaultTerms()
    {
        if (!empty($this->default_terms)) {
            foreach ($this->default_terms as $term) {
                // Check if the term already exists to avoid duplicates
                if (!term_exists($term, $this->taxonomy)) {
                    wp_insert_term($term, $this->taxonomy);
                }
            }
        }
    }
}
