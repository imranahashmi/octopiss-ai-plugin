<?php
/**
 * Admin interface for SEO Challenge Blueprint
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SCB_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_scb_save_agent', array($this, 'ajax_save_agent'));
        add_action('wp_ajax_scb_delete_agent', array($this, 'ajax_delete_agent'));
        add_action('wp_ajax_scb_get_media', array($this, 'ajax_get_media'));
        add_action('wp_ajax_scb_generate_content_now', array($this, 'ajax_generate_content_now'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'SEO Challenge Blueprint',
            'SEO Blueprint',
            'manage_options',
            'scb-agents',
            array($this, 'agents_page'),
            'dashicons-megaphone',
            30
        );
        
        add_submenu_page(
            'scb-agents',
            'Agents',
            'Agents',
            'manage_options',
            'scb-agents',
            array($this, 'agents_page')
        );
        
        add_submenu_page(
            'scb-agents',
            'Add Agent',
            'Add Agent',
            'manage_options',
            'scb-add-agent',
            array($this, 'add_agent_page')
        );
        
        add_submenu_page(
            'scb-agents',
            'Settings',
            'Settings',
            'manage_options',
            'scb-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'scb-') === false) {
            return;
        }
        
        wp_enqueue_media();
        wp_enqueue_script('scb-admin-js', SCB_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'media-upload'), SCB_PLUGIN_VERSION, true);
        wp_enqueue_style('scb-admin-css', SCB_PLUGIN_URL . 'assets/css/admin.css', array(), SCB_PLUGIN_VERSION);
        
        wp_localize_script('scb-admin-js', 'scb_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('scb_nonce')
        ));
    }
    
    /**
     * Agents listing page
     */
    public function agents_page() {
        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
            $this->edit_agent_page();
            return;
        }
        
        $agents = SCB_Database::get_agents('all');
        include SCB_PLUGIN_PATH . 'admin/views/agents-list.php';
    }
    
    /**
     * Add agent page
     */
    public function add_agent_page() {
        include SCB_PLUGIN_PATH . 'admin/views/agent-form.php';
    }
    
    /**
     * Edit agent page
     */
    public function edit_agent_page() {
        $agent_id = intval($_GET['id']);
        $agent = SCB_Database::get_agent($agent_id);
        
        if (!$agent) {
            wp_die('Agent not found');
        }
        
        include SCB_PLUGIN_PATH . 'admin/views/agent-form.php';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        include SCB_PLUGIN_PATH . 'admin/views/settings.php';
    }
    
    /**
     * AJAX handler to save agent
     */
    public function ajax_save_agent() {
        check_ajax_referer('scb_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $agent_id = isset($_POST['agent_id']) ? intval($_POST['agent_id']) : null;
        
        // Prepare agent data
        $agent_data = array(
            'title' => sanitize_text_field($_POST['title']),
            'city' => sanitize_text_field($_POST['city']),
            'category' => sanitize_text_field($_POST['category']),
            'frequency' => sanitize_text_field($_POST['frequency']),
            'topics' => isset($_POST['topics']) ? array_map('sanitize_text_field', $_POST['topics']) : array(),
            'featured_image_id' => intval($_POST['featured_image_id']),
            'gallery_images' => isset($_POST['gallery_images']) ? array_map('intval', $_POST['gallery_images']) : array(),
            'meta_description' => sanitize_textarea_field($_POST['meta_description']),
            'tags' => isset($_POST['tags']) ? array_map('sanitize_text_field', $_POST['tags']) : array(),
            'cta_enabled' => isset($_POST['cta_enabled']),
            'cta_heading' => sanitize_text_field($_POST['cta_heading']),
            'cta_text' => sanitize_textarea_field($_POST['cta_text']),
            'cta_button_text' => sanitize_text_field($_POST['cta_button_text']),
            'cta_button_url' => esc_url_raw($_POST['cta_button_url']),
            'cta_icon' => sanitize_text_field($_POST['cta_icon']),
            'status' => sanitize_text_field($_POST['status'])
        );
        
        // If editing, filter out topics that are already used
        if ($agent_id) {
            $existing_agent = SCB_Database::get_agent($agent_id);
            if ($existing_agent) {
                $new_topics = array_diff($agent_data['topics'], $existing_agent->used_topics);
                $agent_data['topics'] = array_merge($existing_agent->used_topics, $new_topics);
            }
        }
        
        $result = SCB_Database::save_agent($agent_data, $agent_id);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => $agent_id ? 'Agent updated successfully' : 'Agent created successfully',
                'agent_id' => $result
            ));
        } else {
            wp_send_json_error('Failed to save agent');
        }
    }
    
    /**
     * AJAX handler to delete agent
     */
    public function ajax_delete_agent() {
        check_ajax_referer('scb_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $agent_id = intval($_POST['agent_id']);
        $result = SCB_Database::delete_agent($agent_id);
        
        if ($result) {
            wp_send_json_success('Agent deleted successfully');
        } else {
            wp_send_json_error('Failed to delete agent');
        }
    }
    
    /**
     * AJAX handler to get media
     */
    public function ajax_get_media() {
        check_ajax_referer('scb_nonce', 'nonce');
        
        $attachment_id = intval($_POST['attachment_id']);
        $attachment = wp_get_attachment_image_src($attachment_id, 'thumbnail');
        
        if ($attachment) {
            wp_send_json_success(array(
                'url' => $attachment[0],
                'width' => $attachment[1],
                'height' => $attachment[2]
            ));
        } else {
            wp_send_json_error('Attachment not found');
        }
    }
    
    /**
     * AJAX handler to generate content now
     */
    public function ajax_generate_content_now() {
        check_ajax_referer('scb_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $agent_id = intval($_POST['agent_id']);
        $agent_data = SCB_Database::get_agent($agent_id);
        
        if (!$agent_data) {
            wp_send_json_error('Agent not found');
        }
        
        $agent = new SCB_Agent($agent_data);
        $content_generator = new SCB_Content_Generator();
        
        $result = $content_generator->generate_content_for_agent($agent);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => 'Content generated successfully',
                'post_id' => $result
            ));
        } else {
            wp_send_json_error('Failed to generate content. Check if agent has unused topics.');
        }
    }
}