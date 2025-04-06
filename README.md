# Frost Date Lookup Plugin

- Plugin Name: Frost Date Lookup
- Version: 1.0.22
- Requires at least: 6.0
- Tested up to: 6.4
- Author: Everette Mills
- Author URI: https://blueboatsolutions.com
- License: GPL-2.0+
- License URI: http://www.gnu.org/licenses/gpl-2.0.txt


## Description
The Frost Date Lookup plugin retrieves the average frost-free date based on a provided zipcode, along with the maximum and minimum frost-free dates from the last 15 years and the last 30 years using NOAA/NWS data. This plugin is designed to help gardeners and farmers determine the best planting times based on frost-free dates.

## Features
- Retrieve average frost-free dates based on zipcode.
- Access maximum and minimum frost-free dates from the last 15 and 30 years.
- User-friendly admin interface for settings.
- Public-facing display of frost-free date information.
- Localization support for multiple languages.
- Automatic updates from GitHub.

## Installation
1. Upload the `frost-date-lookup` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure the settings in the admin panel under 'Frost Date Lookup'.

## Usage
- Use the shortcode `[frost_date_lookup zipcode="YOUR_ZIPCODE"]` to display frost-free date information on any post or page.
- Replace `YOUR_ZIPCODE` with the desired zipcode to retrieve the relevant data.

## Changelog

All notable changes to this project will be documented in this file.

### [1.0.22]

#### Improved
- Optimized readme.txt generation with performance improvements
- Implemented intelligent caching to prevent redundant file generation
- Added targeted update checks during critical WordPress operations
- Enhanced version detection reliability in the WordPress update system

### [1.0.21]

#### Changed
- Updated version numbers for release
- Ensured all version references are consistent throughout the plugin
- Prepared plugin for release with latest changes

### [1.0.20]

#### Fixed
- Enhanced GitHub automatic update detection system
- Fixed "View Details" functionality in the WordPress plugin page
- Added diagnostic tools to help troubleshoot update issues
- Improved readme.txt generation for better WordPress compatibility

### [1.0.19]

#### Changed
- Updated version number to fix automatic update system

### [1.0.18]

#### Changed
- Documentation link change

### [1.0.17]

#### Changed
- More Bug Fixes

### [1.0.16]

#### Changed
- Bug Fix

### [1.0.15]

#### Changed
- Trying a different class structure

### [1.0.14]

#### Changed
- More tweaks to the display of README information

### [1.0.13]

#### Changed
- Fixed bug with internal constants

### [1.0.12]

#### Changed
- Using functions within updater plugin to pull documentation from readme.

### [1.0.11]

#### Changed
- Improved update documentation process in GitHub
- Enhanced plugin documentation workflow

### [1.0.10]

#### Fixed
- Cleanedup main plugin file
- Continued work to optimize the update process

### [1.0.9]
#### Added
- Enhanced frost date calculation accuracy
- Added caching mechanism for faster data retrieval
#### Changed
- Improved zipcode validation system
- Updated NOAA/NWS API integration
#### Fixed
- Resolved display issues on mobile devices
- Fixed date formatting inconsistencies across different locales

### [1.0.8]
#### Changed
- Latest improvements and optimizations
- Enhanced plugin stability
- Updated compatibility with WordPress 6.4

### [1.0.7]
#### Changed
- Bug fix for update system

### [1.0.6]
#### Changed
- Realigned components and system updates

### [1.0.5]
#### Changed
- Version update and plugin improvements

### [1.0.2]
#### Changed
- Version bump for maintenance release

### [1.0.1]
#### Fixed
- Bug with data retrieval and improved stability of API requests
- Compatibility issues with latest WordPress version
- Optimized data retrieval from NOAA/NWS API

### [1.0.0]
#### Added
- Initial release of the Frost Date Lookup plugin

## License
This plugin is licensed under the GPLv2 or later. See the LICENSE file for more details.

## Support
For support, please open an issue on the GitHub repository or contact the plugin author.