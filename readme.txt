=== Post Pay Counter ===
Contributors: Ste_95
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SM5Q9BVU4RT22
Tags: counter, authors, payment, revenue sharing, stats, multi author, post management, post
Tested up to: 4.6.1
Stable tag: 2.621
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

You can have a look at the [apt tutorial](http://postpaycounter.com/how-to-pay-per-bbpress-topics-and-replies/) for more details.

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
