=== Meal Tracker ===
Contributors: aliakro
Donate link: https://www.paypal.me/yeken
Tags: meal, tracker, calories, weight, food, fatsecrets, collection, macronutrients, fractions, search, edit, create
Requires at least: 5.7
Tested up to: 5.8.2
Stable tag: 3.0.8
Requires PHP: 7.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allow your users to track their meals and calorie intake for any day.

== Upgrade Notice ==

The initial build of Meal Tracker!

== Description ==

Extend your site by allowing your users to track their meals and calorie intake. Calorie targets for the day can be set by admins, the users or automatically pulled from [Yeken's Weight Tracker](https://wordpress.org/plugins/weight-loss-tracker/ "Yeken's Weight Tracker").

 == Documentation ==

For further information read our documentation:
[Meal Tracker - Documentation](https://mealtracker.yeken.uk)

= Core Features =

* [\[meal-tracker\]](https://mealtracker.yeken.uk/shortcodes/meal-tracker.html) shortcode.
* Your user's can log their meals and calorie intake.
* Visual graph to show your user the percentage of their daily allowance used.
* Add meals for Breakfast, Mid-morning, Lunch, Afternoon, Dinner and Evening.
* View total calorie intake for the entire day or a breakdown.
* Each user has their own meal collection.
* Users can create and edit their meals.

= Premium Features =

* [Additional shortcodes](https://mealtracker.yeken.uk/shortcodes.html).
* As an administrator, you can view your user's progress and entries.
* Your user's can have Unlimited meals.
* If enabled, allow your users to record meals for any date, future or past.
* Allow your users to search other user's meals.
* [myCred integration](https://mealtracker.yeken.uk/gamification.html). Gamify your user's entries.
* Support for Macronutrients.
* Search [external APIs](https://mealtracker.yeken.uk/external-apis.html) (like [Fatsecret](https://www.fatsecret.com/)) for meals.
* [FatSecrets Food](https://platform.fatsecret.com/api/Default.aspx?screen=rapiref&method=foods.search) and [Recipe API](https://platform.fatsecret.com/api/Default.aspx?screen=rapiref&method=recipe.get) are supported.
* [Meal Collection](https://mealtracker.yeken.uk/meal-collections.html) - create a library of meals for your users to search.
* Fractional meal quantities - If enabled (via settings) additional quantity settings of 1/4, 1/2 and 3/4 are available when adding meals to an entry.
* View, edit and delete meals in your user's meal collection.

== Installation ==

1. Login into Wordpress Admin Panel
2. Navigate to Plugins > Add New
3. Search for "Meal Tracker"
4. Click Install now and activate plugin

Read more at [Meal Tracker - Documentation](https://mealtracker.yeken.uk) .

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

3.0 - Shortcodes, CSV Import and much more.

== Changelog ==

= 3.0.8 =

* New setting: "Calories Allowed colour" - specify the calories user colour on the pie chart.

= 3.0.7 =

* Updated version WP compatibility statement.

= 3.0.6 =

* Version bump.

= 3.0.5 =

* Updated translation files.

= 3.0.4 =

* Updated version WP compatibility statement.

= 3.0.3 =

* Bug fix: Removed stray var_dump()

= 3.0.2 =

* Bug fix: In some cases, the side bar was missing from admin user panels.

= 3.0.1 =

* Improvement: Small improvements to documentation.

= 3.0 =

* New feature: CSV Import. Bulk import meals into your meal collection. https://mealtracker.yeken.uk/csv-import.html
* New feature: New shortcode: [mt-chart-entries] which displays a chart of the user's entries. Read more: https://mealtracker.yeken.uk/shortcodes/mt-chart-entries.html
* New feature: New shortcode: [mt-table-entries] which displays a table of the user's entries. Read more: https://mealtracker.yeken.uk/shortcodes/mt-table-entries.html
* New feature: New shortcode: [mt-date-oldest-entry] which displays the date of the user's oldest entry. Read more: https://mealtracker.yeken.uk/shortcodes/mt-date-oldest-entry.html
* New feature: New shortcode: [mt-date-latest-entry] which displays the date of the user's latest entry. Read more: https://mealtracker.yeken.uk/shortcodes/mt-date-latest-entry.html
* New feature: New shortcode: [mt-count-entries] which displays a count of the user's entries. Read more: https://mealtracker.yeken.uk/shortcodes/mt-count-entries.html
* New feature: New shortcode: [mt-count-meals] which displays the number of meals the user has added to their collection. Read more: https://mealtracker.yeken.uk/shortcodes/mt-count-meals.html
* New feature: New shortcode [mt-chart-today] which is used to display the user's progress for today. Read more: https://mealtracker.yeken.uk/shortcodes/mt-chart-today.html
* New feature: New shortcode: [mt-calories-allowance] displays the calorie allowance for today's entry. Read more: https://mealtracker.yeken.uk/shortcodes.html
* New feature: New shortcode: [mt-calories-remaining]] displays the remaining calorie allowance for today's entry. Read more: https://mealtracker.yeken.uk/shortcodes.html
* New feature: New shortcode: [mt-calories-used] displays the calories used for today's entry. Read more: https://mealtracker.yeken.uk/shortcodes.html
* New feature: New shortcode: [mt-calories-used-percentage] displays the percentage of calories used for today . Read more: https://mealtracker.yeken.uk/shortcodes.html
* New feature: Added a "Delete all cache" button a user's profile within the admin area.
* Improvement: Display and change the order of boxes on user summary and profile page (admin).
* Improvement: Set the height of the progress chart in [meal-tracker] shortcode to 200px. This can be overridden using the shortcode argument below.
* Improvement: Added the argument "chart-height", "chart-type", "chart-hide-legend" and "chart-hide-title" to the shortcode [meal-tracker] Read more: https://mealtracker.yeken.uk/shortcodes/meal-tracker.html
* Improvement: Added the argument "url-login" to the shortcode [meal-tracker] Read more: https://mealtracker.yeken.uk/shortcodes/meal-tracker.html
* Improvement: Removed caching layer and integrated the new optimised caching layer that was written from scratch for Weight Tracker.
* Improvement: Upgraded underlying charting library (from Chart.js 2 > 3) and updated related MT code.
* Improvement: Re-factored underlying charting code so cleaner and optimised.
* Improvement: Added 'kcal' to chart labels.
* Improvement: Added Bezier curve to lne graphs.
* Bug fix: Date format is now taken from WordPress admin settings.
* Bug fix: Removed duplicate HTML element with the ID "yk-mt-button-meal-add".
* Bug fix: Replaced incorrect reference to plugin slug used with in gamification.
* Added links to the new Meal Tracker documentation site https://mealtracker.yeken.uk

= 2.5.1 =

* Bug fix: Removed empty line from gamification PHP file.

= 2.5 =

* New Feature: Support for myCred. Reward your users for creating new entries and adding meals to their entries.

= 2.4.4 =

* Updated "Tested upto" statement within readme.txt

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
