<?php
/**
 * Agents listing view
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap scb-admin">
    <h1>SEO Challenge Blueprint - Agents</h1>
    
    <div class="scb-header-actions">
        <a href="<?php echo admin_url('admin.php?page=scb-add-agent'); ?>" class="button button-primary">
            <span class="dashicons dashicons-plus-alt"></span> Add New Agent
        </a>
    </div>
    
    <?php if (empty($agents)): ?>
        <div class="scb-empty-state">
            <div class="scb-empty-icon">
                <span class="dashicons dashicons-megaphone"></span>
            </div>
            <h2>No agents found</h2>
            <p>Create your first SEO agent to start generating content automatically.</p>
            <a href="<?php echo admin_url('admin.php?page=scb-add-agent'); ?>" class="button button-primary button-large">
                Create Your First Agent
            </a>
        </div>
    <?php else: ?>
        <div class="scb-agents-grid">
            <?php foreach ($agents as $agent_data): 
                $agent = new SCB_Agent($agent_data);
                $summary = $agent->get_summary();
            ?>
                <div class="scb-agent-card" data-agent-id="<?php echo $agent->id; ?>">
                    <div class="scb-agent-header">
                        <?php if ($summary['featured_image_url']): ?>
                            <div class="scb-agent-thumbnail">
                                <img src="<?php echo esc_url($summary['featured_image_url']); ?>" alt="" />
                            </div>
                        <?php else: ?>
                            <div class="scb-agent-thumbnail scb-placeholder">
                                <span class="dashicons dashicons-format-image"></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="scb-agent-info">
                            <h3 class="scb-agent-title"><?php echo esc_html($agent->title); ?></h3>
                            <p class="scb-agent-meta">
                                <span class="scb-city"><?php echo esc_html($agent->city); ?></span>
                                <span class="scb-category"><?php echo esc_html($agent->category); ?></span>
                            </p>
                        </div>
                        
                        <div class="scb-agent-status">
                            <span class="scb-status scb-status-<?php echo esc_attr($agent->status); ?>">
                                <?php echo esc_html(ucfirst($agent->status)); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="scb-agent-stats">
                        <div class="scb-stat">
                            <span class="scb-stat-label">Topics Remaining</span>
                            <span class="scb-stat-value"><?php echo $summary['topics_remaining']; ?>/<?php echo $summary['topics_total']; ?></span>
                        </div>
                        <div class="scb-stat">
                            <span class="scb-stat-label">Frequency</span>
                            <span class="scb-stat-value"><?php echo esc_html($agent->frequency); ?></span>
                        </div>
                    </div>
                    
                    <div class="scb-agent-actions">
                        <a href="<?php echo admin_url('admin.php?page=scb-agents&action=edit&id=' . $agent->id); ?>" class="button button-secondary">
                            <span class="dashicons dashicons-edit"></span> Edit
                        </a>
                        <button class="button button-link-delete scb-delete-agent" data-agent-id="<?php echo $agent->id; ?>">
                            <span class="dashicons dashicons-trash"></span> Delete
                        </button>
                        <button class="button button-primary scb-generate-now" data-agent-id="<?php echo $agent->id; ?>">
                            <span class="dashicons dashicons-admin-post"></span> Generate Now
                        </button>
                    </div>
                    
                    <div class="scb-agent-footer">
                        <small>Created: <?php echo date('M j, Y', strtotime($agent->created_at)); ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Delete agent handler
    $('.scb-delete-agent').on('click', function() {
        var agentId = $(this).data('agent-id');
        var agentCard = $(this).closest('.scb-agent-card');
        
        if (confirm('Are you sure you want to delete this agent? This action cannot be undone.')) {
            $.ajax({
                url: scb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'scb_delete_agent',
                    agent_id: agentId,
                    nonce: scb_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        agentCard.fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        alert('Error deleting agent: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error deleting agent. Please try again.');
                }
            });
        }
    });
    
    // Generate content now handler
    $('.scb-generate-now').on('click', function() {
        var agentId = $(this).data('agent-id');
        var button = $(this);
        
        button.prop('disabled', true).text('Generating...');
        
        $.ajax({
            url: scb_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'scb_generate_content_now',
                agent_id: agentId,
                nonce: scb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Content generated successfully!');
                    location.reload(); // Reload to update topics count
                } else {
                    alert('Error generating content: ' + response.data);
                }
            },
            error: function() {
                alert('Error generating content. Please try again.');
            },
            complete: function() {
                button.prop('disabled', false).html('<span class="dashicons dashicons-admin-post"></span> Generate Now');
            }
        });
    });
});
</script>