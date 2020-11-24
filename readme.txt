=== Meal Tracker ===
Contributors: aliakro
Donate link: https://www.paypal.me/yeken
Tags: meal, tracker, calories, weight, food, fatsecrets, collection, macronutrients, fractions, search, edit, create
Requires at least: 5.2
Tested up to: 5.6
Stable tag: 2.4.3
Requires PHP: 7.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allow your users to track their meals and calorie intake for any day.

== Upgrade Notice ==

The initial build of Meal Tracker!

== Description ==

Extend your site by allowing your users to track their meals and calorie intake. Calorie targets for the day can be set by admins, the users or automatically pulled from [Yeken's Weight Tracker](https://wordpress.org/plugins/weight-loss-tracker/ "Yeken's Weight Tracker").

Features:

* Your user's can log their meals and calorie intake.
* Visual graph to show your user the percentage of their daily allowance used.
* Add meals for Breakfast, Mid-morning, Lunch, Afternoon, Dinner and Evening.
* View total calorie intake for the entire day or a breakdown.
* Each user has their own meal collection.
* Users can create and edit their meals.

Premium Features:

* As an administrator, you can view your user's progress and entries.
* Your user's can have Unlimited meals.
* If enabled, allow your users to record meals for any date, future or past.
* Allow your users to search other user's meals.
* Support for Macronutrients.
* Search external APIs (like Fatsecrets) for meals.
* FatSecrets Food and Recipe API are supported.
* Meal Collection - create a library of meals for your users to search.
* Fractional meal quantities - If enabled (via settings) additional quantity settings of 1/4, 1/2 and 3/4 are available when adding meals to an entry.
* View, edit and delete meals in your user's meal collection.

== Installation ==

1. Login into Wordpress Admin Panel
2. Navigate to Plugins > Add New
3. Search for "Meal Tracker"
4. Click Install now and activate plugin

== Frequently Asked Questions ==

= Does it support Yeken's Weight Tracker plugin? =

Yes. If you have have the Pro Plus version of Weight Tracker, a user's calorie allowance can be taken from there.

= Language support =

The plugin is written in English (UK) but is ready for translation. If you wish to add translations, please email me at email@yeken.uk

== Screenshots ==

1. Main view of [meal-tracker] shortcode.
2. "Add meal" view of [meal-tracker] shortcode.
3. Admin UI - summary overview.
4. Admin UI - user summary view.
5. Admin UI - view entry details.
6. Setup Wizard.
7. [meal-tracker] placement.
8. Search an external API for meals.
9. Manually add own meal to meal collection.

== Upgrade Notice ==

2.3 - Added quantity fractions when adding meals to entries.

== Changelog ==

= 2.4.3 =

* Signed off as working with 5.6

= 2.4.2 =

* Bug fix: Corrected language domain and path

= 2.4.1 =

* Bug fix: Back slashes are now removed from meal titles and descriptions (when used to escape quotes).
* Improvement:  Updated Arabic translations (thanks @Saeed)

= 2.4 =

* New feature: If enabled, MacroNutrient values are displayed on Meal line items.
* Improvement:  When settings are saved, entire cache is invalidated.

= 2.3 =

* New Feature: If enabled (via settings) new quantity settings of 1/4, 1/2 and 3/4 are available when adding meals to an entry.

= 2.2 =

* New feature: "Meal Collection" - ability to create a library of meals that your users can search.
* New feature: View, edit and delete meals in your user's meal collections.
* Improvement: Added extra security to Ajax calls to ensure a user cant edit another user's meals.
* Bug fix: Description is correctly updated when modified by a user.
* Bug fix: Removed edit button against meals that a user has added from someone else's collection i.e. restrict user's to editing their own meals.
* Bug fix: Under "Calorie Allowance", allow the setting "User specified" to be saved correctly when not premium

= 2.1 =

* New Feature: Support for FatSecrets Food API (https://platform.fatsecret.com/api/Default.aspx?screen=rapiref2&method=foods.search)
* Improvement: Added "Oz" unit for meals.

= 2.0.2 =

* Bug fix: Added missing text for translations.
* Bug fix: Error being thrown on settings page for external sources.

= 2.0.1 =

* Bug fix: Always create Macronutrient MySQL columns regardless of being enabled.

= 2.0 =

* Improvement: Underlying framework for supporting lookup of meals from external APIs.
* Improvement: FatSecret integration (look up meals from them).
* Improvement: Major overhaul of CSS.
* Improvement: Added unified look that ensures it looks more consistent across various themes.
* Improvement: Added a check on Settings page to check that all MySQL tables are present for the plugin. Option to rebuild them if not.
* Improvement: Basic support for specifying fats, proteins and carbs against meals. This will be expanded in the future to render totals and allow sites to focus on totals other than calories.
* Improvement: Added the underlying frame work for custom fields (although currently only utilised for MacroN fields).
* Bug fix: Fixed quantity error handling.
* Bug fix: Replaced deprecated jQuery code.

= 1.2 =

* Improvement: Added Arabic translations (thanks @Saeed)
* Bug fix: Minor bug fixes throwing PHP errors.
* Bug fix: Incorrect slug used for localisation.
* Bug fix: Warning being thrown for missing array index.

= 1.1 =

* Improvement: Added meal description to "View Entry" page in admin. (#66)
* Improvement: New Setting that enables users to search for meals added by any user. (#61)
* Improvement: New Settings that specify whether users are permitted to add new entries in the past and / or future (#72)
* Improvement: Clearer display names for users. (#73)
* Improvement: Admins can now delete user entries and mark their meals as deleted. (#68)
* Improvement: Options to display summary of entries for today, last 7 days, latest 100 and latest 500 on user data dashboard (#65)
* Improvement: Entry summary on the user data dashboard is now cached for 5 minutes to improve performance.
* Bug Fix: Err was being displayed for meal unit if not description had been set for a meal. (#64)
* Bug Fix: Added POT and en_GB language files. This should aid those that wish to translate the plugin (#74)
* Bug Fix: Minified CSS and JS in front end (#37)
* Bug fix: Fix an issue where Weight Tracker can't be selected correctly as a Calorie Allowance source.
* Bug fix: Responsive fix when adding menu link to Weight Tracker

= 1.0.1 =

* Bug Fix: Removed reference to missing function ws_ls_create_dialog_jquery_code()

= 1.0 =

* Initial Release
