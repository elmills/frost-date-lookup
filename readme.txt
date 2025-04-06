=== Frost Date Lookup ===
Contributors: elmills
Tags: frost date, lookup, wordpress, zipcode, garden
Requires at least: 6.0
Requires PHP: 8.1
Tested up to: 6.4
Stable tag: 1.0.31
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

The Frost Date Lookup plugin retrieves the average frost-free date based on a provided zipcode using NOAA/NWS data.

== Description ==

The Frost Date Lookup plugin retrieves the average frost-free date based on a provided zipcode, along with the maximum and minimum frost-free dates from the last 15 years and the last 30 years using NOAA/NWS data. This plugin is designed to help gardeners and farmers determine the best planting times based on frost-free dates.

= Features =
* Retrieve average frost-free dates based on zipcode.
* Access maximum and minimum frost-free dates from the last 15 and 30 years.
* User-friendly admin interface for settings.
* Public-facing display of frost-free date information.
* Localization support for multiple languages.
* Automatic updates from GitHub.

== Installation ==

1. Upload the `frost-date-lookup` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure the settings in the admin panel under 'Frost Date Lookup'.

== Usage ==

* Use the shortcode `[frost_date_lookup zipcode="YOUR_ZIPCODE"]` to display frost-free date information on any post or page.
* Replace `YOUR_ZIPCODE` with the desired zipcode to retrieve the relevant data.

== Frequently Asked Questions ==

= How accurate is the frost date information? =

* The plugin uses data from NOAA/NWS sources and provides a statistical average based on historical data. While this gives a good indication for planning purposes, actual frost dates can vary due to local microclimate conditions.

== Changelog ==

= 1.0.31 =
* Changed: Removed dynamic readme.txt generation in favor of a static file
* Changed: Version increment for plugin simplification

= 1.0.30 =
* Changed: Version increment for new release
* Changed: Maintained compatibility with GitHub readme converter

= 1.0.29 =
* Changed: New improved README.MD to Readme.txt

= 1.0.28 =
* Changed: Refactored GitHub integration for better maintainability
* Changed: Improved readme.txt generation with enhanced section extraction
* Changed: Fixed missing Installation and Changelog sections in plugin details view
* Changed: Moved GitHub updater functionality to reusable GitHub_Readme_Updater class

= 1.0.27 =
* Changed: Improved code modularity with dedicated GitHub_Readme_Updater class
* Changed: Eliminated code duplication between GitHub updater classes 
* Changed: Enhanced separation of concerns for better maintainability
* Changed: Refactored readme.txt generation for better reusability

= 1.0.26 =
* Fixed: New class to generate readme.txt

= 1.0.25 =
* Fixed: Improved update detection mechanism for more reliable version checking
* Fixed: Enhanced readme.txt generation with proper metadata formatting
* Fixed: Fixed "Available Version: Unknown" display in update diagnostics
* Fixed: Added additional cache clearing for more consistent update checking

= 1.0.24 =
* Fixed: Completely revised GitHub updater implementation for reliable update detection
* Fixed: Fixed "Available Version: Unknown" issue in the update diagnostics
* Fixed: Improved metadata handling for better WordPress integration
* Fixed: Added forced readme.txt generation on plugin initialization
* Fixed: Set hook priority to ensure dependencies load first

= 1.0.23 =
* Fixed: Resolved issue with "Unknown" version reporting in update system
* Fixed: Enhanced GitHub API integration for better update detection
* Fixed: Fixed plugin updater compatibility with new dependency versions

= 1.0.22 =
* Improved: Optimized readme.txt generation with performance improvements
* Improved: Implemented intelligent caching to prevent redundant file generation
* Improved: Added targeted update checks during critical WordPress operations
* Improved: Enhanced version detection reliability in the WordPress update system

= 1.0.21 =
* Changed: Updated version numbers for release
* Changed: Ensured all version references are consistent throughout the plugin
* Changed: Prepared plugin for release with latest changes

= 1.0.20 =
* Fixed: Enhanced GitHub automatic update detection system
* Fixed: Fixed "View Details" functionality in the WordPress plugin page
* Fixed: Added diagnostic tools to help troubleshoot update issues
* Fixed: Improved readme.txt generation for better WordPress compatibility

= 1.0.19 =
* Changed: Updated version number to fix automatic update system

= 1.0.18 =
* Changed: Documentation link change

= 1.0.17 =
* Changed: More Bug Fixes

= 1.0.16 =
* Changed: Bug Fix

= 1.0.15 =
* Changed: Trying a different class structure

= 1.0.14 =
* Changed: More tweaks to the display of README information

= 1.0.13 =
* Changed: Fixed bug with internal constants

= 1.0.12 =
* Changed: Using functions within updater plugin to pull documentation from readme.

= 1.0.11 =
* Changed: Improved update documentation process in GitHub
* Changed: Enhanced plugin documentation workflow

= 1.0.10 =
* Fixed: Cleanedup main plugin file
* Fixed: Continued work to optimize the update process

= 1.0.9 =
* Added: Enhanced frost date calculation accuracy
* Added: Added caching mechanism for faster data retrieval
* Changed: Improved zipcode validation system
* Changed: Updated NOAA/NWS API integration
* Fixed: Resolved display issues on mobile devices
* Fixed: Fixed date formatting inconsistencies across different locales

= 1.0.8 =
* Changed: Latest improvements and optimizations
* Changed: Enhanced plugin stability
* Changed: Updated compatibility with WordPress 6.4

= 1.0.7 =
* Changed: Bug fix for update system

= 1.0.6 =
* Changed: Realigned components and system updates

= 1.0.5 =
* Changed: Version update and plugin improvements

= 1.0.2 =
* Changed: Version bump for maintenance release

= 1.0.1 =
* Fixed: Bug with data retrieval and improved stability of API requests
* Fixed: Compatibility issues with latest WordPress version
* Fixed: Optimized data retrieval from NOAA/NWS API

= 1.0.0 =
* Added: Initial release of the Frost Date Lookup plugin

== Upgrade Notice ==

= 1.0.30 =
This update includes the latest improvements and fixes.

== License ==
This plugin is licensed under the GPLv2 or later. See the LICENSE file for more details.

== Support ==
For support, please open an issue on the GitHub repository or contact the plugin author.