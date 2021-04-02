# External APIs

> The following feature is only available in the [Premium]({{ site.baseurl }}/upgrade.html) version of the plugin.

Meal Tracker currently supports [Fatsecret's](https://platform.fatsecret.com/api/) [Food](https://platform.fatsecret.com/api/Default.aspx?screen=rapiref&method=foods.search) and [Recipe](https://platform.fatsecret.com/api/Default.aspx?screen=rapiref&method=recipe.get) API. If enabled and set up correctly, your users will be able to search Fatsecret's online library for meals to add to their collection. This saves them manually entering the information for a given meal, instead, the title, description and other information such as macronutrients are automatically copied to their collection. 

### How to setup

- Via the WordPress Admin menu, navigate to "Meal Tracker" > "Settings".
- Open the tab "External Sources".
- Follow the instructions under the heading "Fat Secret API". This includes visiting [https://platform.fatsecret.com/api/Default.aspx?screen=myk](https://platform.fatsecret.com/api/Default.aspx?screen=myk), creating an account with FatSecrets and retrieve your Client ID and Secret.
- *Whitelist your server IP*. By default, FatSecrets will block all incoming connections, so you must whitelist your servers IP address in the FatSecret's admin panel.
- At this point, you should be able to fetch data from the FatSecrets API. This can be tested by clicking the button "Perform a test search for 'apples'". If succesful, you should see the following response:

```
Success: Results have been found for "apples"Array
(
    [0] => Array
        (
            [name] => Candied Apples
            [description] => Feeling deprived at Halloween?  A spooktacular substitute.
            [calories] => 92
            [meta_proteins] => 5.82
```
- Ensure "Yes" is selected under the heading "Enabled".

[![Settings]({{ site.baseurl }}/assets/images/admin/settings-fatsecrets-small.png)]({{ site.baseurl }}/assets/images/admin/settings-fatsecrets.png)

### What the user sees

Once enabled, when searching their own meal collection, under the heading "Add a new meal to your collection" is a new button labelled "Search". Clicking this button allows your user to search FatSecrets.

[![]({{ site.baseurl }}/assets/images/search-with-api.png)]({{ site.baseurl }}/assets/images/search-with-api.png) 

[![]({{ site.baseurl }}/assets/images/external-api.png)]({{ site.baseurl }}/assets/images/external-api.png) 

