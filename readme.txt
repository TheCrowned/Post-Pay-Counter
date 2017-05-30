=== Post Pay Counter ===
Contributors: Ste_95
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SM5Q9BVU4RT22
Tags: counter, authors, payment, revenue sharing, stats, multi author, post management, post
Tested up to: 4.7.2
Stable tag: 2.725
Requires at least: 3.7

Easily handle authors' payments on a multi-author blog by computing posts' remuneration basing on admin defined rules.

== Description ==
Easily calculate and handle authors' pay on a multi-author blog. Set up the wished payment criteria and let the plugin compute posts payment. Stats are immediately be viewable. Both a general view with all users and a specific one for each author are possible. It can easily help you implement a revenue sharing/paid to write model for your business.

[Plugin HOMEPAGE](https://postpaycounter.com)

**Features include:**

* Pay per post, word, visit ([tutorial](https://postpaycounter.com/pay-writers-per-visit-wordpress?utm_source=wprep&utm_medium=link)), image and comment (not mutually exclusive).
* Pay with an incremental system (eg. each word is €0.01 => 100 words = €1) or with a zonal one (eg. from 200 to 300 words/visits it’s €2.00, up to 10 zones).
* No account needed. Data is yours, no need to sign-up to anything really.
* Old stats availability. View posts countings since the first written post, disregarding the plugin install date. A fancy date picker lets you shift between days and select the desired range, or pick a ready choice (such as *This month*, *Last month*...).
* Responsive and sortable stats: optimized stats page for mobile devices and sortable stats table.
* Personalize user's settings, so that special settings only apply to a particular user. Different settings can be made apparent in the stats or hidden depending on your needs.
* Customizable permissions to prevent your users to see stats and use functions they are not supposed to.
* Extend with your own custom implementation through hooks, filters and special API features ([learn more](https://postpaycounter.com/add-custom-payment-types-post-pay-counter-stats?utm_source=wprep&utm_medium=link)).
* And... works with custom post types, narrow your payments only to chosen user groups, supports pagination, and even more!

[GitHub repository](https://github.com/TheCrowned/Post-Pay-Counter/) (wanna join us coding?)

= Integrate with Analytics/Adsense and pay with PayPal =
The [PRO version](https://postpaycounter.com/post-pay-counter-pro?utm_source=wprep&utm_medium=link&utm_campaign=ppcp) includes Analytics visits payment, Adsense Revenues sharing, payments management and PayPal payments. Among other stuff, it also allows to keep a convenient log of past payments and to display stats in public pages through a shortcode. 

= Integrate with Facebook =
The [Facebook addon](https://postpaycounter.com/facebook-pay-per-social-interactions-shares-likes-and-comments?utm_source=wprep&utm_medium=link&utm_campaign=ppcp_fb) allows to pay posts for the Facebook shares and comments they receive.

Browse [all extensions](https://postpaycounter.com/addons?utm_source=wprep&utm_medium=description&utm_campaign=ppc_addons).

= Available languages =
Post Pay Counter has been translated into the following languages:

* English
* French (Alexandre Mark)
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

You can have a look at the [apt tutorial](https://postpaycounter.com/how-to-pay-per-bbpress-topics-and-replies/) for more details.

= I don't want errors to be logged =
Set to *false* the constant *PPC_DEBUG_LOG* in *post-pay-counter.php*, it is located at line 44.
From this: *define( 'PPC_DEBUG_LOG', true );*
It must become: *define( 'PPC_DEBUG_LOG', false );*

== Changelog ==
= 2.725 (2017-05-30) =
Feature: stats will always display ordered by the column you picked the last time. Can be disabled.
Feature: possible to change how many digits payment figures are rounded to.
Tweak: added notice on top of stats table if post stats caching is active.
Fixed: stats ordering not working in author view.
Fixed: sorting parameters not included in page permalink.
Fixed: counting settings could not be saved if the visits payment callback value had an invalid callback, even if visits payment was inactive.
Fixed: possible PHP notice in cache class.
Fixed: PHP notice in BuddyPress addon if no posts were to be displayed.
Tweak: several enhancements under the hood.

= 2.720 (2017-05-16) =
* New: post stats are now cached for one day. This **speeds up stats page loading by roughly 50%**! The feature can be disabled in case of issues.
* Tweak: updated plugin hooks for compatibility with the new Request Payment addon.
* Tweak: improvements in update routine.

= 2.716 (2017-05-01) =
* Tweak: hidden Error Log box from non-general options pages.
* Tweak: better hiding of general options boxes from non-general options pages.
* Fixed: License box not showing localized strings.
* Fixed: tooltip line breaks not shown on certain browsers/systems.

= 2.715 (2017/03/23) =
* Fixed: roles and users picker in stats page would display roles/users set not-to-show-up in stats as well.
* Fixed: *This week* choice in stats time range would not work correctly.
* New: when activating addons, license keys can now be displayed alongside the addon name.
* Tweak: improved error message of *License is not a PPCP_License Object* error when activating addons.
* Tweak: updated italian locale.
* Tweak: improved performance when clearing cache.
* Tweak: minor improvements.

= 2.714 (2017/02/11) = 
* Fixed: future days not pickable in stats date picker (even if there were future scheduled posts).

= 2.713 (2017/02/09) =
* Fixed: *This year* option not working correctly in stats date picker.

= 2.712 (2017/02/04) =
* Fixed: possible warning in stats page for users with PRO version.
* Fixed: Import/Export settings box may not work correctly the first time user settings were customized.
* Fixed: stats time end not going up to today, causing issues with Publisher Bonus addon (for example).
* Fixed: *Total is X. Displayed is what you'll be paid for* message on hover would not display the total amount.
* Tweak: improved stats page loading time.

= 2.711 (2017/01/22) =
* Fixed: possible error in stats page (*undefined counting_types_object index*)
* Fixed: changing the display setting for payment criteria did not have any effect.
* Fixed: improved styling of upper-right corner of stats page.
* Fixed: declared as static some install methods.
* Tweak: updated italian and turkish translations.

= 2.710 (2017/01/14) =
* Feature: dropdown to select a user to view stats for after picking a user role in stats page.
* Feature: supporting each *post* to have different payment criteria enabled. This allows to effectively selectively enable/disable payment criteria for each category through the [Category Custom Settings](http://postpaycounter.com/category-custom-settings/), for example, and also allows (in theory) to set up per post settings.
* Tweak: minor performance improvements in stats generation.

= 2.708 (2017/01/04) =
* Fixed: broken stats time range picker.

= 2.707 (2017/01/01) =
* Fixed: issue with dates due to new year.

= 2.706 (2016/12/30) =
* Fixed: improved error handling.
* Tweak: updated French translation.
* Tweak: improved *Payment systems* section layout for payment criteria.
* New: addon [BuddyPress](https://postpaycounter.com/buddypress/) 

= 2.705 (2016/12/08) =
* Fixed: compatibility with PHP < 5.5 (array_column() error).
* Tweak: (PRO users) header name is not *Post Pay Counter - Stats*, but whatever you have set your menu label to be named like.
* Tweak: changed *Total payment threshold* label in metabox.

= 2.704 (2016/11/08) =
* Fixed: log data left in stats page.

= 2.703 (2016/11/08) =
* Fixed: fatal error in stats if no posts were to be displayed.

= 2.702 (2016/11/08) =
* Fixed: broken mark as paid for PRO users.

= 2.701 (2016/11/08) =
* Fixed: fatal error in stats for few people.

= 2.700 (2016/11/07) =
* Feature: stats page can now be sorted! Just click on a column and the whole table will be sorted for that column.
* Feature: stats page supports pagination! Default number of items per page is 300, but you can change it in the Screen Options section (upper-right corner).
* Feature: stats columns can now be hidden through the Screen Options section in the upper-right corner.
* Fixed: stats time picker would not allow to pick future days as end time, making the *Count future scheduled posts* uneffective.
* Fixed: payment tooltip not displayed for any user if one user was not supposed to have it.
* Tweak: automating cache purging for css and js files using WP file versions.
* Tweak: sorted list of active licenses in License Status box.
* Tweak: updated Italian translation.
* Tweak: minor performance improvements.

= 2.623 (2016/10/10) =
* Fixed: yet more issues with default stats time range.
* Tweak: minor improvements.

= 2.622 (2016/10/09) =
* Fixed: default stats time range allowed to pick two choices.
* Fixed: minor fixes.

= 2.621 (2016/09/21) =
* Fixed: fatal error on stats page on certain server setups (lacking PHP calendar plugin).

= 2.620 (2016/09/13) =
* New: time range dropdown choice in stats page to quickly pick the desired time frame.
* New: added *All time* to default time range choices.
* New: added *Last month* to default time range choices.
* New: added *This year* to default time range choices. 

= 2.610 (2016/08/19) =
* Fixed: PHP7 issues as found by WPEngine compatibility checker.
* New: addon [Pay Per Character](https://postpaycounter.com/pay-per-character/) 

= 2.609 (2016/08/03) =
* Fixed: notices in stats page when no payment criteria were enabled for some users.

= 2.608 (2016/07/31) =
* New: French translation (Alexandre Mark).

= 2.607 (2016/07/19) =
* Fixed: last update broke stats countings (though nothing happened to payments).

= 2.606 (2016/07/18) =
* Fixed: notice in stats page when no payment criteria were enabled.
* New: released [Author Payment Bonus](https://postpaycounter.com/author-payment-bonus-manually-change-the-total-payout-to-authors/) and [Category Custom Settings](http://postpaycounter.com/category-custom-settings/) released!

= 2.605 (2016/06/19) =
* Fixed: issue with empty stats.

= 2.604 (2016/06/07) =
* Feature: include post excerpt in word count payment.
* Fixed: users having different allowed post statuses settings impacting general stats with wrong data.
* Fixed: fixed notice for unactive counting types on tooltip generation.
* Fixed: wrong error message displayed when no posts were selected.
* Fixed: several PHP notices in stats page when no posts reached the threshold.
* Fixed: PHP notice and some images not displayed in addons page.
* Tweak: updated italian translation.

= 2.603 (2016/04/29) =
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

... several old versions changes removed ...

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

== Screenshots ==
1. Post Pay Counter general stats (i.e. all author are shown). The provided datapicker allows to edit the time range and select the wished stats
2. Post Pay Counter per author stats
3. Post Pay Counter settings page
4. Stats responsive layout
5. The tooltip with all the counting details
6. Extensive documentation in options tooltips
