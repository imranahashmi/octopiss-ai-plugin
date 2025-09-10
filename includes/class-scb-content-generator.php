<?php
/**
 * Content generator for SEO Challenge Blueprint
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SCB_Content_Generator {
    
    public function __construct() {
        add_action('init', array($this, 'schedule_content_generation'));
        add_action('scb_generate_content', array($this, 'generate_scheduled_content'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_styles'));
    }
    
    /**
     * Enqueue frontend styles
     */
    public function enqueue_frontend_styles() {
        wp_enqueue_style('scb-frontend-css', SCB_PLUGIN_URL . 'assets/css/frontend.css', array(), SCB_PLUGIN_VERSION);
    }
    
    /**
     * Schedule content generation
     */
    public function schedule_content_generation() {
        if (!wp_next_scheduled('scb_generate_content')) {
            wp_schedule_event(time(), 'daily', 'scb_generate_content');
        }
    }
    
    /**
     * Generate scheduled content for all active agents
     */
    public function generate_scheduled_content() {
        $agents = SCB_Database::get_agents('active');
        
        foreach ($agents as $agent_data) {
            $agent = new SCB_Agent($agent_data);
            
            if ($agent->should_generate_content()) {
                $this->generate_content_for_agent($agent);
            }
        }
    }
    
    /**
     * Generate content for a specific agent
     */
    public function generate_content_for_agent($agent) {
        $topic = $agent->get_next_topic();
        
        if (!$topic) {
            return false;
        }
        
        // Generate content using AI
        $content = $this->generate_ai_content($agent, $topic);
        
        if (!$content) {
            return false;
        }
        
        // Create WordPress post
        $post_id = $this->create_post($agent, $topic, $content);
        
        if ($post_id) {
            // Mark topic as used
            $agent->mark_topic_used($topic, $post_id);
            
            // Save generation record
            $this->save_generation_record($agent->id, $post_id, $topic, $content);
            
            return $post_id;
        }
        
        return false;
    }
    
    /**
     * Generate AI content
     */
    private function generate_ai_content($agent, $topic) {
        // Create natural city mention prompt
        $prompt = $this->create_ai_prompt($agent, $topic);
        
        // This is where you would integrate with your AI service
        // For now, return a placeholder
        $content = $this->call_ai_service($prompt);
        
        return $content;
    }
    
    /**
     * Create AI prompt with natural city mentions
     */
    private function create_ai_prompt($agent, $topic) {
        $settings = get_option('scb_settings', array());
        $content_length = isset($settings['content_length']) ? $settings['content_length'] : 1000;
        $city_frequency = isset($settings['city_mention_frequency']) ? $settings['city_mention_frequency'] : 3;
        
        $city_variations = array(
            "local businesses in {$agent->city}",
            "our {$agent->city} clients",
            "based in {$agent->city}",
            "the {$agent->city} area",
            "residents of {$agent->city}",
            "{$agent->city}-based",
            "throughout {$agent->city}",
            "the {$agent->city} community",
            "people in {$agent->city}",
            "within {$agent->city}",
            "around {$agent->city}",
            "the greater {$agent->city} area"
        );
        
        $selected_variations = array_slice($city_variations, 0, min(4, count($city_variations)));
        
        $prompt = "Write a comprehensive, engaging article about '{$topic}' in the {$agent->category} category. ";
        $prompt .= "The content should be approximately {$content_length} words and well-structured with clear headings and subheadings. ";
        $prompt .= "\n\nIMPORTANT CITY MENTION GUIDELINES:\n";
        $prompt .= "- Naturally mention the city '{$agent->city}' exactly {$city_frequency} times throughout the content\n";
        $prompt .= "- Use varied phrases like: " . implode(', ', $selected_variations) . "\n";
        $prompt .= "- DO NOT add the city name to the article title\n";
        $prompt .= "- Make city mentions feel natural and contextual, not forced\n";
        $prompt .= "- Distribute mentions evenly throughout the article\n";
        $prompt .= "- Avoid repetitive phrasing\n\n";
        $prompt .= "CONTENT REQUIREMENTS:\n";
        $prompt .= "- Write an engaging title (without city name)\n";
        $prompt .= "- Create well-structured content with H2 and H3 headings\n";
        $prompt .= "- Include practical, valuable information\n";
        $prompt .= "- Use a professional yet accessible tone\n";
        $prompt .= "- Focus on providing real value to readers interested in {$agent->category}\n";
        $prompt .= "- Include actionable insights or tips where relevant\n\n";
        $prompt .= "Format the response as JSON with 'title' and 'content' keys.";
        
        return $prompt;
    }
    
    /**
     * Call AI service (placeholder implementation)
     */
    private function call_ai_service($prompt) {
        // Get API settings
        $settings = get_option('scb_settings', array());
        $ai_service = $settings['ai_service'] ?? 'openai';
        $api_key = $settings['api_key'] ?? '';
        
        // For now, return sample content - in production, this would call the actual AI service
        if (empty($api_key)) {
            error_log('SCB: No AI API key configured');
            return $this->generate_sample_content($prompt);
        }
        
        // Example of how OpenAI integration would work:
        /*
        if ($ai_service === 'openai') {
            return $this->call_openai_api($prompt, $api_key);
        } elseif ($ai_service === 'claude') {
            return $this->call_claude_api($prompt, $api_key);
        }
        */
        
        // For demo purposes, return sample content
        return $this->generate_sample_content($prompt);
    }
    
    /**
     * Generate sample content for demonstration
     */
    private function generate_sample_content($prompt) {
        // Extract topic from prompt for title generation
        preg_match("/about '(.+?)'/", $prompt, $matches);
        $topic = $matches[1] ?? 'Sample Topic';
        
        // Extract city from prompt
        preg_match("/city '(.+?)'/", $prompt, $city_matches);
        $city = $city_matches[1] ?? 'Sample City';
        
        $titles = array(
            "The Ultimate Guide to " . $topic,
            "Everything You Need to Know About " . $topic,
            "A Comprehensive Look at " . $topic,
            "Understanding " . $topic . ": A Professional Perspective",
            "Expert Insights on " . $topic
        );
        
        $sample_content = $this->generate_sample_article_content($topic, $city);
        
        return array(
            'title' => $titles[array_rand($titles)],
            'content' => $sample_content
        );
    }
    
    /**
     * Generate realistic sample article content
     */
    private function generate_sample_article_content($topic, $city) {
        $content = "<p>In today's rapidly evolving landscape, understanding {$topic} has become increasingly important for individuals and businesses alike. This comprehensive guide will explore the key aspects of {$topic} and provide valuable insights for our readers.</p>";
        
        $content .= "<h2>What is {$topic}?</h2>";
        $content .= "<p>{$topic} represents a significant area of focus in the modern world. For local businesses in {$city}, staying informed about {$topic} can provide a competitive advantage and help drive success in an increasingly competitive market.</p>";
        
        $content .= "<h2>Key Benefits and Considerations</h2>";
        $content .= "<p>When it comes to {$topic}, there are several important factors to consider:</p>";
        $content .= "<ul>";
        $content .= "<li>Understanding the fundamental principles</li>";
        $content .= "<li>Implementing best practices</li>";
        $content .= "<li>Staying updated with latest trends</li>";
        $content .= "<li>Measuring success and ROI</li>";
        $content .= "</ul>";
        
        $content .= "<h2>Best Practices for Success</h2>";
        $content .= "<p>Based on extensive research and real-world experience, we've identified several best practices that can help individuals and organizations maximize their success with {$topic}. These strategies have been proven effective by our {$city} clients and industry professionals.</p>";
        
        $content .= "<h3>Getting Started</h3>";
        $content .= "<p>The first step in any successful {$topic} initiative is proper planning and preparation. This involves understanding your goals, identifying key stakeholders, and developing a clear strategy for implementation.</p>";
        
        $content .= "<h3>Implementation Strategy</h3>";
        $content .= "<p>A well-executed implementation strategy is crucial for success. This includes setting realistic timelines, allocating appropriate resources, and establishing clear metrics for measuring progress and success.</p>";
        
        $content .= "<h2>Common Challenges and Solutions</h2>";
        $content .= "<p>While {$topic} offers numerous benefits, it's important to be aware of potential challenges that may arise during implementation. Understanding these challenges and having solutions ready can help ensure a smoother process.</p>";
        
        $content .= "<h2>Future Outlook</h2>";
        $content .= "<p>Looking ahead, {$topic} will continue to evolve and adapt to changing market conditions and technological advances. Staying informed about emerging trends and developments will be crucial for long-term success throughout {$city} and beyond.</p>";
        
        $content .= "<h2>Conclusion</h2>";
        $content .= "<p>In conclusion, {$topic} represents a valuable opportunity for growth and success. By understanding the key principles, implementing best practices, and staying informed about emerging trends, individuals and organizations can position themselves for long-term success in this dynamic field.</p>";
        
        return $content;
    }
    
    /**
     * Example OpenAI API integration (commented out - requires API key and proper error handling)
     */
    /*
    private function call_openai_api($prompt, $api_key) {
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $data = array(
            'model' => 'gpt-3.5-turbo',
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'max_tokens' => 2000,
            'temperature' => 0.7
        );
        
        $args = array(
            'body' => json_encode($data),
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ),
            'timeout' => 60
        );
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            error_log('SCB OpenAI API Error: ' . $response->get_error_message());
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['choices'][0]['message']['content'])) {
            $content = $data['choices'][0]['message']['content'];
            
            // Try to parse as JSON if formatted that way
            $parsed = json_decode($content, true);
            if ($parsed && isset($parsed['title']) && isset($parsed['content'])) {
                return $parsed;
            }
            
            // Fallback: extract title and content manually
            return $this->parse_ai_response($content);
        }
        
        return false;
    }
    */
    
    /**
     * Create WordPress post with generated content
     */
    private function create_post($agent, $topic, $generated_content) {
        // Prepare post data
        $post_data = array(
            'post_title' => $generated_content['title'],
            'post_content' => $this->prepare_post_content($agent, $generated_content['content']),
            'post_status' => 'draft', // Or 'publish' if you want to auto-publish
            'post_author' => 1, // Or specify author
            'post_category' => array(), // Set categories if needed
            'meta_input' => array(
                'scb_agent_id' => $agent->id,
                'scb_topic' => $topic,
                'scb_generated' => true
            )
        );
        
        // Add meta description if provided
        if ($agent->meta_description) {
            $post_data['meta_input']['_yoast_wpseo_metadesc'] = $agent->meta_description;
        }
        
        // Add tags if provided
        if ($agent->tags) {
            $post_data['tags_input'] = $agent->tags;
        }
        
        // Create the post
        $post_id = wp_insert_post($post_data);
        
        if ($post_id && !is_wp_error($post_id)) {
            // Set featured image
            if ($agent->featured_image_id) {
                set_post_thumbnail($post_id, $agent->featured_image_id);
            }
            
            return $post_id;
        }
        
        return false;
    }
    
    /**
     * Prepare post content with CTA and gallery
     */
    private function prepare_post_content($agent, $content) {
        $final_content = $content;
        
        // Add CTA block if enabled
        if ($agent->cta_enabled) {
            $final_content .= $this->generate_cta_block($agent);
        }
        
        // Add gallery block if images exist
        if ($agent->gallery_images && !empty($agent->gallery_images)) {
            $final_content .= $this->generate_gallery_block($agent);
        }
        
        return $final_content;
    }
    
    /**
     * Generate CTA block HTML
     */
    private function generate_cta_block($agent) {
        $cta_config = $agent->get_cta_config();
        
        if (!$cta_config) {
            return '';
        }
        
        $icon_html = '';
        if ($cta_config['icon']) {
            $icon_html = '<span class="scb-cta-icon"><i class="' . esc_attr($cta_config['icon']) . '"></i></span>';
        }
        
        $cta_html = '
        <div class="scb-cta-block">
            <div class="scb-cta-content">
                ' . $icon_html . '
                <h3 class="scb-cta-heading">' . esc_html($cta_config['heading']) . '</h3>
                <p class="scb-cta-text">' . esc_html($cta_config['text']) . '</p>
                <a href="' . esc_url($cta_config['button_url']) . '" class="scb-cta-button">
                    ' . esc_html($cta_config['button_text']) . '
                </a>
            </div>
        </div>';
        
        return $cta_html;
    }
    
    /**
     * Generate gallery block HTML
     */
    private function generate_gallery_block($agent) {
        $gallery_images = $agent->get_gallery_images_urls('medium');
        
        if (empty($gallery_images)) {
            return '';
        }
        
        $gallery_html = '<div class="scb-gallery-block">';
        $gallery_html .= '<h3>Image Gallery</h3>';
        $gallery_html .= '<div class="scb-gallery-grid">';
        
        foreach ($gallery_images as $image) {
            $gallery_html .= '<div class="scb-gallery-item">';
            $gallery_html .= '<img src="' . esc_url($image['url']) . '" alt="' . esc_attr($image['alt'] ?: 'Gallery image') . '" loading="lazy" />';
            $gallery_html .= '</div>';
        }
        
        $gallery_html .= '</div></div>';
        
        return $gallery_html;
    }
    
    /**
     * Save generation record
     */
    private function save_generation_record($agent_id, $post_id, $topic, $content) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'scb_generated_content';
        
        $wpdb->insert(
            $table,
            array(
                'agent_id' => $agent_id,
                'post_id' => $post_id,
                'topic' => $topic,
                'generated_content' => serialize($content)
            )
        );
    }
}