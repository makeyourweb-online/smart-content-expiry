=== Smart Content Expiry ===
Contributors: makeyourwebonline
Donate link: https://buymeacoffee.com/makeyourweb
Tags: expire, content expiration, hide content, schedule content, shortcode, redirect, time-based content  
Requires at least: 5.0  
Tested up to: 6.8 
Requires PHP: 7.0  
Stable tag: 1.0.2
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

Schedule content expiration and decide what happens next: hide it, replace it with a message, or redirect users. Perfect for time-sensitive content.

== Description ==

**Smart Content Expiry** allows you to set expiration dates for posts, pages, and inline content using a shortcode.  
Once expired, the plugin can:

- Automatically hide the content  
- Replace it with a custom message  
- Redirect visitors to a different URL

**Features:**

- Expire full posts and pages with a selected date and time  
- Inline content expiration using `[smart_expire]` shortcode  
- Admin page to list all expiring content  
- Lightweight and easy to set up  
- Ready for translations (internationalized)

**Use cases:**  
Promotions, announcements, event listings, limited-time offers, time-bound disclaimers, and more.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory.  
2. Activate the plugin through the 'Plugins' menu in WordPress.  
3. Edit a post or page to set an expiration date and choose an action.  
4. Use the shortcode for inline control:  
   `[smart_expire expires="YYYY-MM-DD HH:MM" action="replace" message="Expired!"]This content will disappear after the date[/smart_expire]`

== Frequently Asked Questions ==

= What happens when a post expires? =  
Depending on your chosen action, the content is either hidden, replaced, or redirected.

= Can I use it in shortcodes? =  
Yes! Use `[smart_expire]` with options like `expires`, `action`, `message`, and `redirect`.

= Where can I see a list of expiring posts? =  
Go to **Tools > Expiring Content** in the admin area.

== Screenshots ==

1. Meta box to configure expiry for posts/pages  
2. Admin page listing expiring content  
3. Example of inline content that disappears after a date  

== Changelog ==

= 1.0.2 =
* Added admin page for expiring content  
* Improved shortcode handling and admin interface  
* Translations support

= 1.01. =
* Initial release

== Upgrade Notice ==

= 1.0.1 =
Adds admin page to manage expiring content and supports shortcodes.