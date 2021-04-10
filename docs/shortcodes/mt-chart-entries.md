## [mt-chart-entries]

> The following shortcode is only available in the [Premium]({{ site.baseurl }}/upgrade.html) version of the plugin.

Render a line chart of the user's entries, displaying their allowed calorie intake for day against the calories consumed.

[![Chart]({{ site.baseurl }}/assets/images/chart-entries.png)]({{ site.baseurl }}/assets/images/chart-entries.png)

**Shortcode Arguments**
 
The shortcode supports the following arguments:
 
| Argument | Description | Options | Example |
|--|--|--|--|
| max-entries | The maximum number of data points to display (default 15)| number  | [mt-chart-entries max-entries=30] |  
|text-no-entries|The message to display if the user has no entries. |text| [mt-chart-entries text-no-entries="There are no entries"]