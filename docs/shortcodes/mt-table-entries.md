## [mt-table-entries]

> The following shortcode is only available in the [Premium]({{ site.baseurl }}/upgrade.html) version of the plugin.

Render a table of the user's entries with the columns *date, calories allowed, calories used, calories remaining, percentage and an optional link to view the entry*.

If a URL is specified to a page with the [[meal-tracker]]({{ site.baseurl }}/shortcodes/meal-tracker.html) shortcode on, "View" links will appear that allow the user to view each entry.

A table can displayed in two modes:

### Advanced mode

Advanced mode supports the following features:

- Responsive columns
- Sorting
- Paging
- FontAwesome icons

[![Advanced]({{ site.baseurl }}/assets/images/table-advanced.png)]({{ site.baseurl }}/assets/images/table-advanced.png)

### Basic mode

As the name suggests, the basic mode renders a simple HTML table.

[![Basic]({{ site.baseurl }}/assets/images/table-basic.png)]({{ site.baseurl }}/assets/images/table-basic.png)

**Shortcode Arguments**
 
The shortcode supports the following arguments:
 
| Argument | Description | Options | Example |
|--|--|--|--|
| url-mealtracker | Specify a URL to a page that has the [[meal-tracker]]({{ site.baseurl }}/shortcodes/meal-tracker.html) shortcode placed | URL  | [mt-table-entries url-mealtracker="https://yeken.uk/mealtracker/"] |  
|user-id|By default, the shortcode will display the table for the current user. You can display the table for another user by setting this argument to the relevant user ID.|Numeric| [mt-table-entries user-id=12]
|sort-direction|By default, the entries are displayed in reverse chronological order - old to new (desc). This can be reversed by changing this argument to "asc"| "asc" or "desc" | [mt-table-entries sort-direction="asc"]
|text-no-entries|The message to display if the user has no entries. |text| [mt-table-entries text-no-entries="There are no entries"]
|type|The type of table to display. | "advanced" (default) or "basic" | [mt-table-entries type="basic"]