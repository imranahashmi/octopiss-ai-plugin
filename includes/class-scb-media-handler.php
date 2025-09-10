<?php
/**
 * Media handler for SEO Challenge Blueprint
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SCB_Media_Handler {
    
    public function __construct() {
        add_action('wp_enqueue_media', array($this, 'enqueue_media_scripts'));
    }
    
    /**
     * Enqueue media scripts for admin
     */
    public function enqueue_media_scripts() {
        if (is_admin()) {
            wp_enqueue_media();
        }
    }
    
    /**
     * Get image data by ID
     */
    public static function get_image_data($attachment_id, $size = 'thumbnail') {
        if (!$attachment_id) {
            return null;
        }
        
        $image = wp_get_attachment_image_src($attachment_id, $size);
        
        if (!$image) {
            return null;
        }
        
        return array(
            'id' => $attachment_id,
            'url' => $image[0],
            'width' => $image[1],
            'height' => $image[2],
            'alt' => get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
            'title' => get_the_title($attachment_id)
        );
    }
    
    /**
     * Get multiple images data
     */
    public static function get_images_data($attachment_ids, $size = 'thumbnail') {
        if (!is_array($attachment_ids)) {
            return array();
        }
        
        $images = array();
        foreach ($attachment_ids as $id) {
            $image_data = self::get_image_data($id, $size);
            if ($image_data) {
                $images[] = $image_data;
            }
        }
        
        return $images;
    }
    
    /**
     * Generate image HTML
     */
    public static function generate_image_html($attachment_id, $size = 'thumbnail', $attributes = array()) {
        $image_data = self::get_image_data($attachment_id, $size);
        
        if (!$image_data) {
            return '';
        }
        
        $default_attributes = array(
            'src' => $image_data['url'],
            'alt' => $image_data['alt'],
            'width' => $image_data['width'],
            'height' => $image_data['height'],
            'loading' => 'lazy'
        );
        
        $attributes = array_merge($default_attributes, $attributes);
        
        $html = '<img';
        foreach ($attributes as $key => $value) {
            $html .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }
        $html .= ' />';
        
        return $html;
    }
    
    /**
     * Generate gallery HTML
     */
    public static function generate_gallery_html($attachment_ids, $size = 'thumbnail', $columns = 4) {
        if (empty($attachment_ids)) {
            return '';
        }
        
        $images = self::get_images_data($attachment_ids, $size);
        
        if (empty($images)) {
            return '';
        }
        
        $html = '<div class="scb-gallery scb-gallery-columns-' . intval($columns) . '">';
        
        foreach ($images as $image) {
            $html .= '<div class="scb-gallery-item">';
            $html .= self::generate_image_html($image['id'], $size, array('class' => 'scb-gallery-image'));
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}