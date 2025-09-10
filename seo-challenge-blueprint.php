<?php
/**
 * Plugin Name: SEO Challenge Blueprint
 * Plugin URI: https://github.com/imranahashmi/octopiss-ai-plugin
 * Description: AI-powered SEO content generation plugin with agent management and topic tracking
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: seo-challenge-blueprint
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SCB_PLUGIN_VERSION', '1.0.0');
define('SCB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SCB_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * Main plugin class
 */
class SEOChallengeBlueprint {
    
    /**
     * Initialize the plugin
     */
    public function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Initialize plugin functionality
     */
    public function init() {
        // Load required files
        $this->load_dependencies();
        
        // Initialize admin functionality
        if (is_admin()) {
            new SCB_Admin();
        }
        
        // Initialize database operations
        new SCB_Database();
        
        // Initialize content generation
        new SCB_Content_Generator();
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once SCB_PLUGIN_PATH . 'includes/class-scb-database.php';
        require_once SCB_PLUGIN_PATH . 'includes/class-scb-admin.php';
        require_once SCB_PLUGIN_PATH . 'includes/class-scb-agent.php';
        require_once SCB_PLUGIN_PATH . 'includes/class-scb-content-generator.php';
        require_once SCB_PLUGIN_PATH . 'includes/class-scb-media-handler.php';
    }
    
    /**
     * Plugin activation hook
     */
    public function activate() {
        // Create database tables
        SCB_Database::create_tables();
        
        // Set default options
        add_option('scb_version', SCB_PLUGIN_VERSION);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation hook
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

// Initialize the plugin
new SEOChallengeBlueprint();