=== Frost Date Lookup ===
Contributors: everettemills
Tags: frost, date, lookup, retrieves, average
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 8.1
Stable tag: 1.0.32
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

The Frost Date Lookup plugin retrieves the average frost-free date based on a provided zipcode, along with the maximum and minimum frost-free dates from the last 15 years and the last 30 years using NOAA/NWS data. This plugin is designed to help gardeners and farmers determine the best planting times based on frost-free dates.

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

1. Upload the frost-date-lookup folder to the /wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure the settings in the admin panel under 'Frost Date Lookup'.

== Usage ==

* Use the shortcode [frost_date_lookup zipcode="YOUR_ZIPCODE"] to display frost-free date information on any post or page.
* Replace YOUR_ZIPCODE with the desired zipcode to retrieve the relevant data.

== Frequently Asked Questions ==

**How accurate is the frost date information?:**
* The plugin uses data from NOAA/NWS sources and provides a statistical average based on historical data. While this gives a good indication for planning purposes, actual frost dates can vary due to local microclimate conditions.

== Changelog ==

= 1.0.32 =

* Changed to automated precommit generation of readme.txt
* Using script to convert from README.md to readme.txt at stageing step

= 1.0.31 =

* Removed dynamic readme.txt generation in favor of a static file
* Version increment for plugin simplification

= 1.0.30 =

* Version increment for new release
* Maintained compatibility with GitHub readme converter

= 1.0.29 =

* New improved README.MD to Readme.txt

= 1.0.28 =

* Refactored GitHub integration for better maintainability
* Improved readme.txt generation with enhanced section extraction
* Fixed missing Installation and Changelog sections in plugin details view
* Moved GitHub updater functionality to reusable GitHub_Readme_Updater class

= 1.0.27 =

* Improved code modularity with dedicated GitHub_Readme_Updater class
* Eliminated code duplication between GitHub updater classes
* Enhanced separation of concerns for better maintainability
* Refactored readme.txt generation for better reusability

= 1.0.26 =

* New class to generate readme.txt

= 1.0.25 =

* Improved update detection mechanism for more reliable version checking
* Enhanced readme.txt generation with proper metadata formatting
* Fixed "Available Version: Unknown" display in update diagnostics
* Added additional cache clearing for more consistent update checking

= 1.0.24 =

* Completely revised GitHub updater implementation for reliable update detection
* Fixed "Available Version: Unknown" issue in the update diagnostics
* Improved metadata handling for better WordPress integration
* Added forced readme.txt generation on plugin initialization
* Set hook priority to ensure dependencies load first

= 1.0.23 =

* Resolved issue with "Unknown" version reporting in update system
* Enhanced GitHub API integration for better update detection
* Fixed plugin updater compatibility with new dependency versions

= 1.0.22 =
* Optimized readme.txt generation with performance improvements
* Implemented intelligent caching to prevent redundant file generation
* Added targeted update checks during critical WordPress operations
* Enhanced version detection reliability in the WordPress update system

= 1.0.21 =
* Updated version numbers for release
* Ensured all version references are consistent throughout the plugin
* Prepared plugin for release with latest changes

= 1.0.20 =
* Enhanced GitHub automatic update detection system
* Fixed "View Details" functionality in the WordPress plugin page
* Added diagnostic tools to help troubleshoot update issues
* Improved readme.txt generation for better WordPress compatibility

= 1.0.19 =
* Updated version number to fix automatic update system

= 1.0.18 =
* Documentation link change

= 1.0.17 =
* More Bug Fixes

= 1.0.16 =
* Bug Fix

= 1.0.15 =
* Trying a different class structure

= 1.0.14 =
* More tweaks to the display of README information

= 1.0.13 =
* Fixed bug with internal constants

= 1.0.12 =
* Using functions within updater plugin to pull documentation from readme.

= 1.0.11 =
* Improved update documentation process in GitHub
* Enhanced plugin documentation workflow

= 1.0.10 =
* Cleanedup main plugin file
* Continued work to optimize the update process

= 1.0.9 =

* Enhanced frost date calculation accuracy
* Added caching mechanism for faster data retrieval
* Improved zipcode validation system
* Updated NOAA/NWS API integration
* Resolved display issues on mobile devices
* Fixed date formatting inconsistencies across different locales

= 1.0.8 =

* Latest improvements and optimizations
* Enhanced plugin stability
* Updated compatibility with WordPress 6.4

= 1.0.7 =

* Bug fix for update system

= 1.0.6 =

* Realigned components and system updates

= 1.0.5 =

* Version update and plugin improvements

= 1.0.2 =

* Version bump for maintenance release

= 1.0.1 =

* Bug with data retrieval and improved stability of API requests
* Compatibility issues with latest WordPress version
* Optimized data retrieval from NOAA/NWS API

= 1.0.0 =

* Initial release of the Frost Date Lookup plugin

== Upgrade Notice ==

= 1.0.32 =
Update to version 1.0.32. See changelog for details.

== License ==

This plugin is licensed under the GPLv2 or later. See the LICENSE file for more details.

== Other Notes ==

For support, please open an issue on the GitHub repository or contact the plugin author.Test
