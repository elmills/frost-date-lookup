<?php
/**
 * GitHub README.md to WordPress readme.txt Converter
 *
 * @package ReadmeConverter
 * @version 1.0.25
 */

 class GitHub_To_WordPress_Readme_Converter {
    /**
     * The raw markdown content
     * 
     * @var string
     */
    private $markdown_content;
    
    /**
     * The processed readme.txt content
     * 
     * @var string
     */
    private $readme_content;
    
    /**
     * The metadata extracted from the markdown
     * 
     * @var array
     */
    private $metadata = [];
    
    /**
     * Constructor
     * 
     * @param string $markdown_content_or_path The Markdown content or file path to convert
     */
    public function __construct($markdown_content_or_path = '') {
        if (!empty($markdown_content_or_path)) {
            // Check if it's a file path
            if (file_exists($markdown_content_or_path) && is_readable($markdown_content_or_path)) {
                $markdown_content = file_get_contents($markdown_content_or_path);
                $this->set_markdown_content($markdown_content);
            } else {
                // Assume it's the content directly
                $this->set_markdown_content($markdown_content_or_path);
            }
        }
    }
    
    /**
     * Set the markdown content
     * 
     * @param string $markdown_content The Markdown content to convert
     * @return GitHub_To_WordPress_Readme_Converter
     */
    public function set_markdown_content($markdown_content) {
        $this->markdown_content = $markdown_content;
        return $this;
    }
    
    /**
     * Convert markdown to readme.txt
     * 
     * @return string The converted readme.txt content
     */
    public function convert() {
        if (empty($this->markdown_content)) {
            return '';
        }
        
        // Extract metadata and sections
        $this->extract_metadata();
        $sections = $this->extract_sections();
        
        // Build the readme.txt content
        $this->build_header();
        $this->build_sections($sections);
        
        return $this->readme_content;
    }
    
    /**
     * Extract metadata from the markdown content
     */
    private function extract_metadata() {
        // Default metadata values
        $this->metadata = [
            'plugin_name' => '',
            'version' => '',
            'requires_php' => '',
            'tested_up_to' => '',
            'author' => '',
            'author_uri' => '',
            'license' => '',
            'license_uri' => '',
            'contributors' => '',
            'tags' => '',
            'requires_at_least' => '5.0',
            'stable_tag' => '',
            'short_description' => '',
        ];
        
        // Plugin name (usually the first H1)
        if (preg_match('/^#\s+(.*?)$/m', $this->markdown_content, $matches)) {
            $this->metadata['plugin_name'] = trim($matches[1]);
        }
        
        // Extract version
        if (preg_match('/- Version:\s*([\d\.]+)/i', $this->markdown_content, $matches)) {
            $this->metadata['version'] = $this->metadata['stable_tag'] = trim($matches[1]);
        }
        
        // Extract PHP requirement
        if (preg_match('/- Requires PHP:\s*([\d\.]+)/i', $this->markdown_content, $matches)) {
            $this->metadata['requires_php'] = trim($matches[1]);
        }
        
        // Extract WordPress tested up to
        if (preg_match('/- Tested up to:\s*([\d\.]+)/i', $this->markdown_content, $matches)) {
            $this->metadata['tested_up_to'] = trim($matches[1]);
        }
        
        // Extract author
        if (preg_match('/- Author:\s*(.*?)$/im', $this->markdown_content, $matches)) {
            $this->metadata['author'] = trim($matches[1]);
            // Convert author to contributor slug
            $this->metadata['contributors'] = $this->create_contributor_slug(trim($matches[1]));
        }
        
        // Extract author URI
        if (preg_match('/- Author URI:\s*(.*?)$/im', $this->markdown_content, $matches)) {
            $this->metadata['author_uri'] = trim($matches[1]);
        }
        
        // Extract license
        if (preg_match('/- License:\s*(.*?)$/im', $this->markdown_content, $matches)) {
            $this->metadata['license'] = trim($matches[1]);
        }
        
        // Extract license URI
        if (preg_match('/- License URI:\s*(.*?)$/im', $this->markdown_content, $matches)) {
            $this->metadata['license_uri'] = trim($matches[1]);
        }
        
        // Extract short description
        if (preg_match('/## Description\s*(.*?)(?=##|\z)/ms', $this->markdown_content, $matches)) {
            $desc = trim($matches[1]);
            $desc_lines = explode("\n", $desc);
            $this->metadata['short_description'] = trim($desc_lines[0]);
        }
        
        // Extract tags - by default use common plugin tags
        $this->metadata['tags'] = 'plugin';
    }
    
    /**
     * Build the header section of the readme.txt
     */
    private function build_header() {
        $this->readme_content = "=== {$this->metadata['plugin_name']} ===\n";
        $this->readme_content .= "Contributors: {$this->metadata['contributors']}\n";
        $this->readme_content .= "Tags: {$this->metadata['tags']}\n";
        $this->readme_content .= "Requires at least: {$this->metadata['requires_at_least']}\n";
        $this->readme_content .= "Requires PHP: {$this->metadata['requires_php']}\n";
        $this->readme_content .= "Tested up to: {$this->metadata['tested_up_to']}\n";
        $this->readme_content .= "Stable tag: {$this->metadata['stable_tag']}\n";
        $this->readme_content .= "License: {$this->metadata['license']}\n";
        $this->readme_content .= "License URI: {$this->metadata['license_uri']}\n\n";
        $this->readme_content .= "{$this->metadata['short_description']}\n\n";
    }
    
    /**
     * Extract different sections from the markdown
     * 
     * @return array An array of sections
     */
    private function extract_sections() {
        $sections = [];
        
        // Pattern to match sections (## Heading)
        preg_match_all('/##\s+(.*?)(?=##|\z)/ms', $this->markdown_content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $section_title = trim($match[1]);
            $section_content = isset($match[2]) ? trim($match[2]) : '';
            
            // If no content was matched, extract it manually
            if (empty($section_content)) {
                $pos = strpos($this->markdown_content, '## ' . $section_title);
                $end_pos = strpos($this->markdown_content, '##', $pos + strlen('## ' . $section_title));
                
                if ($end_pos !== false) {
                    $section_content = trim(substr(
                        $this->markdown_content, 
                        $pos + strlen('## ' . $section_title), 
                        $end_pos - ($pos + strlen('## ' . $section_title))
                    ));
                } else {
                    // If this is the last section, get everything after the heading
                    $section_content = trim(substr(
                        $this->markdown_content, 
                        $pos + strlen('## ' . $section_title)
                    ));
                }
            }
            
            $sections[$section_title] = $section_content;
        }
        
        return $sections;
    }
    
    /**
     * Build the sections for the readme.txt
     * 
     * @param array $sections The sections extracted from markdown
     */
    private function build_sections($sections) {
        $standard_sections = [
            'Description' => 'Description',
            'Features' => 'Description',
            'Installation' => 'Installation',
            'Usage' => 'Usage',
            'Frequently Asked Questions' => 'Frequently Asked Questions',
            'FAQ' => 'Frequently Asked Questions',
            'Changelog' => 'Changelog',
            'License' => 'License',
            'Support' => 'Support'
        ];
        
        foreach ($standard_sections as $md_section => $wp_section) {
            if (isset($sections[$md_section])) {
                $content = $this->format_section_content($sections[$md_section], $md_section);
                
                // Special case for features - include in description
                if ($md_section === 'Features' && $wp_section === 'Description') {
                    if (isset($sections['Description'])) {
                        // Only add features if not already in description
                        if (strpos($sections['Description'], $sections['Features']) === false) {
                            $content = "\n= Features =\n" . $content;
                        } else {
                            continue;
                        }
                    }
                }
                
                $this->readme_content .= "== $wp_section ==\n\n$content\n\n";
            }
        }
        
        // Add Upgrade Notice if not present
        if (!isset($sections['Upgrade Notice'])) {
            $this->readme_content .= "== Upgrade Notice ==\n\n";
            
            // If we have a changelog, use the latest version for upgrade notice
            if (isset($sections['Changelog'])) {
                preg_match('/### \[([\d\.]+)\]/m', $sections['Changelog'], $matches);
                if (isset($matches[1])) {
                    $latest_version = $matches[1];
                    $this->readme_content .= "= $latest_version =\n";
                    $this->readme_content .= "This update includes the latest improvements and fixes.\n\n";
                }
            }
        }
    }
    
    /**
     * Format section content based on section type
     * 
     * @param string $content The section content
     * @param string $section_type The section type
     * @return string The formatted content
     */
    private function format_section_content($content, $section_type) {
        // Format the content based on section type
        switch ($section_type) {
            case 'Changelog':
                return $this->format_changelog($content);
                
            case 'Features':
                return $this->format_features($content);
                
            default:
                return $this->format_generic_section($content);
        }
    }
    
    /**
     * Format changelog section
     * 
     * @param string $content The changelog content
     * @return string The formatted changelog
     */
    private function format_changelog($content) {
        $formatted = '';
        
        // Replace ### [x.x.x] with = x.x.x =
        $content = preg_replace('/### \[([\d\.]+)\]/', '= $1 =', $content);
        
        // Replace #### Added/Changed/Fixed with * Added/Changed/Fixed
        $content = preg_replace('/#### (Added|Changed|Fixed|Improved)/', '* $1', $content);
        
        // Replace - with *
        $content = preg_replace('/- /', '* ', $content);
        
        // Clean up the content
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $formatted .= "$line\n";
            }
        }
        
        return $formatted;
    }
    
    /**
     * Format features section
     * 
     * @param string $content The features content
     * @return string The formatted features
     */
    private function format_features($content) {
        // Convert markdown bullet points to WP readme format
        $content = preg_replace('/- /', '* ', $content);
        return $content;
    }
    
    /**
     * Format generic section content
     * 
     * @param string $content The section content
     * @return string The formatted content
     */
    private function format_generic_section($content) {
        // Convert markdown bullet points to WP readme format
        $content = preg_replace('/- /', '* ', $content);
        
        // Convert markdown links [text](url) to WP links
        $content = preg_replace('/\[(.*?)\]\((.*?)\)/', '$1: $2', $content);
        
        return $content;
    }
    
    /**
     * Create a contributor slug from an author name
     * 
     * @param string $author_name The author name
     * @return string The contributor slug
     */
    private function create_contributor_slug($author_name) {
        // Convert spaces to underscores and make lowercase
        $slug = strtolower(str_replace(' ', '', $author_name));
        // Remove any non-alphanumeric characters
        $slug = preg_replace('/[^a-z0-9_]/', '', $slug);
        return $slug;
    }
    
    /**
     * Save the readme.txt content to a file
     * 
     * @param string $file_path The file path to save to
     * @return bool Whether the file was saved successfully
     */
    public function save($file_path) {
        if (empty($this->readme_content)) {
            $this->convert();
        }
        
        return file_put_contents($file_path, $this->readme_content) !== false;
    }
    
    /**
     * Save the readme.txt content to a file (alias for save)
     * 
     * @param string $file_path The file path to save to
     * @return bool Whether the file was saved successfully
     */
    public function save_readme_txt($file_path) {
        return $this->save($file_path);
    }
    
    /**
     * Get recommendations for improving the README.md
     * 
     * @return array An array of recommendations
     */
    public function get_recommendations() {
        $recommendations = [];
        
        // Check if metadata is properly defined
        if (empty($this->metadata['plugin_name'])) {
            $recommendations[] = 'Add a title (H1) to your README.md file.';
        }
        
        if (empty($this->metadata['version'])) {
            $recommendations[] = 'Add Version information (e.g., "- Version: 1.0.0").';
        }
        
        if (empty($this->metadata['requires_php'])) {
            $recommendations[] = 'Add PHP requirement (e.g., "- Requires PHP: 7.4").';
        }
        
        if (empty($this->metadata['tested_up_to'])) {
            $recommendations[] = 'Add WordPress compatibility (e.g., "- Tested up to: 6.3").';
        }
        
        // Check for key sections
        if (!preg_match('/## Description/i', $this->markdown_content)) {
            $recommendations[] = 'Add a Description section.';
        }
        
        if (!preg_match('/## Installation/i', $this->markdown_content)) {
            $recommendations[] = 'Add an Installation section.';
        }
        
        if (!preg_match('/## Changelog/i', $this->markdown_content)) {
            $recommendations[] = 'Add a Changelog section.';
        }
        
        return $recommendations;
    }
}

/*
// Example usage:
// To run this example, define GITHUB_TO_README_EXAMPLE constant as true
// Example: define('GITHUB_TO_README_EXAMPLE', true);

if (defined('GITHUB_TO_README_EXAMPLE') && GITHUB_TO_README_EXAMPLE) {
    // Load README.md file
    $readme_md = file_get_contents('README.md');
    
    // Convert to readme.txt
    $converter = new GitHub_To_WordPress_Readme_Converter($readme_md);
    $readme_txt = $converter->convert();
    
    // Save to file
    $converter->save('readme.txt');
    
    echo "Conversion complete!";
}
*/