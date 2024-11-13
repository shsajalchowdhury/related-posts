<?php
/**
 * Plugin Name: Related Posts
 * Description: Displays a list of related posts based on the current post's category.
 * Version: 1.0.0
 * Author: SH Sajal Chowdhury
 * Text Domain: related-posts
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Related_Posts_Plugin {

   
    public function __construct() {
        add_filter( 'the_content', [ $this, 'display_related_posts' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
    }

    /**
     * Enqueue the plugin's CSS styles.
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'related-posts-style',
            plugin_dir_url( __FILE__ ) . 'css/related-posts.css',
            [],
            '1.1.0'
        );
    }

    /**
     * Display the related posts after the content.
     */
    public function display_related_posts( $content ) {
        if ( ! is_single() || ! in_the_loop() || ! is_main_query() ) {
            return $content;
        }

        $related_posts = $this->get_related_posts();

        if ( ! empty( $related_posts ) ) {
            ob_start();

            echo '<div class="related-posts">';
            echo '<h3>' . esc_html__( 'Related Posts', 'related-posts' ) . '</h3>';
            echo '<ul>';

            foreach ( $related_posts as $post ) {
                $thumbnail = get_the_post_thumbnail( $post->ID, 'thumbnail', [
                    'alt' => esc_attr( get_the_title( $post->ID ) ),
                ] );
                $author_name = get_the_author_meta( 'display_name', $post->post_author );
                $post_date   = get_the_date( '', $post->ID );

                echo '<li>';
                echo '<a href="' . esc_url( get_permalink( $post->ID ) ) . '">';
                if ( $thumbnail ) {
                    echo '<div class="related-post-thumbnail">' . $thumbnail . '</div>';
                }
                echo '<div class="related-post-details">';
                echo '<span class="related-post-title">' . esc_html( get_the_title( $post->ID ) ) . '</span>';
                echo '<span class="related-post-meta">';
                echo '<span class="related-post-author">' . esc_html( $author_name ) . '</span> | ';
                echo '<span class="related-post-date">' . esc_html( $post_date ) . '</span>';
                echo '</span>';
                echo '</div>';
                echo '</a>';
                echo '</li>';
            }

            echo '</ul>';
            echo '</div>';

            $content .= ob_get_clean();
        }

        return $content;
    }

    /**
     * Get related posts based on the current post's category.
     */
    private function get_related_posts() {
        global $post;

        $categories = wp_get_post_categories( $post->ID );

        if ( empty( $categories ) ) {
            return [];
        }

        $args = [
            'category__in'   => $categories,
            'post__not_in'   => [ $post->ID ],
            'posts_per_page' => 5,
            'orderby'        => 'rand',
            'no_found_rows'  => true,
        ];

        $related_posts = get_posts( $args );

        return $related_posts;
    }
}

// Initialize the plugin.
new Related_Posts_Plugin();
