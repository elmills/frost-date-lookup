<?php
/**
 * Custom Plugin Information from README.md
 * 
 * This library pulls plugin information from the README.md file
 * hosted on GitHub rather than hardcoding it in the plugin file.
 * The cache automatically resets when a plugin update is detected.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Github_Plugin_Info {
    private $plugin_slug;
    private $plugin_file;
    private $github_username;
    private $github_repository;
    private $readme_cache_time = 86400; // 24 hours in seconds
    private $readme_transient_key;
    private $version_transient_key;
    
    /**
     * Constructor
     * 
     * @param string $plugin_slug The plugin slug
     * @param string $plugin_file Full path to plugin main file
     * @param string $github_username GitHub username
     * @param string $github_repository GitHub repository name
     */
    public function __construct($plugin_slug, $plugin_file, $github_username, $github_repository) {
        $this->plugin_slug = $plugin_slug;
        $this->plugin_file = $plugin_file;
        $this->github_username = $github_username;
        $this->github_repository = $github_repository;
        $this->readme_transient_key = 'github_readme_' . $this->plugin_slug;
        $this->version_transient_key = 'github_version_' . $this->plugin_slug;
        
        // Add filter to provide custom plugin information
        add_filter('plugins_api', array($this, 'plugin_info'), 10, 3);
        
        // Check for plugin updates and clear cache if needed
        add_action('admin_init', array($this, 'check_for_updates'));
        
        // Clear cache after plugin update
        add_action('upgrader_process_complete', array($this, 'clear_cache_after_update'), 10, 2);
    }
    
    /**
     * Get plugin information from GitHub README.md
     * 
     * @param mixed $res Result object or false
     * @param string $action The type of information requested
     * @param object $args Plugin information arguments
     * @return object Plugin information
     */
    public function plugin_info($res, $action, $args) {
        // Check if this is a request for our plugin
        if ($action !== 'plugin_information' || !isset($args->slug) || $args->slug !== $this->plugin_slug) {
            return $res;
        }
        
        // Get plugin data from the plugin file
        $plugin_data = get_plugin_data($this->plugin_file);
        
        // Get README.md content from GitHub
        $readme_content = $this->get_readme_content();
        
        // Create new response object
        $res = new stdClass();
        $res->name = $plugin_data['Name'];
        $res->slug = $this->plugin_slug;
        $res->version = $plugin_data['Version'];
        $res->author = $plugin_data['Author'];
        $res->author_profile = isset($plugin_data['AuthorURI']) ? $plugin_data['AuthorURI'] : '';
        $res->requires = $this->parse_readme_value($readme_content, 'Requires at least');
        $res->tested = $this->parse_readme_value($readme_content, 'Tested up to');
        $res->last_updated = $this->parse_readme_value($readme_content, 'Last updated');
        $res->download_link = "https://github.com/{$this->github_username}/{$this->github_repository}/releases/latest/download/{$this->plugin_slug}.zip";
        
        // Parse sections from README.md
        $res->sections = array(
            'description' => $this->parse_readme_section($readme_content, 'Description'),
            'installation' => $this->parse_readme_section($readme_content, 'Installation'),
            'changelog' => $this->parse_readme_section($readme_content, 'Changelog')
        );
        
        return $res;
    }
    
    /**
     * Check for plugin updates and clear cache if needed
     */
    public function check_for_updates() {
        // Get current plugin data
        $plugin_data = get_plugin_data($this->plugin_file);
        $current_version = $plugin_data['Version'];
        
        // Get stored version
        $stored_version = get_transient($this->version_transient_key);
        
        // If version has changed or doesn't exist, update stored version and clear README cache
        if ($stored_version !== $current_version) {
            // Update stored version
            set_transient($this->version_transient_key, $current_version, $this->readme_cache_time);
            
            // Clear README cache to force refresh
            delete_transient($this->readme_transient_key);
        }
    }
    
    /**
     * Clear cache after plugin update
     * 
     * @param WP_Upgrader $upgrader WP_Upgrader instance
     * @param array $options Upgrade options
     */
    public function clear_cache_after_update($upgrader, $options) {
        // Check if our plugin was updated
        if ($options['action'] === 'update' && $options['type'] === 'plugin') {
            // Check if our plugin is in the list of updated plugins
            if (isset($options['plugins']) && is_array($options['plugins'])) {
                $plugin_base = plugin_basename($this->plugin_file);
                
                foreach ($options['plugins'] as $plugin) {
                    if ($plugin === $plugin_base) {
                        // Our plugin was updated, clear the cache
                        delete_transient($this->readme_transient_key);
                        delete_transient($this->version_transient_key);
                        break;
                    }
                }
            }
        }
    }
    
    /**
     * Get README.md content from GitHub
     * 
     * @return string README.md content
     */
    private function get_readme_content() {
        // Check if we have a cached version
        $readme_content = get_transient($this->readme_transient_key);
        
        if (false === $readme_content) {
            // Construct the GitHub raw URL for README.md
            $readme_url = "https://raw.githubusercontent.com/{$this->github_username}/{$this->github_repository}/main/README.md";
            
            // Get README.md content
            $response = wp_remote_get($readme_url);
            
            if (!is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response)) {
                $readme_content = wp_remote_retrieve_body($response);
                
                // Cache the README.md content
                set_transient($this->readme_transient_key, $readme_content, $this->readme_cache_time);
            } else {
                // If failed, return empty string
                $readme_content = '';
            }
        }
        
        return $readme_content;
    }
    
    /**
     * Parse a value from README.md
     * 
     * @param string $content README.md content
     * @param string $field Field to find
     * @return string Field value or empty string if not found
     */
    private function parse_readme_value($content, $field) {
        $pattern = "/(?:^|\n)[#\s]*{$field}[:]*\s*(.*?)(?:\n|$)/i";
        if (preg_match($pattern, $content, $matches)) {
            return trim($matches[1]);
        }
        
        // Default values for common fields
        switch ($field) {
            case 'Requires at least':
                return '6.0';
            case 'Tested up to':
                return get_bloginfo('version');
            case 'Last updated':
                return date('Y-m-d');
            default:
                return '';
        }
    }
    
    /**
     * Parse a section from README.md
     * 
     * @param string $content README.md content
     * @param string $section Section name to find
     * @return string Section content in HTML or empty string if not found
     */
    private function parse_readme_section($content, $section) {
        // Try to find section using markdown headings (## Section)
        $pattern = "/(?:^|\n)#{1,2}\s*{$section}\s*(?:\n)(.*?)(?:\n#{1,2}|\Z)/is";
        
        if (preg_match($pattern, $content, $matches)) {
            // Convert markdown to HTML (basic conversion)
            $section_content = $matches[1];
            
            // Convert markdown lists to HTML lists
            $section_content = preg_replace('/^\s*[\*\-]\s*(.*?)$/m', '<li>$1</li>', $section_content);
            $section_content = preg_replace('/(<li>.*?<\/li>(\n|$))+/', '<ul>$0</ul>', $section_content);
            
            // Convert markdown headings to HTML headings
            $section_content = preg_replace('/^#{3}\s*(.*?)$/m', '<h3>$1</h3>', $section_content);
            $section_content = preg_replace('/^#{4}\s*(.*?)$/m', '<h4>$1</h4>', $section_content);
            
            // Convert markdown bold to HTML bold
            $section_content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $section_content);
            
            // Convert markdown italic to HTML italic
            $section_content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $section_content);
            
            // Convert markdown links to HTML links
            $section_content = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2">$1</a>', $section_content);
            
            // Convert markdown code to HTML code
            $section_content = preg_replace('/`(.*?)`/', '<code>$1</code>', $section_content);
            
            // Convert double line breaks to paragraphs
            $section_content = '<p>' . str_replace("\n\n", '</p><p>', $section_content) . '</p>';
            
            // Remove single line breaks
            $section_content = str_replace("\n", ' ', $section_content);
            
            return trim($section_content);
        }
        
        // Default values for common sections
        switch ($section) {
            case 'Description':
                $plugin_data = get_plugin_data($this->plugin_file);
                return '<p>' . $plugin_data['Description'] . '</p>';
            case 'Installation':
                return '<p>Install the plugin and activate it.</p>';
            case 'Changelog':
                return '<h4>Current Version</h4><ul><li>See GitHub repository for latest changes</li></ul>';
            default:
                return '';
        }
    }
}