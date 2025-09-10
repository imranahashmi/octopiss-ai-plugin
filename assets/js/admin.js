/**
 * SEO Challenge Blueprint Admin JavaScript
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        SCBAdmin.init();
    });

    // Main admin object
    var SCBAdmin = {
        
        init: function() {
            this.initMediaUploader();
            this.initTopicsManager();
            this.initFormValidation();
            this.initAgentActions();
            this.initTooltips();
        },

        // Initialize media uploader functionality
        initMediaUploader: function() {
            var self = this;

            // Featured image uploader
            $(document).on('click', '#select-featured-image', function(e) {
                e.preventDefault();
                self.openMediaUploader(false, function(attachment) {
                    $('#featured-image-id').val(attachment.id);
                    $('#featured-image-preview').html(
                        '<img src="' + attachment.sizes.thumbnail.url + '" alt="" />' +
                        '<button type="button" class="scb-remove-image">×</button>'
                    );
                });
            });

            // Gallery images uploader
            $(document).on('click', '#select-gallery-images', function(e) {
                e.preventDefault();
                self.openMediaUploader(true, function(attachments) {
                    attachments.forEach(function(attachment) {
                        if ($('#gallery-preview [data-id="' + attachment.id + '"]').length === 0) {
                            var thumbnailUrl = attachment.sizes && attachment.sizes.thumbnail ? 
                                attachment.sizes.thumbnail.url : attachment.url;
                            
                            var imageHtml = '<div class="scb-gallery-item" data-id="' + attachment.id + '">' +
                                '<img src="' + thumbnailUrl + '" alt="" />' +
                                '<button type="button" class="scb-remove-gallery-image">×</button>' +
                                '<input type="hidden" name="gallery_images[]" value="' + attachment.id + '" />' +
                                '</div>';
                            $('#gallery-preview').append(imageHtml);
                        }
                    });
                });
            });

            // Remove featured image
            $(document).on('click', '#featured-image-preview .scb-remove-image', function() {
                $('#featured-image-id').val('');
                $('#featured-image-preview').html('<span class="scb-placeholder">No image selected</span>');
            });

            // Remove gallery image
            $(document).on('click', '.scb-remove-gallery-image', function() {
                $(this).closest('.scb-gallery-item').remove();
            });
        },

        // Open WordPress media uploader
        openMediaUploader: function(multiple, callback) {
            var mediaUploader = wp.media({
                title: multiple ? 'Select Gallery Images' : 'Select Featured Image',
                button: { text: multiple ? 'Add to Gallery' : 'Select Image' },
                multiple: multiple,
                library: { type: 'image' }
            });

            mediaUploader.on('select', function() {
                if (multiple) {
                    var attachments = mediaUploader.state().get('selection').toJSON();
                    callback(attachments);
                } else {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    callback(attachment);
                }
            });

            mediaUploader.open();
        },

        // Initialize topics management
        initTopicsManager: function() {
            var self = this;

            // Add topic on button click or Enter key
            $(document).on('click', '#add-topic', function(e) {
                e.preventDefault();
                self.addTopic();
            });

            $(document).on('keypress', '#topic-input', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    self.addTopic();
                }
            });

            // Remove topic
            $(document).on('click', '.scb-remove-topic', function() {
                $(this).closest('.scb-topic-tag').fadeOut(300, function() {
                    $(this).remove();
                });
            });
        },

        // Add a new topic
        addTopic: function() {
            var topic = $('#topic-input').val().trim();
            
            if (!topic) {
                this.showNotice('Please enter a topic', 'error');
                return;
            }

            if (this.topicExists(topic)) {
                this.showNotice('Topic already exists', 'warning');
                $('#topic-input').val('');
                return;
            }

            var topicHtml = '<span class="scb-topic-tag">' +
                '<span class="scb-topic-text">' + this.escapeHtml(topic) + '</span>' +
                '<button type="button" class="scb-remove-topic" title="Remove topic">×</button>' +
                '<input type="hidden" name="topics[]" value="' + this.escapeHtml(topic) + '" />' +
                '</span>';

            $('#topics-container').append(topicHtml);
            $('#topic-input').val('');
            
            // Animate new topic
            $('#topics-container .scb-topic-tag:last-child').hide().fadeIn(300);
        },

        // Check if topic already exists
        topicExists: function(topic) {
            var exists = false;
            $('#topics-container input[name="topics[]"]').each(function() {
                if ($(this).val().toLowerCase() === topic.toLowerCase()) {
                    exists = true;
                    return false;
                }
            });
            return exists;
        },

        // Initialize form validation
        initFormValidation: function() {
            var self = this;

            // Real-time validation
            $('#scb-agent-form input[required]').on('blur', function() {
                self.validateField($(this));
            });

            // Form submission
            $('#scb-agent-form').on('submit', function(e) {
                e.preventDefault();
                self.submitForm($(this));
            });

            // CTA toggle
            $('#cta-enabled').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#cta-fields').slideDown(300);
                } else {
                    $('#cta-fields').slideUp(300);
                }
            });
        },

        // Validate individual field
        validateField: function($field) {
            var value = $field.val().trim();
            var isValid = true;
            var message = '';

            if ($field.prop('required') && !value) {
                isValid = false;
                message = 'This field is required';
            }

            if ($field.attr('type') === 'url' && value && !this.isValidUrl(value)) {
                isValid = false;
                message = 'Please enter a valid URL';
            }

            this.showFieldValidation($field, isValid, message);
            return isValid;
        },

        // Show field validation state
        showFieldValidation: function($field, isValid, message) {
            $field.removeClass('scb-field-error scb-field-valid');
            $field.siblings('.scb-field-message').remove();

            if (!isValid) {
                $field.addClass('scb-field-error');
                $field.after('<span class="scb-field-message scb-error">' + message + '</span>');
            } else if ($field.val().trim()) {
                $field.addClass('scb-field-valid');
            }
        },

        // Submit form via AJAX
        submitForm: function($form) {
            var self = this;
            var isValid = true;

            // Validate all required fields
            $form.find('input[required]').each(function() {
                if (!self.validateField($(this))) {
                    isValid = false;
                }
            });

            // Check if at least one topic is added
            if ($('#topics-container input[name="topics[]"]').length === 0) {
                this.showNotice('Please add at least one topic', 'error');
                isValid = false;
            }

            if (!isValid) {
                this.showNotice('Please fix the errors above', 'error');
                return;
            }

            var $submitButton = $form.find('button[type="submit"]');
            var originalText = $submitButton.html();
            
            $submitButton.prop('disabled', true).html(
                '<span class="dashicons dashicons-update-alt" style="animation: spin 1s linear infinite;"></span> Saving...'
            );

            var formData = new FormData($form[0]);
            formData.append('action', 'scb_save_agent');
            formData.append('nonce', scb_ajax.nonce);

            $.ajax({
                url: scb_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        self.showNotice(response.data.message, 'success');
                        setTimeout(function() {
                            window.location.href = scb_ajax.agents_url || 'admin.php?page=scb-agents';
                        }, 1500);
                    } else {
                        self.showNotice('Error: ' + response.data, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    self.showNotice('Network error: Please try again', 'error');
                    console.error('AJAX Error:', error);
                },
                complete: function() {
                    $submitButton.prop('disabled', false).html(originalText);
                }
            });
        },

        // Initialize agent actions (delete, generate)
        initAgentActions: function() {
            var self = this;

            // Delete agent
            $(document).on('click', '.scb-delete-agent', function() {
                var $button = $(this);
                var agentId = $button.data('agent-id');
                var $card = $button.closest('.scb-agent-card');
                var agentTitle = $card.find('.scb-agent-title').text();

                if (confirm('Are you sure you want to delete "' + agentTitle + '"?\n\nThis action cannot be undone and will remove all associated data.')) {
                    self.deleteAgent(agentId, $card);
                }
            });

            // Generate content now
            $(document).on('click', '.scb-generate-now', function() {
                var $button = $(this);
                var agentId = $button.data('agent-id');
                
                self.generateContentNow(agentId, $button);
            });
        },

        // Delete agent via AJAX
        deleteAgent: function(agentId, $card) {
            var self = this;

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
                        $card.fadeOut(300, function() {
                            $(this).remove();
                            // Check if no agents left
                            if ($('.scb-agent-card').length === 0) {
                                location.reload();
                            }
                        });
                        self.showNotice('Agent deleted successfully', 'success');
                    } else {
                        self.showNotice('Error deleting agent: ' + response.data, 'error');
                    }
                },
                error: function() {
                    self.showNotice('Network error: Could not delete agent', 'error');
                }
            });
        },

        // Generate content now
        generateContentNow: function(agentId, $button) {
            var self = this;
            var originalHtml = $button.html();

            $button.prop('disabled', true).html(
                '<span class="dashicons dashicons-update-alt" style="animation: spin 1s linear infinite;"></span> Generating...'
            );

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
                        self.showNotice('Content generated successfully!', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        self.showNotice('Error generating content: ' + response.data, 'error');
                    }
                },
                error: function() {
                    self.showNotice('Network error: Could not generate content', 'error');
                },
                complete: function() {
                    $button.prop('disabled', false).html(originalHtml);
                }
            });
        },

        // Initialize tooltips
        initTooltips: function() {
            // Simple tooltip implementation
            $(document).on('mouseenter', '[title]', function() {
                var $this = $(this);
                var title = $this.attr('title');
                
                if (title) {
                    $this.attr('data-original-title', title).removeAttr('title');
                    
                    var $tooltip = $('<div class="scb-tooltip">' + title + '</div>');
                    $('body').append($tooltip);
                    
                    var offset = $this.offset();
                    $tooltip.css({
                        top: offset.top - $tooltip.outerHeight() - 5,
                        left: offset.left + ($this.outerWidth() / 2) - ($tooltip.outerWidth() / 2)
                    }).fadeIn(200);
                }
            });

            $(document).on('mouseleave', '[data-original-title]', function() {
                var $this = $(this);
                $this.attr('title', $this.attr('data-original-title')).removeAttr('data-original-title');
                $('.scb-tooltip').fadeOut(200, function() {
                    $(this).remove();
                });
            });
        },

        // Show admin notice
        showNotice: function(message, type) {
            type = type || 'info';
            
            var $notice = $('<div class="notice notice-' + type + ' is-dismissible scb-notice">' +
                '<p>' + message + '</p>' +
                '<button type="button" class="notice-dismiss"></button>' +
                '</div>');

            // Remove existing notices
            $('.scb-notice').remove();

            // Add new notice
            $('.scb-admin h1').after($notice);

            // Auto-dismiss success notices
            if (type === 'success') {
                setTimeout(function() {
                    $notice.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 3000);
            }

            // Handle dismiss button
            $notice.find('.notice-dismiss').on('click', function() {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            });

            // Scroll to notice
            $('html, body').animate({
                scrollTop: $notice.offset().top - 50
            }, 300);
        },

        // Utility: Escape HTML
        escapeHtml: function(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        },

        // Utility: Validate URL
        isValidUrl: function(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }
    };

    // Make SCBAdmin globally available
    window.SCBAdmin = SCBAdmin;

})(jQuery);

// CSS for animations and tooltips
(function() {
    var style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .scb-field-error {
            border-color: #d63638 !important;
            box-shadow: 0 0 0 1px #d63638 !important;
        }
        
        .scb-field-valid {
            border-color: #00a32a !important;
            box-shadow: 0 0 0 1px #00a32a !important;
        }
        
        .scb-field-message {
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
        
        .scb-field-message.scb-error {
            color: #d63638;
        }
        
        .scb-tooltip {
            position: absolute;
            background: #1d2327;
            color: white;
            padding: 5px 8px;
            border-radius: 3px;
            font-size: 12px;
            z-index: 10000;
            pointer-events: none;
            white-space: nowrap;
        }
        
        .scb-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #1d2327 transparent transparent transparent;
        }
        
        .scb-notice {
            margin: 15px 0 !important;
        }
    `;
    document.head.appendChild(style);
})();