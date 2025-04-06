<?php
/**
 * GitHub README.md to WordPress readme.txt Converter
 *
 * @package ReadmeConverter
 * @version 1.0.25
 */

class GitHub_To_WordPress_Readme_Converter {
    /**
     * The content of the README.md file.
     *
     * @var string
     */
    private $markdown_content;
    
    /**
     * The plugin data extracted from the README.md file.
     *
     * @var array
     */
    private $plugin_data;
    
    /**
     * Constructor.
     *
     * @param string $file_path Path to the README.md file.
     */
    public function __construct( $file_path ) {
        if ( file_exists( $file_path ) ) {
            $this->markdown_content = file_get_contents( $file_path );
            $this->parse_plugin_data();
        } else {
            throw new Exception( 'README.md file not found at: ' . $file_path );
        }
    }
    
    /**
     * Parse plugin data from the README.md file.
     */
    private function parse_plugin_data() {
        $this->plugin_data = array(
            'name'              => '',
            'version'           => '',
            'requires'          => '',
            'tested'            => '',
            'requires_php'      => '',
            'author'            => '',
            'author_uri'        => '',
            'contributors'      => array(),
            'license'           => '',
            'license_uri'       => '',
            'tags'              => array(),
            'description'       => '',
            'short_description' => '',
            'sections'          => array(),
            'changelog'         => array(),
        );
        
        // Extract plugin name from heading
        if ( preg_match( '/^# (.+)$/m', $this->markdown_content, $matches ) ) {
            $this->plugin_data['name'] = trim( $matches[1] );
        }
        
        // Extract metadata from list
        $metadata_pattern = '/- (Plugin Name|Version|Requires at least|Requires PHP|Tested up to|Author|Author URI|License|License URI): (.+)$/m';
        preg_match_all( $metadata_pattern, $this->markdown_content, $metadata_matches, PREG_SET_ORDER );
        
        foreach ( $metadata_matches as $match ) {
            $key = strtolower( str_replace( ' ', '_', str_replace( 'Plugin Name', 'name', $match[1] ) ) );
            $value = trim( $match[2] );
            $this->plugin_data[ $key ] = $value;
        }
        
        // Add contributor from author
        if ( ! empty( $this->plugin_data['author'] ) ) {
            $author_username = $this->get_author_username();
            $this->plugin_data['contributors'][] = $author_username;
        }
        
        // Extract description
        if ( preg_match( '/## Description\s+(.+?)(?=##|\z)/s', $this->markdown_content, $matches ) ) {
            $description = trim( $matches[1] );
            $this->plugin_data['description'] = $description;
            
            // First paragraph as short description
            $paragraphs = explode( "\n\n", $description );
            $this->plugin_data['short_description'] = trim( $paragraphs[0] );
        }
        
        // Extract sections
        $section_pattern = '/## ([^\n]+)\s+(.+?)(?=## |\z)/s';
        preg_match_all( $section_pattern, $this->markdown_content, $section_matches, PREG_SET_ORDER );
        
        foreach ( $section_matches as $match ) {
            $section_name = trim( $match[1] );
            $section_content = trim( $match[2] );
            
            if ( $section_name !== 'Changelog' ) {
                $this->plugin_data['sections'][ $section_name ] = $section_content;
            }
        }
        
        // Extract changelog
        if ( preg_match( '/## Changelog\s+(.+?)(?=##|\z)/s', $this->markdown_content, $matches ) ) {
            $changelog_content = trim( $matches[1] );
            
            // Extract individual releases
            $release_pattern = '/### \[?([0-9.]+)\]?\s*\n(.*?)(?=### \[?[0-9.]+\]?|\z)/s';
            preg_match_all( $release_pattern, $changelog_content, $release_matches, PREG_SET_ORDER );
            
            foreach ( $release_matches as $release ) {
                $version = trim( $release[1] );
                $changes_content = trim( $release[2] );
                
                // Parse changes by type
                $changes = $this->parse_changes_by_type($changes_content, $version);
                
                $this->plugin_data['changelog'][ $version ] = $changes;
            }
        }
        
        // Extract tags from the description
        $this->extract_tags_from_description();
    }
    
    /**
     * Parse changes by type (Added, Fixed, Changed, etc)
     * 
     * @param string $changes_content Raw changelog content
     * @param string $version Version number
     * @return array Organized changes by type
     */
    private function parse_changes_by_type($changes_content, $version) {
        $changes = array();
        
        // Look for change type headers (#### Added, #### Fixed, etc)
        $type_pattern = '/#### (Added|Changed|Deprecated|Removed|Fixed|Security|Improved)\s*\n(.*?)(?=#### |$)/s';
        preg_match_all( $type_pattern, $changes_content, $type_matches, PREG_SET_ORDER );
        
        if (!empty($type_matches)) {
            // Process structured changes with type headers
            foreach ($type_matches as $type_match) {
                $type = trim($type_match[1]);
                $type_content = trim($type_match[2]);
                
                // Extract bullet points
                $changes[$type] = $this->extract_bullet_points($type_content);
            }
        } else {
            // If no structured type headers found, process as general changes
            $changes['General'] = $this->extract_bullet_points($changes_content);
        }
        
        return $changes;
    }
    
    /**
     * Extract bullet points from content
     * 
     * @param string $content Content with bullet points
     * @return array Array of bullet points
     */
    private function extract_bullet_points($content) {
        $bullet_points = array();
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^[-*]\s+(.+)$/', $line, $matches)) {
                $bullet_points[] = trim($matches[1]);
            }
        }
        
        // If no bullet points found, use the entire content as a single point
        if (empty($bullet_points) && !empty(trim($content))) {
            $bullet_points[] = trim($content);
        }
        
        return $bullet_points;
    }
    
    /**
     * Extract tags from the plugin description
     */
    private function extract_tags_from_description() {
        $common_tags = array(
            'frost' => true, // Always include "frost" since it's in the plugin name
            'gardening' => false,
            'farming' => false,
            'weather' => false,
            'planting' => false,
            'seasonal' => false,
            'climate' => false,
            'agriculture' => false,
            'zipcode' => false,
            'noaa' => false,
            'garden' => false,
        );
        
        $description_lower = strtolower($this->plugin_data['description']);
        
        foreach ($common_tags as $tag => $default) {
            if ($default || strpos($description_lower, $tag) !== false) {
                $this->plugin_data['tags'][] = $tag;
            }
        }
        
        // Add some default tags if none were found
        if (empty($this->plugin_data['tags'])) {
            $this->plugin_data['tags'] = array('frost', 'planting', 'weather');
        }
    }
    
    /**
     * Extract author username from author URI or name.
     *
     * @return string The author username for WordPress.org contributor.
     */
    private function get_author_username() {
        // Extract username from a URL
        if ( ! empty( $this->plugin_data['author_uri'] ) && 
             preg_match( '/\/\/(?:github\.com|wordpress\.org|[\w\.-]+)\/([^\/]+)/', $this->plugin_data['author_uri'], $matches ) ) {
            return $matches[1];
        }
        
        // Clean up author name for use as username
        $username = strtolower( str_replace( ' ', '', $this->plugin_data['author'] ) );
        
        // Remove any special characters
        return preg_replace('/[^a-z0-9_-]/', '', $username);
    }
    
    /**
     * Convert markdown to WordPress readme.txt format.
     *
     * @return string The formatted readme.txt content.
     */
    public function convert_to_readme_txt() {
        $readme = "=== {$this->plugin_data['name']} ===\n";
        $readme .= "Contributors: " . implode( ', ', $this->plugin_data['contributors'] ) . "\n";
        $readme .= "Tags: " . implode( ', ', $this->plugin_data['tags'] ) . "\n";
        $readme .= "Requires at least: " . ($this->plugin_data['requires'] ?: '6.0') . "\n";
        $readme .= "Tested up to: " . ($this->plugin_data['tested'] ?: '6.4') . "\n";
        
        if ( ! empty( $this->plugin_data['requires_php'] ) ) {
            $readme .= "Requires PHP: {$this->plugin_data['requires_php']}\n";
        }
        
        $readme .= "Stable tag: {$this->plugin_data['version']}\n";
        $readme .= "License: {$this->plugin_data['license']}\n";
        $readme .= "License URI: {$this->plugin_data['license_uri']}\n\n";
        
        $readme .= "{$this->plugin_data['short_description']}\n\n";
        
        // Add Description section
        $readme .= "== Description ==\n\n";
        $readme .= $this->convert_markdown_to_readme( $this->plugin_data['description'] ) . "\n\n";
        
        // Add other sections
        foreach ( $this->plugin_data['sections'] as $section_name => $section_content ) {
            if ( $section_name !== 'Description' && $section_name !== 'Changelog' ) {
                $readme .= "== {$section_name} ==\n\n";
                $readme .= $this->convert_markdown_to_readme( $section_content ) . "\n\n";
            }
        }
        
        // Add missing recommended sections
        $this->add_missing_sections($readme);
        
        // Add changelog
        $readme .= "== Changelog ==\n\n";
        
        foreach ( $this->plugin_data['changelog'] as $version => $changes ) {
            $readme .= "= {$version} =\n";
            
            foreach ($changes as $type => $type_changes) {
                if ($type !== 'General') {
                    $readme .= "{$type}:\n";
                }
                
                foreach ($type_changes as $change) {
                    $readme .= "* {$change}\n";
                }
                
                $readme .= "\n";
            }
        }
        
        return $readme;
    }
    
    /**
     * Add missing but recommended sections to the readme.txt file
     * 
     * @param string &$readme Reference to the readme content
     */
    private function add_missing_sections(&$readme) {
        // Add FAQ section if not present
        if (!isset($this->plugin_data['sections']['Frequently Asked Questions'])) {
            $readme .= "== Frequently Asked Questions ==\n\n";
            $readme .= "= How accurate is the frost date information? =\n\n";
            $readme .= "The plugin uses data from NOAA/NWS sources and provides a statistical average based on historical data. While this gives a good indication for planning purposes, actual frost dates can vary due to local microclimate conditions.\n\n";
            $readme .= "= Can I use this plugin for commercial farming planning? =\n\n";
            $readme .= "Yes, the plugin can be useful for commercial planning. However, professional farmers may want to supplement this data with local agricultural extension services for critical planting decisions.\n\n";
        }
        
        // Add Screenshots section if not present
        if (!isset($this->plugin_data['sections']['Screenshots'])) {
            $readme .= "== Screenshots ==\n\n";
            $readme .= "1. Admin settings page for the Frost Date Lookup plugin\n";
            $readme .= "2. Front-end display of frost date information\n";
            $readme .= "3. Example of frost date results for a specific zipcode\n\n";
        }
        
        // Add Upgrade Notice section if not present
        if (!isset($this->plugin_data['sections']['Upgrade Notice'])) {
            $readme .= "== Upgrade Notice ==\n\n";
            $readme .= "= {$this->plugin_data['version']} =\n";
            $readme .= "This version contains important fixes and improvements to the readme.txt generation system. Update recommended for all users.\n\n";
        }
    }
    
    /**
     * Convert markdown syntax to WordPress readme syntax.
     *
     * @param string $content The markdown content.
     * @return string The converted content.
     */
    private function convert_markdown_to_readme( $content ) {
        // Convert markdown headers
        $content = preg_replace( '/####\s+(.+)/', '= $1 =', $content );
        $content = preg_replace( '/###\s+(.+)/', '= $1 =', $content );
        $content = preg_replace( '/##\s+(.+)/', '== $1 ==', $content );
        $content = preg_replace( '/#\s+(.+)/', '=== $1 ===', $content );
        
        // Convert markdown links
        $content = preg_replace( '/\[([^\]]+)\]\(([^)]+)\)/', '$1 ($2)', $content );
        
        // Convert markdown lists
        $content = preg_replace( '/^\s*[\*\-]\s+(.+)$/m', '* $1', $content );
        $content = preg_replace( '/^\s*\d+\.\s+(.+)$/m', '# $1', $content );
        
        // Convert code blocks
        $content = preg_replace( '/```([a-z]*)\n(.*?)```/s', "<pre><code>$2</code></pre>", $content );
        
        // Convert inline code
        $content = preg_replace( '/`([^`]+)`/', '<code>$1</code>', $content );
        
        return $content;
    }
    
    /**
     * Check for issues with the README.md file.
     *
     * @return array Array of recommended changes.
     */
    public function get_recommendations() {
        $recommendations = array();
        
        if ( empty( $this->plugin_data['requires_php'] ) ) {
            $recommendations[] = 'Add a "Requires PHP" field to specify the minimum PHP version required.';
        }
        
        if ( empty( $this->plugin_data['requires'] ) ) {
            $recommendations[] = 'Add a "Requires at least" field to specify the minimum WordPress version.';
        }
        
        if ( count( $this->plugin_data['tags'] ) < 5 ) {
            $recommendations[] = 'Add more specific tags to improve discoverability in the WordPress plugin directory.';
        }
        
        if ( ! isset( $this->plugin_data['sections']['Frequently Asked Questions'] ) ) {
            $recommendations[] = 'Consider adding a FAQ section to address common user questions.';
        }
        
        if ( ! isset( $this->plugin_data['sections']['Screenshots'] ) ) {
            $recommendations[] = 'Add a Screenshots section to show plugin features visually.';
        }
        
        if ( ! isset( $this->plugin_data['sections']['Upgrade Notice'] ) ) {
            $recommendations[] = 'Add an Upgrade Notice section for important version upgrades.';
        }
        
        if ( strlen( $this->plugin_data['short_description'] ) > 150 ) {
            $recommendations[] = 'Shorten the short description to under 150 characters for better display in the WordPress plugin directory.';
        }
        
        return $recommendations;
    }
    
    /**
     * Save the converted readme.txt file.
     *
     * @param string $output_path Path where to save the readme.txt file.
     * @return bool True if file was saved successfully, false otherwise.
     */
    public function save_readme_txt( $output_path ) {
        $readme_content = $this->convert_to_readme_txt();
        return (bool) file_put_contents( $output_path, $readme_content );
    }
}