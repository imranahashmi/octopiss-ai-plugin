<?php
/**
 * Settings view
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['submit']) && check_admin_referer('scb_settings_nonce')) {
    $settings = array(
        'ai_service' => sanitize_text_field($_POST['ai_service']),
        'api_key' => sanitize_text_field($_POST['api_key']),
        'default_post_status' => sanitize_text_field($_POST['default_post_status']),
        'enable_auto_generation' => isset($_POST['enable_auto_generation']),
        'content_length' => intval($_POST['content_length']),
        'city_mention_frequency' => intval($_POST['city_mention_frequency'])
    );
    
    update_option('scb_settings', $settings);
    echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
}

// Get current settings
$settings = get_option('scb_settings', array(
    'ai_service' => 'openai',
    'api_key' => '',
    'default_post_status' => 'draft',
    'enable_auto_generation' => true,
    'content_length' => 1000,
    'city_mention_frequency' => 3
));
?>

<div class="wrap scb-admin">
    <h1>SEO Challenge Blueprint - Settings</h1>
    
    <form method="post" action="" class="scb-settings-form">
        <?php wp_nonce_field('scb_settings_nonce'); ?>
        
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="ai_service">AI Service</label>
                    </th>
                    <td>
                        <select id="ai_service" name="ai_service">
                            <option value="openai" <?php selected($settings['ai_service'], 'openai'); ?>>OpenAI GPT</option>
                            <option value="claude" <?php selected($settings['ai_service'], 'claude'); ?>>Anthropic Claude</option>
                            <option value="custom" <?php selected($settings['ai_service'], 'custom'); ?>>Custom API</option>
                        </select>
                        <p class="description">Choose your preferred AI service for content generation.</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="api_key">API Key</label>
                    </th>
                    <td>
                        <input type="password" id="api_key" name="api_key" value="<?php echo esc_attr($settings['api_key']); ?>" class="regular-text" />
                        <p class="description">Your AI service API key. This will be stored securely.</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="default_post_status">Default Post Status</label>
                    </th>
                    <td>
                        <select id="default_post_status" name="default_post_status">
                            <option value="draft" <?php selected($settings['default_post_status'], 'draft'); ?>>Draft</option>
                            <option value="publish" <?php selected($settings['default_post_status'], 'publish'); ?>>Published</option>
                            <option value="pending" <?php selected($settings['default_post_status'], 'pending'); ?>>Pending Review</option>
                        </select>
                        <p class="description">Default status for generated posts.</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="enable_auto_generation">Auto Generation</label>
                    </th>
                    <td>
                        <label class="scb-checkbox-label">
                            <input type="checkbox" id="enable_auto_generation" name="enable_auto_generation" value="1" <?php checked($settings['enable_auto_generation']); ?> />
                            <span class="scb-checkbox"></span>
                            Enable automatic content generation based on agent frequency
                        </label>
                        <p class="description">When enabled, content will be generated automatically according to each agent's frequency setting.</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="content_length">Content Length</label>
                    </th>
                    <td>
                        <input type="number" id="content_length" name="content_length" value="<?php echo esc_attr($settings['content_length']); ?>" min="500" max="3000" step="100" />
                        <p class="description">Target word count for generated content (500-3000 words).</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="city_mention_frequency">City Mention Frequency</label>
                    </th>
                    <td>
                        <input type="number" id="city_mention_frequency" name="city_mention_frequency" value="<?php echo esc_attr($settings['city_mention_frequency']); ?>" min="1" max="10" />
                        <p class="description">How many times to naturally mention the city in generated content (1-10).</p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <h2>Content Generation Guidelines</h2>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <td colspan="2">
                        <div class="scb-guidelines">
                            <h4>City Mention Strategy</h4>
                            <p>The AI will use natural variations when mentioning cities:</p>
                            <ul>
                                <li><strong>Good:</strong> "local businesses in [City]", "our [City] clients", "based in [City]"</li>
                                <li><strong>Avoid:</strong> Adding city name to post titles or forcing it into every heading</li>
                            </ul>
                            
                            <h4>Content Structure</h4>
                            <p>Generated content will automatically include:</p>
                            <ul>
                                <li>Well-structured headings and subheadings</li>
                                <li>Natural city mentions throughout the content</li>
                                <li>CTA block (if enabled for the agent)</li>
                                <li>Gallery block with thumbnail images</li>
                            </ul>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <?php submit_button('Save Settings'); ?>
    </form>
    
    <div class="scb-settings-info">
        <h2>System Information</h2>
        <table class="widefat striped">
            <tbody>
                <tr>
                    <td><strong>Plugin Version</strong></td>
                    <td><?php echo SCB_PLUGIN_VERSION; ?></td>
                </tr>
                <tr>
                    <td><strong>Database Version</strong></td>
                    <td><?php echo get_option('scb_db_version', 'Not installed'); ?></td>
                </tr>
                <tr>
                    <td><strong>WordPress Version</strong></td>
                    <td><?php echo get_bloginfo('version'); ?></td>
                </tr>
                <tr>
                    <td><strong>PHP Version</strong></td>
                    <td><?php echo PHP_VERSION; ?></td>
                </tr>
                <tr>
                    <td><strong>Total Agents</strong></td>
                    <td><?php echo count(SCB_Database::get_agents('all')); ?></td>
                </tr>
                <tr>
                    <td><strong>Active Agents</strong></td>
                    <td><?php echo count(SCB_Database::get_agents('active')); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>