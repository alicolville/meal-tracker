# Gamification

> The following feature is only available in the [Pro]({{ site.baseurl }}/upgrade.html) version of the plugin.

Weight Tracker supports the common WordPress gamification plugin [myCred](https://mycred.me/). This allows you to reward yours users for setting their target weight and adding new weight entries. 

*Please note: This guide presumes you have the relevant knowledge to setup myCred and the core principles of the plugin.*

### How to enable

First, via the WP Dashboard > Plugins, install and configure the [myCred WordPress plugin](https://en-gb.wordpress.org/plugins/mycred/).

myCred has a collection of hooks (these can be found in WP Dashboard > Points > Hooks). Each hook allows other plugins, like Weight Tracker, to specify "on this event reward the user x points". There are hooks for the Weight Tracker events "Weight Entry Added" and "Target Set". If enabled, you have the ability to specify how many points a user should be rewarded with for each event. You can also set a limit to state that the award can only be awarded **x** times with the given time period.

### Usage examples

[![myCred]({{ site.baseurl }}/assets/images/mycred.png)]({{ site.baseurl }}/assets/images/mycred.png)

Below are some example usages by our friends at [Be Strong](https://mybestrong.com/).

**Today's Meal Tracker leaderboard**

    [mycred_leaderboard based_on="yk_mt_meal_added" number=20 total=1 timeframe="today" current=1 wrap="ol" template="#%position% %user_profile_link% (%cred_f% Superstars)"]

**This week's Meal Tracker leaderboard**

    [mycred_leaderboard based_on="yk_mt_meal_added" number=20 total=1 timeframe="this-week" current=1 wrap="ol" template="#%position% %user_profile_link% (%cred_f% Superstars)"]  

**This month's Meal Tracker leaderboard**
  
    [mycred_leaderboard based_on="yk_mt_meal_added" number=20 total=1 timeframe="thismonth" current=1 wrap="ol" template="#%position% %user_profile_link% (%cred_f% Superstars)"]  

**Today's Weight Tracker leaderboard**  

    [mycred_leaderboard based_on="ws_ls_ Weight_Entry" number=20 total=1 timeframe="today" current=1 wrap="ol" template="#%position% %user_profile_link% (%cred_f% Superstars)"]  

**This week's Weight Tracker leaderboard**

    [mycred_leaderboard based_on="ws_ls_ Weight_Entry" number=20 total=1 timeframe="this-week" current=1 wrap="ol" template="#%position% %user_profile_link% (%cred_f% Superstars)"]  

**This month's Weight Tracker leaderboard**

    [mycred_leaderboard based_on="ws_ls_ Weight_Entry" number=20 total=1 timeframe="thismonth" current=1 wrap="ol" template="#%position% %user_profile_link% (%cred_f% Superstars)"]
