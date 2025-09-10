<?php
/**
 * Database operations for SEO Challenge Blueprint
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SCB_Database {
    
    /**
     * Create plugin database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Agents table
        $agents_table = $wpdb->prefix . 'scb_agents';
        $agents_sql = "CREATE TABLE $agents_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            city varchar(100) NOT NULL,
            category varchar(100) NOT NULL,
            frequency varchar(50) NOT NULL,
            topics text NOT NULL,
            used_topics text,
            featured_image_id int(11),
            gallery_images text,
            meta_description text,
            tags text,
            cta_enabled tinyint(1) DEFAULT 0,
            cta_heading varchar(255),
            cta_text text,
            cta_button_text varchar(100),
            cta_button_url varchar(255),
            cta_icon varchar(100),
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Topics usage tracking table
        $topics_table = $wpdb->prefix . 'scb_topic_usage';
        $topics_sql = "CREATE TABLE $topics_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            agent_id int(11) NOT NULL,
            topic varchar(255) NOT NULL,
            used_at datetime DEFAULT CURRENT_TIMESTAMP,
            post_id int(11),
            PRIMARY KEY (id),
            FOREIGN KEY (agent_id) REFERENCES $agents_table(id) ON DELETE CASCADE,
            UNIQUE KEY unique_agent_topic (agent_id, topic)
        ) $charset_collate;";
        
        // Generated content table
        $content_table = $wpdb->prefix . 'scb_generated_content';
        $content_sql = "CREATE TABLE $content_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            agent_id int(11) NOT NULL,
            post_id int(11) NOT NULL,
            topic varchar(255) NOT NULL,
            ai_prompt text,
            generated_content longtext,
            generation_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            FOREIGN KEY (agent_id) REFERENCES $agents_table(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($agents_sql);
        dbDelta($topics_sql);
        dbDelta($content_sql);
        
        // Update version
        update_option('scb_db_version', '1.0');
    }
    
    /**
     * Get all agents
     */
    public static function get_agents($status = 'active') {
        global $wpdb;
        
        $table = $wpdb->prefix . 'scb_agents';
        
        if ($status === 'all') {
            $results = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
        } else {
            $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE status = %s ORDER BY created_at DESC", $status));
        }
        
        return $results;
    }
    
    /**
     * Get agent by ID
     */
    public static function get_agent($id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'scb_agents';
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
        
        if ($result) {
            // Decode JSON fields
            $result->topics = json_decode($result->topics, true) ?: array();
            $result->used_topics = json_decode($result->used_topics, true) ?: array();
            $result->gallery_images = json_decode($result->gallery_images, true) ?: array();
            $result->tags = $result->tags ? explode(',', $result->tags) : array();
        }
        
        return $result;
    }
    
    /**
     * Save agent
     */
    public static function save_agent($data, $id = null) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'scb_agents';
        
        // Prepare data
        $agent_data = array(
            'title' => sanitize_text_field($data['title']),
            'city' => sanitize_text_field($data['city']),
            'category' => sanitize_text_field($data['category']),
            'frequency' => sanitize_text_field($data['frequency']),
            'topics' => json_encode($data['topics']),
            'featured_image_id' => intval($data['featured_image_id']),
            'gallery_images' => json_encode($data['gallery_images']),
            'meta_description' => sanitize_textarea_field($data['meta_description']),
            'tags' => is_array($data['tags']) ? implode(',', array_map('sanitize_text_field', $data['tags'])) : sanitize_text_field($data['tags']),
            'cta_enabled' => isset($data['cta_enabled']) ? 1 : 0,
            'cta_heading' => sanitize_text_field($data['cta_heading']),
            'cta_text' => sanitize_textarea_field($data['cta_text']),
            'cta_button_text' => sanitize_text_field($data['cta_button_text']),
            'cta_button_url' => esc_url_raw($data['cta_button_url']),
            'cta_icon' => sanitize_text_field($data['cta_icon']),
            'status' => sanitize_text_field($data['status']) ?: 'active'
        );
        
        if ($id) {
            // Update existing agent
            $result = $wpdb->update($table, $agent_data, array('id' => $id));
            return $result !== false ? $id : false;
        } else {
            // Insert new agent
            $result = $wpdb->insert($table, $agent_data);
            return $result !== false ? $wpdb->insert_id : false;
        }
    }
    
    /**
     * Delete agent
     */
    public static function delete_agent($id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'scb_agents';
        return $wpdb->delete($table, array('id' => $id));
    }
    
    /**
     * Get unused topics for an agent
     */
    public static function get_unused_topics($agent_id) {
        global $wpdb;
        
        $agent = self::get_agent($agent_id);
        if (!$agent) {
            return array();
        }
        
        $all_topics = $agent->topics;
        $used_topics = $agent->used_topics;
        
        return array_diff($all_topics, $used_topics);
    }
    
    /**
     * Mark topic as used
     */
    public static function mark_topic_used($agent_id, $topic, $post_id = null) {
        global $wpdb;
        
        $usage_table = $wpdb->prefix . 'scb_topic_usage';
        $agent_table = $wpdb->prefix . 'scb_agents';
        
        // Insert into usage tracking table
        $wpdb->insert(
            $usage_table,
            array(
                'agent_id' => $agent_id,
                'topic' => $topic,
                'post_id' => $post_id
            )
        );
        
        // Update agent's used_topics
        $agent = self::get_agent($agent_id);
        if ($agent) {
            $used_topics = $agent->used_topics;
            if (!in_array($topic, $used_topics)) {
                $used_topics[] = $topic;
                $wpdb->update(
                    $agent_table,
                    array('used_topics' => json_encode($used_topics)),
                    array('id' => $agent_id)
                );
            }
        }
    }
}