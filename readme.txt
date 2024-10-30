=== HTML filter and csv-file search ===
Contributors: jonashjalmarsson
Tags: filter, csv, excel, search, jquery
Requires at least: 5.0
Tested up to: 6.3.2
Stable tag: 2.8
License: GPLv3
Text Domain: hk-filter-and-search
Domain Path: /languages
License URI: http://www.gnu.org/licenses/gpl.html

Easy way to enable jquery HTML filter or a CSV-file-search to a webpage. Use the shortcodes [csvsearch] and [filtersearch] to enable.


== Description ==
Two different shortcodes; `[csvsearch]` enables search in csv-file uploaded to the Media-library. Enter the URL in the shortcode to add a searchbox that will find matches in that file with a jquery-ajax-call and presents each row with the format specified. `[filtersearch]` enabled a filter search box to a page that filter contents instant with jquery. You can set the element to filter within and other settings through the shortcode attributes.

= [csvsearch] =
Add csv file search functionallity to any post or widget with the shortcode
`[csvsearch src="https://path_to/content/file.csv"]`

With this shortcode a search button will be generated and will by instant-ajax-search search through the csv-file in the src attribute and return the rows found matching the search input.

[Check blog post at jonashjalmarsson.se for live samples.](https://jonashjalmarsson.se/csv-and-html-search-filter-for-wordpress/)

**Settings:**

There are some settings to control the search and output.


* **src** Enter the path to the file to search, the file is uploaded through the Media archive as with any other file. Find the URL and enter it in this attribute.

* **format** How each line in the output will be formatted. 
Use {b} to render <b> etc. All { and } will be replaced with < and >.
Use {0}, {1} etc. to place the columns where you want in the output line.
Default is: `{b}{0}{/b}, {1}, {2}{br/}`
column1 (in bold), column2, column3 end with line-break.

* **searchtext** Text that will be shown in search button. Default is in swedish: 'Sök'.

* **charset** Default charset is 'iso-8859-1'. Can be set to any charset available through this attribute.

* **instantsearch** Should instant search be enabled. I.e. dropdown results when entering text. Default is 'false'.

* **instantformat** Format for instant search. Default is '{0}'.

* **dataidformat** Which column contains ID. Is used when click on dropdown. Default is '{0}'.

* **csv_separator** Set column delimiter to use. , or ; is normally used depending on csv type. Default is ';'

* **nothing_found_message** Set your own message when there are no hits in search. Default is 'Nothing found when searching for: '

* **exact_match** Set to true to only match exact matches, case sensitive. Default is 'false'

* **placeholder_text** Set a text to be seen as placeholder for the search. Will be replaced when a search is made. Default is none.

* **only_search_in_column** Only search in one specific column. Enter 0 if you only want to search in first column. Remove this argument or add -1 to search in all columns. Default is -1

* **show_header_row** Show first row as a header row by setting to true. Default is 'false'

* **headerformat** Combine with show_header_row to set format for header row. See **format** above to see more info.

* **ignore_default_header_style** Set to true to ignore default style for header row. Default is 'false'.

* **skip_file_check** Skip the file check. Use when file check not working properly, for exampel when WP installed in sub directory.

* **set_focus_on_load** Set to true to focus input element on page load, set false if not to focus. Default is 'false'.

**Screenshot 1 shortcode:**
`[csvsearch format="{0} | {1} | {3}{br/}" src="/files/2016/07/sizedata.csv"/]`
Which means showing first, second and fourth column from the file with a separator between and line break at the end.


= [filtersearch] =
Add filter/search functionallity to any post or widget with the shortcode
`[filtersearch]`

With this shortcode a filter input will be generated and will instant filter the content in the page or post. You will be able to select if all page will be filtered or just some of the elements.


**Settings:**

There are some settings to control the filter.

* **search_element** Select within which html-element the content should be filtered. Should be entered as 'element.classname' (i.e. table.content or .content) in any form combining element, id and/or classname.

* **show_header_in_table** Set to true if the first row in table is a title and always should be visible. Only affects when filtering tables.

* **text** Replace the standard text placed in front of search box.

* **clear_icon_class** Class to use to show 'clear'-icon. Default is 'delete-icon'.

* **clear_text** Text to show in the clear button, useful instead of icon. Default is empty.

* **set_focus_on_load** Set to true to focus input element on page load, set false if not to focus. Default is 'true'.

**Screenshot 2 shortcode:**
`[filtersearch text="Sök hämtningsdag för din ort" search_element="table" show_header_in_table="false"]`

== Screenshots ==

1. Output of csvsearch on page.
2. Output of filtersearch on page.

== Changelog ==

= 2.8 =
Major security fix. Attributes escaped. Cross scripting fix. Please update to this version!
Translation added. Swedish and English.

= 2.7 =
Bugfixes for filtersearch, search per td cell if filtering table. Support if thead is used in table.

= 2.6 =
Attribute set_focus_on_load added to csvsearch to set focus automatically to input element.

= 2.5 =
More attributes added when using show_header_row='true'. Set specific headerformat and ignore_default_header_style.

= 2.4 =
skip_file_check argument added to csvsearch shortcode to skip file check.

= 2.3 =
Added check to see if src-file exist. (also removes site name from src-file if any)
Bugfix for csvsearch, now shows nothing_found_message if show_header_row and is true is empty result.
Bugfix show_header_row now works if multiple shortcodes on same page.

= 2.2 =
Added show_header_row to csvsearch shortcode to be able to show first row as header row.

= 2.1 =
Load shortcode on init.

= 2.0 =
Cleanup warning text, default to 'table' if no element is set in filtersearch.

= 1.9 =
Add to specific column added to csvsearch.

= 1.8 =
Placeholder text is added to csvsearch.

= 1.7 =
Added exact_match to csvsearch shortcode to force check for exact matches, case sensitive.

= 1.6 =
Added nothing_found_message to csvsearch shortcode to set your own message.

= 1.5 =
csv_separator added to csvsearch shortcode to be able to set correct delimiter depending on csv file.

= 1.4 =
Change in how to find element to filter. Trying to find find closest post or page if shown in blog list.
New filtersearch setting added, set_focus_on_load, to be able to not focus the input element on page load. Default is still focused.

= 1.3 =
Bug fix. Fixing some issues when file added with wrong quotation marks in [csvsearch].

= 1.2 =
Bug fix. Now loading jquery if not previously loaded.
Update. 'Nothing found' message added when no results in csvsearch
Update. Rename of shortcode to [csvsearch] and [filtersearch] (old shortcodes still works).
Breaking style change. [filtersearch] style updated to fit modern standard. If your style is breaking, add argument old_style=true and old look should be back.

= 1.1 =
New feature instant search is added.

= 1.0 =
Initial commit.


== Frequently Asked Questions ==


== Installation ==

1. install and activate the plugin on the Plugins page
2. add one of the shortcodes to page or post content
