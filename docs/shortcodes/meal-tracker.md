# [meal-tracker] 
This is the most popular shortcode for the Meal Tracker plugin and renders the main component of the plugin. Using this tools, users are able to:

* View a summary of their allowed calories for the day and how many they have already consumed.
* View a progress chart indicating visually how many calories have been consumed from the user's daily budget.
* View their previous entries. ([Premium]({{ site.baseurl }}/upgrade.html))
* Add entries for previous/future dates. ([Premium]({{ site.baseurl }}/upgrade.html))
* Add meals to their meal collection.
* Specify quantities of meals. If [Premium]({{ site.baseurl }}/upgrade.html), add fractions e.g. 1/2 a meal.
* Add meals to current entry for different meal times e.g. Morning, Lunch, etc.
* If enabled, search for meals from [external APIs]({{ site.baseurl }}/external-apis.html) and add them automatically to their meal collection. ([Premium]({{ site.baseurl }}/upgrade.html))

**Shortcode arguments**
    
The shortcode supports the following arguments:    
    
| Argument | Description | Options | Example |    
|--|--|--|--|   
| chart-height | Specifies the height of the progress chart used within the shortcode | Number/px  | [meal-tracker chart-height="150px"] |  
| url-login | If the user is logged out then the tracker presents a login link. By default, it will direct the user to the standard WP login page. This argument allows you to specify another login page.   | Text  | [meal-tracker url-login="https://mysite.com/mypage"] |  

# Images

### Main view
    
[![]({{ site.baseurl }}/assets/images/meal-tracker.png)]({{ site.baseurl }}/assets/images/meal-tracker.png)   

### Search for a meal dialog
    
[![]({{ site.baseurl }}/assets/images/search-for-meal.png)]({{ site.baseurl }}/assets/images/search-for-meal.png)   

### Search external API for a meal
    
[![]({{ site.baseurl }}/assets/images/external-api.png)]({{ site.baseurl }}/assets/images/external-api.png)   

### Add a new meal
    
[![]({{ site.baseurl }}/assets/images/add-new-meal.png)]({{ site.baseurl }}/assets/images/add-new-meal.png)  