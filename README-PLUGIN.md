# SEO Challenge Blueprint WordPress Plugin

A comprehensive WordPress plugin for automated SEO content generation with AI integration, agent management, and topic tracking.

## Features

### 🤖 AI-Powered Content Generation
- Automated content creation using AI services (OpenAI, Claude)
- Natural city mentions without overuse
- Customizable content length and frequency
- Professional, well-structured articles

### 👥 Agent Management
- Create unlimited content agents
- Configure per-agent settings (city, category, frequency)
- Upload featured images and galleries
- Custom CTA blocks with styling
- Topic management with usage tracking

### 📊 Advanced Admin Interface
- Modern, responsive admin dashboard
- Drag-and-drop media library integration
- Real-time form validation
- AJAX-powered interactions
- Professional card-based layouts

### 🎯 Content Enhancement
- Automatic CTA block insertion with custom styling
- Gallery blocks with thumbnail grids
- SEO meta descriptions
- Tag management
- Post scheduling based on frequency

### 🔒 Enterprise Features
- Topic usage tracking (prevents reuse)
- Content generation history
- Bulk operations
- Error logging and debugging
- Database cleanup utilities

## Installation

1. Upload the plugin files to `/wp-content/plugins/seo-challenge-blueprint/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure your AI API key in Settings > SEO Blueprint
4. Create your first agent and start generating content!

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- cURL, JSON, and mbstring PHP extensions

## Configuration

### AI Service Setup
1. Go to **SEO Blueprint > Settings**
2. Choose your AI service (OpenAI or Claude)
3. Enter your API key
4. Configure content generation preferences

### Creating Agents
1. Navigate to **SEO Blueprint > Add Agent**
2. Fill in agent details:
   - **Title**: Descriptive name for your agent
   - **City**: Location for natural mentions
   - **Category**: Content category/niche
   - **Frequency**: How often to generate content
   - **Topics**: List of topics to write about
   - **Images**: Featured image and gallery
   - **CTA**: Call-to-action configuration

### Topic Management
- Topics are used once per agent to avoid duplication
- Used topics are marked and cannot be reused
- Add new topics anytime via the edit agent form

## Usage

### Automatic Generation
Content is automatically generated based on each agent's frequency setting when WordPress cron runs.

### Manual Generation
Click the "Generate Now" button on any agent card to create content immediately.

### Content Structure
Generated posts include:
1. AI-generated article content
2. Natural city mentions (configurable frequency)
3. Custom CTA block (if enabled)
4. Gallery thumbnails (if images provided)

## Customization

### CTA Blocks
- Fully customizable heading, text, and button
- Support for Font Awesome icons
- Responsive design with hover effects
- Gradient backgrounds and rounded corners

### Gallery Blocks
- Automatic thumbnail grid layout
- Hover effects and animations
- Responsive design for all devices
- Lightbox support (optional)

### Styling
- Admin CSS: `/assets/css/admin.css`
- Frontend CSS: `/assets/css/frontend.css`
- JavaScript: `/assets/js/admin.js`

## Developer Information

### Database Tables
- `wp_scb_agents` - Agent configurations
- `wp_scb_topic_usage` - Topic usage tracking
- `wp_scb_generated_content` - Generation history

### Hooks and Filters
```php
// Scheduled content generation
do_action('scb_generate_content');

// Before agent save
apply_filters('scb_before_save_agent', $agent_data);

// After content generation
do_action('scb_after_content_generated', $post_id, $agent_id);
```

### Helper Functions
```php
scb_is_installed()              // Check if plugin is installed
scb_agent_has_unused_topics()   // Check for available topics
scb_get_stats()                 // Get generation statistics
scb_validate_agent_data()       // Validate agent data
```

## Troubleshooting

### Common Issues

**No content generated**
- Check AI API key configuration
- Ensure agent has unused topics
- Verify WordPress cron is working

**Topics not saving**
- Check for JavaScript errors
- Ensure proper form submission
- Verify database permissions

**Images not loading**
- Check media library permissions
- Verify image attachment IDs
- Clear browser cache

### Debug Mode
Enable WordPress debug mode for detailed error logging:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Support

For support and documentation, please visit the plugin repository or contact the development team.

## License

This plugin is licensed under the GPL v2 or later.

## Changelog

### Version 1.0.0
- Initial release
- Complete agent management system
- AI content generation integration
- Advanced admin interface
- Topic usage tracking
- CTA and gallery blocks
- Responsive design