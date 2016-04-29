=== Post Pay Counter ===
Contributors: Ste_95
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SM5Q9BVU4RT22
Tags: counter, authors, payment, revenue sharing, stats, multi author, post management, post
Tested up to: 4.5
Stable tag: 2.603
Requires at least: 3.7

Easily handle authors' payments on a multi-author blog by computing posts' remuneration basing on admin defined rules.

== Description ==
Easily calculate and handle authors' pay on a multi-author blog by computing posts' remuneration basing on admin defined rules. The administrator can specify criteria upon which payments should be computed and the stats will immediately be viewable. Both a general view with all users and a specific one for a author are possible. It can easily help you implement a revenue sharing/paid to write model for your business.

Features include:

* Pay per post, word, visit ([tutorial](http://www.thecrowned.org/pay-writers-per-visit-wordpress?utm_source=wprep&utm_medium=link)), image and comment (not mutually exclusive).
* Pay with an incremental system (eg. each word is €0.01 => 100 words = €1) or with a zonal one (eg. from 200 to 300 words/visits it’s €2.00, up to 10 zones).
* No account needed. Data is yours, no need to sign-up to anything really.
* Old stats availability. View posts countings since the first written post, disregarding the plugin install date. A fancy date picker lets you shift between days and select the desired range.
* Personalize user's settings, so that special settings only apply to a particular user. Different settings can be made viewable in the stats or hidden depending on your needs.
* Customizable permissions to prevent your users to see stats and use functions they are not supposed to.
* Extend with your own custom implementation through hooks, filters and special API features ([learn more](http://postpaycounter.com/add-custom-payment-types-post-pay-counter-stats?utm_source=wprep&utm_medium=link)).
* And... works with custom post types, narrow your payments only to chosen user groups, and more.

[GitHub repository](https://github.com/TheCrowned/Post-Pay-Counter/) (wanna join us coding?)

= Integrate with Analytics/Adsense and pay with PayPal =
The [PRO version](http://postpaycounter.com/post-pay-counter-pro?utm_source=wprep&utm_medium=link&utm_campaign=ppcp) includes Analytics visits payment, Adsense Revenues sharing and PayPal payments. Among other stuff, it also allows to keep a convenient log of past payments and to display stats in public pages through a shortcode. 

= Integrate with Facebook =
The [Facebook addon](http://postpaycounter.com/facebook-pay-per-social-interactions-shares-likes-and-comments?utm_source=wprep&utm_medium=link&utm_campaign=ppcp_fb) allows to pay posts for the Facebook shares and comments they receive.

Browse [all extensions](http://postpaycounter.com/addons?utm_source=wprep&utm_medium=description&utm_campaign=ppc_addons).

= Available languages =
Post Pay Counter has been translated into the following languages:

* English
* German ([Julian Beck](http://inside11.de/))
* Dutch (Elza van Swieten)
* Italian (Stefano Ottolenghi)
* Portoguese (Marco Dantas)
* Turkish (Kamer DINC)
* Czech (Jiří Kučera)

If you want to **translate it in your own language** and get a discount on the PRO version, [contact us](http://www.thecrowned.org/contact-me)!

[youtube https://www.youtube.com/watch?v=mSFjvR-2zCI]

== Installation ==
1. Upload the directory of the Post Pay Counter in your wp-content/plugins directory; note that you need the whole folder, not only the single files.
2. Activate the plugin through the "Activate" button in the "Plugins" page of your WordPress.
3. Head to the configuration page first. The plugin already comes with a predefined set of settings, but you may want to set it up to better suit your needs.
5. That's it, you are done! You can now check the stats page to browse all the countings.

== Frequently Asked Questions ==
= You said I could pay per visit. How do I do that? =
There's an [apt tutorial here](http://postpaycounter.com/pay-writers-per-visit-wordpress). However, note that Post Pay Counter does not keep track of visits, it can only keep it in mind when computing the payment. You either need to have a plugin who keeps track of visits, and put the post_meta name of the field in which it stores the visits (must be a number), or get the PRO version of Post Pay Counter and use your Google Analytics account to get visits data.

= I installed the plugin but it does not show up in the menu. Also, if I go to the settings page, it says I am not authorized =
That is probably due to a permissions manager plugin you have on your blog. Check that capabilities *post_pay_counter_access_stats* and *post_pay_counter_manage_options* are correctly assigned and working.

= Can I pay for BBPress contents? =
It is indeed possible to pay for BBPress topics and replies with Post Pay Counter.

In *Post Pay Counter > Options > Miscellanea > Allowed post types*, make sure you have *Topic* and *Reply* ticked, and those types of contents will be included in countings.

= I don't want errors to be logged =
Set to *false* the constant *PPC_DEBUG_LOG* in *post-pay-counter.php*, it is located at line 44.
From this: *define( 'PPC_DEBUG_LOG', true );*
It must become: *define( 'PPC_DEBUG_LOG', false );*

== Screenshots ==
1. Post Pay Counter settings page
2. Use the tooltips beside each field to know what you can do with them
3. Post Pay Counter general stats (i.e. all author are shown). The provided datapicker allows to edit the time range and select the wished stats
4. Post Pay Counter per author stats. Datapicker avaiable here, too
5. The tooltip with all the counting details

== Changelog ==
= 2.602 (2016/04/29) =
* Fixed: mispelled cache key resulting in poorer performance.
* Fixed: wrong pagepath on "Insert valid license for automatic updates" link.
* Fixed: expired license notice displayed on all addons, even not yet expired ones.

= 2.602 (2016/04/17) =
* Fixed: PHP notices with PRO bonus payment enabled.
* Fixed: possible PHP Warning in case of notifications error.
* Tweak: stripping PHP ?> closing tag.

= 2.601 (2016/04/04) =
* Fixed: issues with memcached and other parmanent caching systems - settings changes didn't affect stats.
* Fixed: possible PHP errors with PHP 7.
* Tweak: using local time format.
* Tweak: using WP checked(), selected() and disabled() functions.

= 2.600 (2016/03/30) =
* Huge **performance improvements**! Basing on our tests, with all counting types enabled (basic, words, visits (postmeta), comments and images), we managed to load 6500+ posts in around 10 seconds (overall stats disabled).
* New: option to avoid making post titles clickable in stats (off by default, improves performance).
* New: option to avoid making super-cautious spaces parsing in word count (off by default, improves performance).
* New: option to avoid display of payment tooltips in stats (on by default).
* New: making use of WP_Object_Cache. If you use a permanent cache plugin, part of PPC requests will be cached as well.

= 2.518 (2016/03/16) =
* [New addon released](http://postpaycounter.com/user-roles-custom-settings?utm_source=wprep&utm_medium=link) to set custom settings per user role!
* Fixed: admins override permissions feature didn't check the user role, but manage_options capability (so non-admins who could manage_options would override permissions).
* Fixed: PHP warning due to missing argument.
* Fixed: addons page would display "Array" and no addons in some sites.
* Fixed: saving user settings could overwrite past settings.
* Fixed: settings import/export did not work cross-user.
* Tweak: settings import/export only considers settings which are different from general.
* Tweak: personalize users list can take up more space, so more users fit in it without scrolling.
* Tweak: allowing for faster settings retrieval.
* Tweak: new actions and filters.

= 2.517 (2016/02/21) =
* Fixed: hour:minute:second date format wrongly displayed.
* Tweak: addons list is now displayed even if network requests are not working, and anyway is only updated every two days.

= 2.516 (2016/01/23) =
* Fixed: issue with user settings not saving in certain circumstances.

= 2.515 (2016/01/18) =
* Fixed: people with PRO version prior to 1.5.9.1 had problems in activating/deactivating their addons license.
* Tweak: moved promotional boxes in the Options page down below all important boxes.
* Tweak: deleted old lang files.

= 2.514 (2016/01/10) =
* New: it's now possible to control what is displayed in the stats page for each payment criteria. You can display just the counting number, just the payment value, both or completely hide a column but still have the payment criteria active, and you can even personalize this per-user!
* New: when personalizing a user settings, only settings that have a different value from general settings are stored in the database. This allows for less data to be stored; moreover, when general settings are updated, users don't retain outdated settings, but all settings that have not been specifically changed for them, follow the general ones. This only applies to newly-personalized user's settings.
* Tweak: hidden license box when personalizing user settings, and moved it under the Personalize settings box in the general options.
* Tweak: updated italian translation.

= 2.513 (2016/01/07) =
* New: added option to allow admins to override all permissions. This applies only if they don't have specific personalized settings.

= 2.512 (2015/12/18) =
* Fixed: notification dismissing not fading out immediately but only at subsequent page load.
* Fixed: notifications being displayed on all admin pages instead of just on plugin pages.
* Fixed: new notification not being displayed.

= 2.511 (2015/10/10) =
* New: all addons are now compatible with the free version alone, no need to get the PRO if you just need features from another addon (we moved to a [new site](http://postpaycounter.com)).
* Tweak: sped up tooltip generation.
* Tweak: removed penguins logo in stats/options page.

= 2.510 =
* New: added Turkish translation (Kamer DINC).
* New: added Czech translation (Jiří Kučera).
* Tweak: backend improvements (new filters/actions/stuff like that).

= 2.509 =
* Fixed: last update broke localization.

= 2.508 =
* Fixed: PHP warnings when payments consisting of only Bonus would be done.
* Fixed: (hopefully for real): possible fatal error due to too many redirects on update.
* Fixed: when selecting a time range, the end time doesn't go to the day after the selected one any more.
* Tweak: changed text domain to *post-pay-counter* to grant compatibility with WP Language Packs.

= 2.506/2.507 =
* Fixed: PRO shortcode wouldn't exclude selected columns in detailed stats.

= 2.505 =
* Fixed: problems in saving custom visits counter callback function.
* Fixed: detailed stats generation not working in HHVM environments.

= 2.504 =
* Fixed: possible fatal error due to too many redirects on update.

= 2.503 =
* Fixed: word counter wouldn't count one-char words.
* Fixed: correctly handling of &nbsp; that wouldn't be counted as spaces as resulting from strange behavior of the editor.
* New: install procedure now grants by default all permissions to administrator by personalizing their settings (the user id of the user who installs the plugin is taken).
* Tweak: new PHP method that generates stats table tbody. That's a public one that can by used by any implementation (for example, is used several times in the PRO).
* Fixed: layout broken in user settings page, with links at the top being smushed in the upper-right corner. 
* New: added pot files to translate plugin in whatever language.
* Tweak: hiding the *Filter by user role* feature in stats page if user doesn't have the permission to see other people's stats.
* Tweak: on install, notifications issued before install date are all hidden in bulk.
* New: added Dutch translation (Elza van Swieten).

= 2.502 =
* Fixed: after last updated PRO version Analytics visits wouldn't show up.

= 2.501 =
* New: stats countings now display amounts that should be paid (is a post has 300 words and the upper payment limit is 200, the counter will show 200). Clicking on the amount will display the real total amount.
* Tweak: preventing (for 6 hours) notifications update if the request generated an error.
* Fixed: couple of non-static methods called statically.

= 2.500 =
* Fixed: general stats would not display all needed columns when users had different counting types enabled.
* Fixed: notification dismissing would log an error.
* Tweak: when a counting type is disabled for a user, data related to that cnt type in general stats is shown as "N.A.".
* Tweak: notifications remote request timeout decreased to 2 seconds.
* Tweak: extended notifications transient validity (2 days).
* Tweak: logging errors in HTTP notifications list request.

= 2.492 =
* Tweak: possible to select future dates as stast end times (allows to see future scheduled posts).

= 2.491 =
* Tweak: new actions needed for the [Facebook addon](http://www.thecrowned.org/facebook-pay-per-social-interactions-shares-likes-and-comments?utm_source=wprep&utm_medium=link&utm_campaign=ppcp_fb).
* Tweak: notifications processing only made in wp-admin, just as a bit of speed-up.

= 2.490 =
* Feature: possible to filter stats view by user role: select a user role and get stats only for users belonging to that one.
* Fixed: prevent PHP warning if website can't contact developer server to check for notifications.
* Tweak: ensuring logged plugin errors are automatically deleted after 20 days.

= 2.482 =
* Fixed: fatal error in PRO payment history windows.

= 2.481 =
* Fixed: last update would break PRO Analytics feature.

= 2.48 =
* Feature: possible to specify a callback function for visits counting, instead of a postmeta (grants compatibility with Post Views Counter and [more custom counting plugins](http://www.thecrowned.org/pay-writers-per-visit-wordpress)).
* Fixed: word counting problems for non-latin characters.
* Fixed: (this time for real) notifications would be displayed on all admin pages although they were not supposed to.

= 2.47 =
* Fixed: word count would sometimes miss a word (the last one).
* Fixed: notifications would be displayed on all admin pages although they were not supposed to.
* Tweak: updated Italian and German translations (thanks [Julian](http://inside11.de/)).

= 2.46 =
* Feature: now possible to include gallery images in images counting (disabled by default, go to Counting settings > Images payment to enable it).
* Feature: introducing plugin notifications system.

= 2.45 =
* Fixed: misaligned stats columns if users had different counting types enabled.

= 2.44 =
* Fixed: datepicker would misbehave and not let select correct dates.

= 2.43 =
* Fixed: if a maximum payment threshold was set with payment only when the threshold was reached, and no posts across all authors reached the threhsold, some warnings would be shown with no explanation of the problem.
* Fixed: PRO version payment bonus not showing in the stats (although was counted for payment).
* Fixed: options page style showing messed up on some sites.
* Tweak: dinamically generating overall stats (if some payment types are disabled, they are not displayed).
* Tweak: stats generation is a bit less memory demanding.
* Fixed: warning again for users with no counting types enabled.
* Tweak: updated italian translation.

= 2.42 =
* Fixed: warnings in shortcoded (PRO) and maybe admin stats if current user didn't have any counting types enabled.

= 2.41 =
* Fixed: overall stats not displaying data.

= 2.40 =
* New: centralized control of counting types - makes code easier, hopefully faster, and simple to hook for who wants to integrate with the plugin.
* Tweak: welcome page css not being loaded on all wp-admin pages.
* New: Pengu-ins logo added on plugin pages.
* New: addons page (new addons coming!)

= 2.35 =
* New: words included in any HTML tag with class *ppc_exclude_words* is automatically excluded from word counting. Doesn't handle nested tags, i.e. < div class="ppc_exclude_posts">some content < div class="nested">nested content</ div> this will already be counted</ div>.
* Fixed: blockquotes exluding from words counting would not work correctly with more than one blockquote (would not count even words in the middle of blockquotes).
* Fixed: furthest reaching stats display starting date would not keep in account selected custom post types, would only get first *post*.
* Tweak: new js function ppc_zones_manager for options zones adding/removal (saves lines of code).
* Tweak: $ppc_global_settings['current_page'] holds current page name.
* Tweak: PPC_Error objects pass debug $data to WP_Error.
* Tweak: updated italian and german (Thanks Julian!) translations.
* Tweak: moved screenshots to /assets folder = smaller zip file!

= 2.34 =
* New: Welcome and Changelog pages.
* New: possible to disable overall stats display - performance matters!

= 2.33 =
* New: plugin logged errors are automatically deleted after a month - the deletion happens once a day.
* Fixed: *ppc_options_fields_class* methods now declared *static*, avoiding PHP Strict notices of non-static methods being called statically blah blah blah.
* Fixed: start time stats limit in datepicker would only consider posts instead of selected post types.

= 2.32 =
* New: possible to include private posts in stats.
* Tweak: improved update method.
* Tweak: updated German translation.

= 2.31 =
* Fixed: German and portoguese translation had not been committed, sorry.

= 2.3 =
* Fixed: some translastion would not show up (mainly stats cols header).
* Tweak: minor performance enhancements due to tooltips for posts only being generated for detailed stats.
* New: German translation (Julian Beck).
* New: Portoguese (pt_PT, pt_BR) translation (Marco Dantas).
* Fixed: if using names different from their their values for user roles, clicking on a user role in the Personalize settings box would always return an empty result.
* Fixed: typo in custom default time range tooltip (*rime range* instead of *time range*).
* Fixed: grammar mistake in personalize settings box (*payed* instead of *paid*).

= 2.29 =
* Fixed: word counting not working for non-latin charsets due to PHP bug. Restoring old counting method from v. 2.26.
* Fixed: still some minor issues with time zone differences.
* Fixed: username for currently editing personalized settings would not be displayed.
* Fixed: when generating stats, user personalized settings for *allowed post statuses* would not be used; general settings would be used instead.

= 2.28 =
* Tweak: various performance enhancements (should speed up especially large sites stats).
* Fixed: stats display would experience a time delay when selecting a time range due to time zone and date generation problems. Now posts up to the last second of the requested end time are selected.
* Tweak: in author detailed stats, if the viewer has the capability *edit_post*, the post link is to the post editing page (it is faster to fetch than the public permalink). If the user can't edit posts, they still see the public permalink.

= 2.27 =
* Tweak: changed localization slug (from *post-pay-counter* to *ppc*).
* Tweak: using PHP function str_word_count instead of custom function (for word counting).
* Tweak: various filters and actions changes.
* Tweak: added localization to options page meta boxes titles and stats table column headings.
* Tweak: partly unified images and comments counting routine (and moved to PPC_counting_stuff class).
* Tweak: other minor performance improvements.
* Fixed: images counting would count one more.

= 2.26 =
* Fixed: Italian translation wouldn't show.

= 2.25 =
* Fixed: blockquotes exclusion from word countings didn't work.
* Fixed: preventing notice about *ppc_filter_user_roles* from showing due to last update.

= 2.24 =
* Tweak: **dramatic speed increase** on websites with lots of users.
* New: Italian translation (Stefano Ottolenghi).

= 2.23 =
* New: overall stats show total count for words, visits, images and comments.
* Fixed: settings would not get personalized user's settings in some cases.
* Fixed: error handling in installation.
* Tweak: improved settings caching.

= 2.22 =
* New: possible to clear error log.
* Tweak: some errors do not get logged anyway (like *empty_selection*).
* Fixed: if payment threshold was set, posts which did not meet it would not count as written posts in stats.
* Fixed: error log option is not autoloaded by default now.
* Fixed: unexpected output during activation notice.

= 2.21 =
* Tweak: payments types (basic, words, visits, images, comments), when unchecked, have their details hidden.
* New: error handling class and debug features. Errors get logged when PPC_DEBUG_LOG is true (some errors get logged anyway) and detailed errors data is shown when PPC_DEBUG_SHOW is true (lines 42-43 of post-pay-counter.php). Not all errors have been updated to use the class yet. See the errors log in the Options page.
* Fixed: update had broken tooltip info feature.
* Fixed: problems when the image/comments lower threshold was set but the upper threshold was left to zero.

= 2.2 =
* Feature: import/export settings from a plugin installation to another.
* Fixed: personalizable users list duplicated and triplicated usernames.
* Fixed: problems with computing payment for images and comments.
* Change: version number is now castable to float - no more 2.1.2, became 2.12.

= 2.1.2 =
* Fixed: images and comments counting had problems with their counting systems and didn't update properly.
* Fixed: PRO version gets reactivated after PPC is updated.

= 2.1.1 =
* Fixed: new installations would add personalized user settings in place of general ones.

= 2.1 =
* Added *System info* page for easier troubleshooting. Please include those data when asking for support.
* PRO version available: added a notification & updated link.
* Installation procedure now grants by default all permissions to administrator by personalizing their settings (the user id of the user who installs the plugin is taken).
* Fixed: misc settings would not save.
* Fixed: added css dependency for *wp-admin* without which plugin's css may be overriden.

= 2.0.9 =
* Various markup fixing, among which a non-closed div in stats which messed up with the page layout.
* Fix to grant compatibility with PHP < 5.3.

= 2.0.8 =
* Fixed a problem in which new installations wouldn't be able to access plugin pages due to permissions problems.
* Changed menu slugs: they are now ppc-options and ppc-stats.

= 2.0.7 =
* Update class only loaded if update is going to be run.
* Added link in detailed stats to go back to general stats.

*Under the hood:*

* Stats data are hold in subarrays: normal_countings, normal_payment and tooltip_normal_payment.
* Default stats time range now selected by an apt function.
* format_stats_for_output returns array('cols' => array(), 'stats => array()).

= 2.0.6 =
* Fixed a bug in which detailed settings were showing all posts regardless of the user.

= 2.0.5 =
* Fixed a bug in which, when users weren't allowed to see others' general stats, no stats would be shown but "Array" would be displayed in every field.
* Added update procedure (even though there's nothing to be updated yet but the version number)

= 2.0.4 =
* Fixed a bug in which plugin pages permissions were not put in pratice correctly.

*Under the hood:*

* Stats datepicker js moved from inline to external file.
* Slight changes in the counting data structure.
* New highest-level method for generating stats.

= 2.0.3 =
* Fixed a bug in which authors' totals in general stats and total payment in overall stats were not shown right when using zonal systems.

= 2.0.2 =
* Fixed post statuses filter not working, pending revision and future scheduled were selected regardless of settings.

= 2.0.1 =
* Localization slug added to localization functions calls. The plugin can now be translated.
* Fixed a bug in which posts published on the time frame boundaries days would not show up in stats.

= 2.0 =
**IMPORTANT NOTICE: Versions 2.0 or higher need to be reinstalled** if you had a previous version due to its different settings storage system and the availability of new features. Also, the **following features are currently missing**: post payment bonus, trial settings, csv esport, full multisite integration, word count in post list. They will be added soon. If you need one specifically, let me know in order to make up a priority list.

* Almost complete plugin redesign and code refactoring which should give dramatic speed improvements. Less data is stored in the database, making requests lighter.
* The plugin is now fully extensible, check the list of hooks and filters.
* Supports localization.
* Words and visits are not mutually esclusive counting types anymore.
* Plugin's visits counting method is not available anymore. If you use some other plugin to keep track of visits, you can specify its postmeta and Post Pay Counter will use that. Post Pay Counter PRO (soon available) will allow use of Google Analytics.
* Images and comments can now use both incremental and zones payment system.
* Up to 10 zones are allowed now.
* Feature to allow payment only when certain threshold is met.
* Settings save is now AJAX working.
* Post featured image can now be counted as well.

A paid addon to Post Pay Counter, PRO, will be released soon, adding more new features.

= 1.3.6 =
* Fixed fatal error when on a post save the counting entry was removed.
* Should have fixed the unexpected output on installation.

= 1.3.5 =
* Fixed a fatal error that occurred due to PHP 5.4.x.

= 1.3.4.9 =
* Fixed a problem where when using the zone counting system and only 5 five zones, the last zone was not taken into account.

= 1.3.4.8 =
* Some permissions settings were not taken into account when showing stats.

= 1.3.4.7 =
* PayPal email addresses were not saved, fixed now.

= 1.3.4.6 =
* Fixed a PHP warning.

= 1.3.4.5 =
* Fixed a problem that prevented personalized counting system and manual trial enable from working.
* Currently installed version is now shown on the upper-right corner in the plugin Options page.

= 1.3.4.1 =
* Solved more multisite-related problems that excluded some users from countings.
* Fixed an issue that set to zero the counted words when a post page was viewed and the counting type visits was not enabled. 

= 1.3.4 =
* If plugin table or its default settings are missing, they are automatically added when either the options page or the stats page are loaded.
* Update procedure now works with multisite - can not believe this was not introduced when the multisite capability was introduced!
* I took the chance to redesign the class structure of the plugin, using class inheritance and making everything cleaner.
* The debugging feature has been moved to the *post-pay-counter.php* file.
* Every time the global *$post* variable is used, is now casted to object: in some cases I found it being an array and breaking everything.

= 1.3.3 =
* Little problem (not so little, since prevented activation) with user roles permissions is fixed now! If you were experiencing the has_cap() fatal error, it should be ok now. If you were experiencing the array_intersect warning, that should be fixed too. For the latter, should it persist, try to save options and reload the page, and see if that solves.
* For the future (or the present, who knows), I have added a debug functionality that will make troubleshooting problems on my part far easier than now. It can be enabled and disabled at will, though not via a user interface as of the present release. Default option is disabled. Instructions to enable it are in the FAQ.
* Unexpected output during installation is now logged in the database as a wp_option called *ppc_install_error* and included in the debugging data.

= 1.3.2 =
* Without noticing, I was using a PHP 5.3 function that, of course, triggered a fatal error almost everywhere. Sorry!

= 1.3.1 =
* Hopefully fixed a bug that, after the update, prevented the new user roles permissions for the plugin pages to work properly.
* Fixed a uninstallation bug that prevented the ppc_current_version option from being deleted.

= 1.3 =
* Some options contained in the Counting Settings box can not be personalized by user anymore. This allows the counting routine to run much faster, and it was necessary to logically differentiate between settings that apply to everybody and the ones that may be useful to personalize. Those options, if personalized before this release, will not be taken into account anymore: the plugin will use general ones instead.
* It is now possible to mark as paid counted posts. Along each post in the stats by author there is a checkbox that allows to do that; it works with AJAX, so that there is no need to reload the page after a park is marked as such. The plugin also keeps a payment history, so that, if over time the payment for a post should change, the plugin will show you how much you have already paid and how much you still have to pay. The control is only available to administrators, other users can only see how much a post was paid (only if the related permission is checked).
* Post of a post type different than the default one can now be included into countings (including pages). Post types to include can be chosen in the Options page from a list of the registered ones, and in the stats a column will show the post type the displayed posts fit in. The post types to include can not be personalized by user.
* Choose the user groups of which you would like posts to be counted from a convenient list in the Options. In the general stats, the user group will be displayed.
* Define what user groups should be able to view and edit plugin settings and browse through the stats page.
* Update procedure changed, to line up with new Wordpress standards (we now store the installed version in a wp_option in the database and compare it with the most recent one, hardcoded in the plugin files).
* It is now possible to exclude quotations from posts counting routine: only award authors for what they write themselves.
* It is now possible to define up to 10 zones when using the zones counting type, with the second five being optional.
* It is possible to define how often payment usually takes place, so that in the stats page it will automatically be selected the right time range accordingly.
* If user is allowed to, they can now clearly see how the payment was computed by a convenient hover tooltip.
* Future scheduled posts can now be excluded from countings.
* Users are now shown by their chosen display name and not by nickname.
* Only 250 usernames are now shown for personalizing settings due to hanging in blogs with very large databases. To personalize settings for other users, you can put their IDs in the userid parameter in the URL.
* No more problems in pressing *Enter* to update settings, it works!
* Deleted the old stats permission: with the new free time frame picker, it became useless (already a couple releases ago...).
* Split in a different class the functions used to generate the HTML form fields in the options and everything related to that.
* General speed up.

= 1.2.2 =
* Word counting is now more precise.

= 1.2.1 =
* Fixed a problem with the installation which prevented the new functions to work properly because of missing database columns.

= 1.2.0 =
* The plugin now has its own toplevel menu item: it is called Post Pay Counter and is located at the bottom of the admin menu, with the stats and options pages being accesible through it.
* Introduced the minimum fee capability. Admins can now set a minimum amout of money that will be credited to posts when their ordinary payment would be too low (there are options to define how much low is).
* It is now possible to show the posts word count directly in the WordPress post list as a column.
* In the stats page, if the user can, when the payment has bonuses associated with it they are now shown on mouse overlay.
* The exported CSV files now have a little introduction with the site name and address and also report the total counting at the bottom (total payment and total posts).

= 1.1.9 =
* Changes to counting routine grant wider compatibility: Greek charachters are now supported.

= 1.1.8 =
* Bug from previous release made impossible to update settings because of two MySQL columns missing. Should be fixed now.

= 1.1.7 =
* When uninstalling it now checks for table/columns existance while already into the uninstallation process, not before it.

= 1.1.6 =
* Fixed a bug that prevented the installation process to work correctly due to MySQL errors.
* Fixed a JS error in the jQuery datepicker when no posts were available.

= 1.1.5 =
* Fixed a bug that prevented comments and images bonuses to be awarded when using the unique payment system.

= 1.1.4 =
* Manually creating a post meta named *payment_bonus* allows to award a bonus to posts. Bonuses are then shown in the stats page in brackets and with a smaller font, though the admin can decide to disable the function or hide the bonuses.
* Fixed a bug that triggered a fatal error when updating settings without having them in the database (default case of switch).

= 1.1.3 =
* Changed view counting method, it could trigger problems is headers where already sent before the plugin got in. It's now using an AJAX request to set the cookie.
* Minimal improvements in in the view counting method.

= 1.1.2 =
* Stats are not generated during installation anymore. This is to prevent the plugin hanging on activation due to large databases. If you still want to have old stats, use the *Update Stats* box in the Options Page.

= 1.1.1 =
* Made the install process lighter.

= 1.1 =
* Multisite compatibility added.

= 1.0 =
The plugin is highly derived from Monthly Post Counter, which has almost been re-written from scratch to optimize performance, include new tasty functions and carry many many bug and security fixes. Look has been restyled too, using wordpress metaboxes for the settings page.

These the changes from the old Monthly Post Counter:
* Added possibility to set different settings for each user. Stats which do involve different settings are shown only to the writer itself or the admins by default.
* The admin can define permissions for old, overall and other's stats (general and detailed), csv exporting and special settings in countings.
* The counting type can be chosen between visits and words (the latter used by default), and during the installation all the posts in database are selected and updated.
* Two counting systems are now avaiable: the zones one and the unique payment one.
* Stats time range is now customely selectable with a jQuery datepicker.
* Added possibility to pay images after the first one with a little award.
* The admin can define a set of trial settings that will be applied to new users.
* The plugin now records the words number instead of the payment value, this allows the countings to be update immediately without any post-all update.
* Tooltips added all over the options page.
* Ability to update all the posts with a single action added in the options page.
* A new box shows stats from the first published post, they are shown as "overall stats".
* Cool jQuery effects added to show/hide options.
* Improvements in csv encoding shortcomings.
* Uninstall file added instead of the deactivation method.
