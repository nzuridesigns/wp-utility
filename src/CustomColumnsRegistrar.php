<?php

namespace Nzuridesigns\WPUtility;

/**
 * Class for registering custom columns for various post types in WordPress.
 */
class CustomColumnsRegistrar
{
    /**
     * Registers columns for a specific post type.
     *
     * @param string $postType The post type to register columns for.
     * @param array $columnsConfig Array of column configurations.
     * @return self
     */
    public function registerColumns(string $postType, array $columnsConfig): self
    {
        add_filter("manage_{$postType}_posts_columns", function ($columns) use ($columnsConfig) {
            foreach ($columnsConfig as $column) {
                $columns[$column['field']] = $column['label'];
            }
            return $columns;
        });

        add_action("manage_{$postType}_posts_custom_column", function ($column) use ($columnsConfig) {
            foreach ($columnsConfig as $config) {
                if ($column !== $config['field']) {
                    continue;
                }

                if (isset($config['callback']) && is_callable($config['callback'])) {
                    $config['callback'](get_the_ID(), get_post_meta(get_the_ID(), $config['field'], true));
                    continue;
                }


                if (isset($config['related']) && $config['related']) {
                    $this->displayRelatedPost($config['field']);
                    continue;
                }
                if (isset($config['link']['same_as_link']) && $config['link']['same_as_link']) {
                    $link = get_post_meta(get_the_ID(), $config['field'], true);
                    $this->displayLink($link, $link);
                    continue;
                }
                if (isset($config['link']) && $config['link']) {
                    $this->displayLink($config['label']['label'], get_edit_post_link(get_the_ID()));
                    continue;
                }

                echo esc_html(get_post_meta(get_the_ID(), $config['field'], true));
            }
        });

        return $this;
    }


    /**
     * Displays a link with the given label and URL.
     *
     * @param string $label The label of the link.
     * @param string $url The URL of the link.
     */
    protected function displayLink(string $label, string $url): void
    {
        echo '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
    }

    /**
     * Displays the related post title as a link.
     *
     * @param string $metaKey The meta key used to store the related post ID.
     */
    protected function displayRelatedPost(string $metaKey): void
    {
        $post = get_post_meta(get_the_ID(), $metaKey, true);
        if (!$post) {
            echo '—';
            return;
        }

        if (is_array($post) && !empty($post)) {
            foreach ($post as $postId) {
                echo '<a href="' . esc_url(get_edit_post_link($postId)) . '">' . esc_html(get_the_title($postId)) . '</a>';
            }
            return;
        }

        if ($post) {
            echo '<a href="' . esc_url(get_edit_post_link($post)) . '">' . esc_html(get_the_title($post)) . '</a>';
        }
    }
}
