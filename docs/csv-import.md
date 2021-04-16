# Import meals via CSV

> The following feature is only available in the [Premium]({{ site.baseurl }}/upgrade.html) version of the plugin.

Rather than manually add meals to your [meal collection]({{ site.baseurl }}/meal-collections.html), Meal Tracker has a CSV import tool which allows you to bulk import meals.

**Note:** Depending on your server, you may need to split larger CSV files into smaller, more manageable sizes for performance reasons. As a guide, we'd recommend processing no more than 500 rows at a time.

### CSV Format

A CSV file must adhere to the following rules:

- Have a header row with the following headers

*Note: The headers "description", "proteins", "carbs", and "fats" are required however, the data for each row can be left blank if not required.*
```
name,description,calories,quantity,unit,proteins,carbs,fats
```
- Have one or more data rows.
- For each row, ensure the following are met:
    - Has a name that is 100 characters or less.
    - If a description (optional) is specified, ensure it is no longer than 200 characters.
    - Enter a numeric value for calories (can be zero).
    - Enter a numeric value for quantity (can be zero).
    - If specifying a unit (can be blank), ensure it is one of the following values: na, g, oz, ml, small, medium or large.
    - If you have set unit to one the following, then ensure you have specified a quantity: g, oz, ml.
    
**Note:** If the import finds the above rules haven't been met, then the row will either be skipped or fail.

#### A simple CSV example

```
name,description,calories,quantity,unit
Apple Pie,Classic homemade apple pie,400,200,g
Plum,,15,0,medium
Pizza slice,Ham and Pineappe (crazy!),600,100,g
Coke,Bottle,30,500,ml
...
```

### Importing a CSV file

- Via the WordPress Admin menu, navigate to "Meal Tracker" > "Meal Collection".
- Click on the button "Import from CSV".
[![]({{ site.baseurl }}/assets/images/admin/import-button.png)]({{ site.baseurl }}/assets/admin/images/import-button.png) 
- On the following screen, click on the button "Select CSV file".
[![]({{ site.baseurl }}/assets/images/admin/import-select.png)]({{ site.baseurl }}/assets/admin/images/import-select.png) 
- Either upload a CSV to the media library or select an existing one. Click the button "Import CSV".
[![]({{ site.baseurl }}/assets/images/admin/import-selected.png)]({{ site.baseurl }}/assets/admin/images/import-selected.png)
- Examine the output report and action any errors.
[![]({{ site.baseurl }}/assets/images/admin/import-output.png)]({{ site.baseurl }}/assets/admin/images/import-output.png)

#### Dry run mode

If selected, dry run mode will run basic tests against each row without attempting to update the database. We recommend you do this before performing an import as it gives the opportunity to correct any issues before attempting the main import.