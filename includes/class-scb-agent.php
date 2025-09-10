<?php
/**
 * Agent model for SEO Challenge Blueprint
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SCB_Agent {
    
    public $id;
    public $title;
    public $city;
    public $category;
    public $frequency;
    public $topics;
    public $used_topics;
    public $featured_image_id;
    public $gallery_images;
    public $meta_description;
    public $tags;
    public $cta_enabled;
    public $cta_heading;
    public $cta_text;
    public $cta_button_text;
    public $cta_button_url;
    public $cta_icon;
    public $status;
    public $created_at;
    public $updated_at;
    
    /**
     * Constructor
     */
    public function __construct($data = null) {
        if ($data) {
            $this->load_from_data($data);
        }
    }
    
    /**
     * Load agent from database data
     */
    private function load_from_data($data) {
        $this->id = $data->id;
        $this->title = $data->title;
        $this->city = $data->city;
        $this->category = $data->category;
        $this->frequency = $data->frequency;
        $this->topics = is_string($data->topics) ? json_decode($data->topics, true) : $data->topics;
        $this->used_topics = is_string($data->used_topics) ? json_decode($data->used_topics, true) : $data->used_topics;
        $this->featured_image_id = $data->featured_image_id;
        $this->gallery_images = is_string($data->gallery_images) ? json_decode($data->gallery_images, true) : $data->gallery_images;
        $this->meta_description = $data->meta_description;
        $this->tags = is_string($data->tags) ? explode(',', $data->tags) : $data->tags;
        $this->cta_enabled = $data->cta_enabled;
        $this->cta_heading = $data->cta_heading;
        $this->cta_text = $data->cta_text;
        $this->cta_button_text = $data->cta_button_text;
        $this->cta_button_url = $data->cta_button_url;
        $this->cta_icon = $data->cta_icon;
        $this->status = $data->status;
        $this->created_at = $data->created_at;
        $this->updated_at = $data->updated_at;
    }
    
    /**
     * Get unused topics
     */
    public function get_unused_topics() {
        if (!$this->topics || !is_array($this->topics)) {
            return array();
        }
        
        $used = $this->used_topics ?: array();
        return array_diff($this->topics, $used);
    }
    
    /**
     * Get next topic for content generation
     */
    public function get_next_topic() {
        $unused_topics = $this->get_unused_topics();
        
        if (empty($unused_topics)) {
            return null;
        }
        
        // Return first unused topic
        return reset($unused_topics);
    }
    
    /**
     * Mark topic as used
     */
    public function mark_topic_used($topic, $post_id = null) {
        if (!$this->used_topics) {
            $this->used_topics = array();
        }
        
        if (!in_array($topic, $this->used_topics)) {
            $this->used_topics[] = $topic;
            SCB_Database::mark_topic_used($this->id, $topic, $post_id);
        }
    }
    
    /**
     * Get featured image URL
     */
    public function get_featured_image_url($size = 'thumbnail') {
        if (!$this->featured_image_id) {
            return null;
        }
        
        $image = wp_get_attachment_image_src($this->featured_image_id, $size);
        return $image ? $image[0] : null;
    }
    
    /**
     * Get gallery images URLs
     */
    public function get_gallery_images_urls($size = 'thumbnail') {
        if (!$this->gallery_images || !is_array($this->gallery_images)) {
            return array();
        }
        
        $urls = array();
        foreach ($this->gallery_images as $image_id) {
            $image = wp_get_attachment_image_src($image_id, $size);
            if ($image) {
                $urls[] = array(
                    'id' => $image_id,
                    'url' => $image[0],
                    'width' => $image[1],
                    'height' => $image[2]
                );
            }
        }
        
        return $urls;
    }
    
    /**
     * Get CTA configuration
     */
    public function get_cta_config() {
        if (!$this->cta_enabled) {
            return null;
        }
        
        return array(
            'heading' => $this->cta_heading,
            'text' => $this->cta_text,
            'button_text' => $this->cta_button_text,
            'button_url' => $this->cta_button_url,
            'icon' => $this->cta_icon
        );
    }
    
    /**
     * Should generate content based on frequency
     */
    public function should_generate_content() {
        // This would implement frequency-based logic
        // For now, return true if there are unused topics
        return !empty($this->get_unused_topics());
    }
    
    /**
     * Get agent summary for listing
     */
    public function get_summary() {
        $unused_count = count($this->get_unused_topics());
        $total_count = count($this->topics ?: array());
        
        return array(
            'id' => $this->id,
            'title' => $this->title,
            'city' => $this->city,
            'category' => $this->category,
            'frequency' => $this->frequency,
            'topics_remaining' => $unused_count,
            'topics_total' => $total_count,
            'featured_image_url' => $this->get_featured_image_url(),
            'status' => $this->status,
            'created_at' => $this->created_at
        );
    }
}