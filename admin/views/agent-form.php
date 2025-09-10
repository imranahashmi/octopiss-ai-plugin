<?php
/**
 * Agent form view (add/edit)
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$is_edit = isset($agent) && $agent;
$page_title = $is_edit ? 'Edit Agent' : 'Add New Agent';
$button_text = $is_edit ? 'Update Agent' : 'Create Agent';

// Default values
$default_agent = (object) array(
    'id' => '',
    'title' => '',
    'city' => '',
    'category' => '',
    'frequency' => 'daily',
    'topics' => array(),
    'used_topics' => array(),
    'featured_image_id' => '',
    'gallery_images' => array(),
    'meta_description' => '',
    'tags' => array(),
    'cta_enabled' => false,
    'cta_heading' => '',
    'cta_text' => '',
    'cta_button_text' => '',
    'cta_button_url' => '',
    'cta_icon' => '',
    'status' => 'active'
);

if (!$is_edit) {
    $agent = $default_agent;
}
?>

<div class="wrap scb-admin">
    <h1><?php echo esc_html($page_title); ?></h1>
    
    <form id="scb-agent-form" class="scb-form">
        <?php if ($is_edit): ?>
            <input type="hidden" name="agent_id" value="<?php echo esc_attr($agent->id); ?>" />
        <?php endif; ?>
        
        <div class="scb-form-sections">
            <!-- Basic Information -->
            <div class="scb-form-section">
                <h2 class="scb-section-title">
                    <span class="dashicons dashicons-admin-generic"></span>
                    Basic Information
                </h2>
                
                <div class="scb-form-grid">
                    <div class="scb-form-field scb-field-full">
                        <label for="agent-title">Agent Title <span class="required">*</span></label>
                        <input type="text" id="agent-title" name="title" value="<?php echo esc_attr($agent->title); ?>" required />
                        <p class="description">Give your agent a descriptive name (e.g., "Health & Wellness - Miami")</p>
                    </div>
                    
                    <div class="scb-form-field">
                        <label for="agent-city">City <span class="required">*</span></label>
                        <input type="text" id="agent-city" name="city" value="<?php echo esc_attr($agent->city); ?>" required />
                        <p class="description">City name for local SEO mentions</p>
                    </div>
                    
                    <div class="scb-form-field">
                        <label for="agent-category">Category <span class="required">*</span></label>
                        <input type="text" id="agent-category" name="category" value="<?php echo esc_attr($agent->category); ?>" required />
                        <p class="description">Content category (e.g., "Health", "Finance", "Technology")</p>
                    </div>
                    
                    <div class="scb-form-field">
                        <label for="agent-frequency">Generation Frequency</label>
                        <select id="agent-frequency" name="frequency">
                            <option value="daily" <?php selected($agent->frequency, 'daily'); ?>>Daily</option>
                            <option value="weekly" <?php selected($agent->frequency, 'weekly'); ?>>Weekly</option>
                            <option value="monthly" <?php selected($agent->frequency, 'monthly'); ?>>Monthly</option>
                        </select>
                    </div>
                    
                    <div class="scb-form-field">
                        <label for="agent-status">Status</label>
                        <select id="agent-status" name="status">
                            <option value="active" <?php selected($agent->status, 'active'); ?>>Active</option>
                            <option value="inactive" <?php selected($agent->status, 'inactive'); ?>>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Topics -->
            <div class="scb-form-section">
                <h2 class="scb-section-title">
                    <span class="dashicons dashicons-list-view"></span>
                    Content Topics
                </h2>
                
                <div class="scb-topics-container">
                    <div class="scb-topics-input">
                        <label for="topic-input">Add Topics</label>
                        <div class="scb-topic-input-group">
                            <input type="text" id="topic-input" placeholder="Enter a topic and press Enter" />
                            <button type="button" id="add-topic" class="button">Add Topic</button>
                        </div>
                        <p class="description">Add topics one at a time. Used topics will be marked and cannot be reused.</p>
                    </div>
                    
                    <div class="scb-topics-list">
                        <h4>Current Topics</h4>
                        <div id="topics-container" class="scb-topics-tags">
                            <?php if (!empty($agent->topics)): ?>
                                <?php foreach ($agent->topics as $topic): ?>
                                    <?php 
                                    $is_used = in_array($topic, $agent->used_topics ?: array());
                                    $class = $is_used ? 'scb-topic-tag scb-topic-used' : 'scb-topic-tag';
                                    ?>
                                    <span class="<?php echo $class; ?>">
                                        <?php echo esc_html($topic); ?>
                                        <?php if ($is_used): ?>
                                            <span class="scb-used-indicator" title="Topic already used">✓</span>
                                        <?php else: ?>
                                            <button type="button" class="scb-remove-topic" title="Remove topic">×</button>
                                        <?php endif; ?>
                                        <input type="hidden" name="topics[]" value="<?php echo esc_attr($topic); ?>" />
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Media & Images -->
            <div class="scb-form-section">
                <h2 class="scb-section-title">
                    <span class="dashicons dashicons-format-gallery"></span>
                    Media & Images
                </h2>
                
                <div class="scb-media-container">
                    <div class="scb-media-field">
                        <label>Featured Image</label>
                        <div class="scb-featured-image">
                            <div id="featured-image-preview" class="scb-image-preview">
                                <?php if ($agent->featured_image_id): ?>
                                    <?php $featured_image = wp_get_attachment_image_src($agent->featured_image_id, 'thumbnail'); ?>
                                    <img src="<?php echo esc_url($featured_image[0]); ?>" alt="" />
                                    <button type="button" class="scb-remove-image">×</button>
                                <?php else: ?>
                                    <span class="scb-placeholder">No image selected</span>
                                <?php endif; ?>
                            </div>
                            <button type="button" id="select-featured-image" class="button">Select Featured Image</button>
                            <input type="hidden" id="featured-image-id" name="featured_image_id" value="<?php echo esc_attr($agent->featured_image_id); ?>" />
                        </div>
                    </div>
                    
                    <div class="scb-media-field">
                        <label>Gallery Images</label>
                        <div class="scb-gallery-images">
                            <div id="gallery-preview" class="scb-gallery-preview">
                                <?php if (!empty($agent->gallery_images)): ?>
                                    <?php foreach ($agent->gallery_images as $image_id): ?>
                                        <?php $image = wp_get_attachment_image_src($image_id, 'thumbnail'); ?>
                                        <?php if ($image): ?>
                                            <div class="scb-gallery-item" data-id="<?php echo esc_attr($image_id); ?>">
                                                <img src="<?php echo esc_url($image[0]); ?>" alt="" />
                                                <button type="button" class="scb-remove-gallery-image">×</button>
                                                <input type="hidden" name="gallery_images[]" value="<?php echo esc_attr($image_id); ?>" />
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <button type="button" id="select-gallery-images" class="button">Add Gallery Images</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SEO & Meta -->
            <div class="scb-form-section">
                <h2 class="scb-section-title">
                    <span class="dashicons dashicons-search"></span>
                    SEO & Meta Information
                </h2>
                
                <div class="scb-form-field scb-field-full">
                    <label for="meta-description">Meta Description</label>
                    <textarea id="meta-description" name="meta_description" rows="3" maxlength="160"><?php echo esc_textarea($agent->meta_description); ?></textarea>
                    <p class="description">SEO meta description (max 160 characters)</p>
                </div>
                
                <div class="scb-form-field scb-field-full">
                    <label for="agent-tags">Tags</label>
                    <input type="text" id="agent-tags" name="tags" value="<?php echo esc_attr(is_array($agent->tags) ? implode(', ', $agent->tags) : $agent->tags); ?>" />
                    <p class="description">Comma-separated tags for generated posts</p>
                </div>
            </div>
            
            <!-- Call-to-Action -->
            <div class="scb-form-section">
                <h2 class="scb-section-title">
                    <span class="dashicons dashicons-megaphone"></span>
                    Call-to-Action Settings
                </h2>
                
                <div class="scb-cta-enable">
                    <label class="scb-checkbox-label">
                        <input type="checkbox" id="cta-enabled" name="cta_enabled" value="1" <?php checked($agent->cta_enabled); ?> />
                        <span class="scb-checkbox"></span>
                        Enable CTA block in generated content
                    </label>
                </div>
                
                <div id="cta-fields" class="scb-cta-fields" <?php if (!$agent->cta_enabled) echo 'style="display: none;"'; ?>>
                    <div class="scb-form-grid">
                        <div class="scb-form-field">
                            <label for="cta-heading">CTA Heading</label>
                            <input type="text" id="cta-heading" name="cta_heading" value="<?php echo esc_attr($agent->cta_heading); ?>" />
                        </div>
                        
                        <div class="scb-form-field">
                            <label for="cta-icon">Icon Class</label>
                            <input type="text" id="cta-icon" name="cta_icon" value="<?php echo esc_attr($agent->cta_icon); ?>" placeholder="e.g., fas fa-phone" />
                        </div>
                        
                        <div class="scb-form-field scb-field-full">
                            <label for="cta-text">CTA Text</label>
                            <textarea id="cta-text" name="cta_text" rows="2"><?php echo esc_textarea($agent->cta_text); ?></textarea>
                        </div>
                        
                        <div class="scb-form-field">
                            <label for="cta-button-text">Button Text</label>
                            <input type="text" id="cta-button-text" name="cta_button_text" value="<?php echo esc_attr($agent->cta_button_text); ?>" />
                        </div>
                        
                        <div class="scb-form-field">
                            <label for="cta-button-url">Button URL</label>
                            <input type="url" id="cta-button-url" name="cta_button_url" value="<?php echo esc_attr($agent->cta_button_url); ?>" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="scb-form-actions">
            <button type="submit" class="button button-primary button-large">
                <span class="dashicons dashicons-yes"></span>
                <?php echo esc_html($button_text); ?>
            </button>
            <a href="<?php echo admin_url('admin.php?page=scb-agents'); ?>" class="button button-secondary button-large">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // CTA enable/disable toggle
    $('#cta-enabled').on('change', function() {
        if ($(this).is(':checked')) {
            $('#cta-fields').slideDown();
        } else {
            $('#cta-fields').slideUp();
        }
    });
    
    // Topics management
    $('#add-topic, #topic-input').on('click keypress', function(e) {
        if (e.type === 'click' || e.which === 13) {
            e.preventDefault();
            var topic = $('#topic-input').val().trim();
            if (topic && !topicExists(topic)) {
                addTopic(topic);
                $('#topic-input').val('');
            }
        }
    });
    
    function topicExists(topic) {
        var exists = false;
        $('#topics-container input[name="topics[]"]').each(function() {
            if ($(this).val() === topic) {
                exists = true;
                return false;
            }
        });
        return exists;
    }
    
    function addTopic(topic) {
        var topicHtml = '<span class="scb-topic-tag">' +
            '<span class="scb-topic-text">' + topic + '</span>' +
            '<button type="button" class="scb-remove-topic" title="Remove topic">×</button>' +
            '<input type="hidden" name="topics[]" value="' + topic + '" />' +
            '</span>';
        $('#topics-container').append(topicHtml);
    }
    
    // Remove topic
    $(document).on('click', '.scb-remove-topic', function() {
        $(this).closest('.scb-topic-tag').remove();
    });
    
    // Featured image selection
    $('#select-featured-image').on('click', function() {
        var mediaUploader = wp.media({
            title: 'Select Featured Image',
            button: { text: 'Select Image' },
            multiple: false,
            library: { type: 'image' }
        });
        
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#featured-image-id').val(attachment.id);
            $('#featured-image-preview').html(
                '<img src="' + attachment.sizes.thumbnail.url + '" alt="" />' +
                '<button type="button" class="scb-remove-image">×</button>'
            );
        });
        
        mediaUploader.open();
    });
    
    // Remove featured image
    $(document).on('click', '#featured-image-preview .scb-remove-image', function() {
        $('#featured-image-id').val('');
        $('#featured-image-preview').html('<span class="scb-placeholder">No image selected</span>');
    });
    
    // Gallery images selection
    $('#select-gallery-images').on('click', function() {
        var mediaUploader = wp.media({
            title: 'Select Gallery Images',
            button: { text: 'Add to Gallery' },
            multiple: true,
            library: { type: 'image' }
        });
        
        mediaUploader.on('select', function() {
            var attachments = mediaUploader.state().get('selection').toJSON();
            
            attachments.forEach(function(attachment) {
                // Check if image is already in gallery
                if ($('#gallery-preview [data-id="' + attachment.id + '"]').length === 0) {
                    var imageHtml = '<div class="scb-gallery-item" data-id="' + attachment.id + '">' +
                        '<img src="' + attachment.sizes.thumbnail.url + '" alt="" />' +
                        '<button type="button" class="scb-remove-gallery-image">×</button>' +
                        '<input type="hidden" name="gallery_images[]" value="' + attachment.id + '" />' +
                        '</div>';
                    $('#gallery-preview').append(imageHtml);
                }
            });
        });
        
        mediaUploader.open();
    });
    
    // Remove gallery image
    $(document).on('click', '.scb-remove-gallery-image', function() {
        $(this).closest('.scb-gallery-item').remove();
    });
    
    // Form submission
    $('#scb-agent-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'scb_save_agent');
        formData.append('nonce', scb_ajax.nonce);
        
        var submitButton = $(this).find('button[type="submit"]');
        submitButton.prop('disabled', true).text('Saving...');
        
        $.ajax({
            url: scb_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    window.location.href = '<?php echo admin_url("admin.php?page=scb-agents"); ?>';
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('Error saving agent. Please try again.');
            },
            complete: function() {
                submitButton.prop('disabled', false).html('<?php echo esc_js($button_text); ?>');
            }
        });
    });
});
</script>