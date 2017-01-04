##Overview

Super Search greatly improves search functionality, allowing for powerful and flexible searching on channel entries in ExpressionEngine. It can be used both as a search engine and as a replacement for the *Channel:Entries* tag.

The architecture of the add-on borrows from Google's model of constructing search queries. Anything you might want to search for... keywords, channels, categories, statuses, authors, custom fields, date ranges, custom field numeric ranges, etc, can be loaded into a single URI segment with the Super Search syntax. This makes pages highly shareable, flexible and versatile. Searches can be performed in a variety and/or *combination* of a few different ways:

* Through a POST or GET [search form](https://solspace.com/expressionengine/legacy/super-search/form) (Super Search automatically redirects the POST as a human-readable query in the URI).
* Visiting a [Results](https://solspace.com/expressionengine/legacy/super-search/results) template with a search query in the URI, whether it be linked from another page, or a bookmarked URL, etc.
* Hard-coding values for any type of search, including keywords, channels, categories, custom fields, etc.

Super Search supports [relevance](https://solspace.com/expressionengine/legacy/super-search/relevance_ordering)-based searching, which allows you to create simple or advanced algorithms to control ranking of entries in search results. This is done by using the relevance-related parameters in the [Super_Search:Results](https://solspace.com/expressionengine/legacy/super-search/results) tag.

Super Search also supports [Fuzzy Searching](https://solspace.com/expressionengine/legacy/super-search/fuzzy_searching), which basically means that searches can be set to ignore plurals, match up similar words, and suggest other words based on spelling in search terms.

And finally, Super Search includes a [Search Curation](https://solspace.com/expressionengine/legacy/super-search/control-panel/#curation) feature, which is designed to allow manual selection of entry results for keyword searches. This would typically be used as supplemental search results to normal search results, similar to how Google displays sponsored results at the top or side of search results pages.


##Important Notes

Super Search by [Solspace, Inc.](http://solspace.com) is a discontinued product and is provided here for free to users that wish to still use it.
**USE OF SUPER SEARCH FROM THIS REPO COMES WITH NO SUPPORT OR GUARANTEE THAT IT WILL WORK. WE WILL NOT UPDATE THIS REPO OR ACCEPT ANY PULL REQUESTS.**

Last ExpressionEngine versions known to work on is **EE 2.11.x** and **EE 3.4.x**

Documentation can be found here:
https://solspace.com/expressionengine/legacy/super-search
