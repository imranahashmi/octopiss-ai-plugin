<?php
/**
 * Helper functions for SEO Challenge Blueprint
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if plugin is properly installed
 */
function scb_is_installed() {
    $db_version = get_option('scb_db_version');
    return !empty($db_version);
}

/**
 * Get plugin version
 */
function scb_get_version() {
    return get_option('scb_version', SCB_PLUGIN_VERSION);
}

/**
 * Check if agent has unused topics
 */
function scb_agent_has_unused_topics($agent_id) {
    $unused = SCB_Database::get_unused_topics($agent_id);
    return !empty($unused);
}

/**
 * Format frequency for display
 */
function scb_format_frequency($frequency) {
    $frequencies = array(
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly'
    );
    
    return isset($frequencies[$frequency]) ? $frequencies[$frequency] : ucfirst($frequency);
}

/**
 * Get available frequencies
 */
function scb_get_frequencies() {
    return array(
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly'
    );
}

/**
 * Validate agent data
 */
function scb_validate_agent_data($data) {
    $errors = array();
    
    // Required fields
    $required = array('title', 'city', 'category', 'frequency');
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $errors[] = "Field '{$field}' is required.";
        }
    }
    
    // Validate frequency
    if (!empty($data['frequency']) && !in_array($data['frequency'], array_keys(scb_get_frequencies()))) {
        $errors[] = 'Invalid frequency selected.';
    }
    
    // Validate topics
    if (!isset($data['topics']) || !is_array($data['topics']) || empty($data['topics'])) {
        $errors[] = 'At least one topic is required.';
    }
    
    // Validate CTA URL if CTA is enabled
    if (!empty($data['cta_enabled']) && !empty($data['cta_button_url'])) {
        if (!filter_var($data['cta_button_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'CTA button URL must be a valid URL.';
        }
    }
    
    return $errors;
}

/**
 * Log plugin errors
 */
function scb_log_error($message, $data = null) {
    if (WP_DEBUG_LOG) {
        $log_message = 'SCB Plugin Error: ' . $message;
        if ($data) {
            $log_message .= ' Data: ' . print_r($data, true);
        }
        error_log($log_message);
    }
}

/**
 * Check WordPress and PHP requirements
 */
function scb_check_requirements() {
    $errors = array();
    
    // Check WordPress version
    if (version_compare(get_bloginfo('version'), '5.0', '<')) {
        $errors[] = 'WordPress 5.0 or higher is required.';
    }
    
    // Check PHP version
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        $errors[] = 'PHP 7.4 or higher is required.';
    }
    
    // Check required PHP extensions
    $required_extensions = array('json', 'curl', 'mbstring');
    foreach ($required_extensions as $extension) {
        if (!extension_loaded($extension)) {
            $errors[] = "PHP extension '{$extension}' is required.";
        }
    }
    
    return $errors;
}

/**
 * Get content generation statistics
 */
function scb_get_stats() {
    global $wpdb;
    
    $stats = array();
    
    // Total agents
    $stats['total_agents'] = count(SCB_Database::get_agents('all'));
    $stats['active_agents'] = count(SCB_Database::get_agents('active'));
    
    // Total generated content
    $content_table = $wpdb->prefix . 'scb_generated_content';
    $stats['total_generated'] = $wpdb->get_var("SELECT COUNT(*) FROM $content_table");
    
    // Total topics used
    $usage_table = $wpdb->prefix . 'scb_topic_usage';
    $stats['total_topics_used'] = $wpdb->get_var("SELECT COUNT(*) FROM $usage_table");
    
    return $stats;
}

/**
 * Clean up old generated content (optional maintenance function)
 */
function scb_cleanup_old_content($days = 30) {
    global $wpdb;
    
    $table = $wpdb->prefix . 'scb_generated_content';
    $date_threshold = date('Y-m-d H:i:s', strtotime("-{$days} days"));
    
    $wpdb->query($wpdb->prepare(
        "DELETE FROM $table WHERE generation_date < %s",
        $date_threshold
    ));
}