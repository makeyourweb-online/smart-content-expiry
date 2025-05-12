=== Smart Content Expiry ===
Contributors: makeyourwebonline
Donate link: https://buymeacoffee.com/makeyourweb
Tags: content expiration, expire post, schedule removal, auto-hide, post expiration
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Expire posts and pages automatically. Hide, replace or redirect content after expiry — no cron jobs or deletions.

== Description ==

**Smart Content Expiry** lets you automatically manage content lifecycle in WordPress.

You can schedule an expiration date for any post or page and define what should happen when that date is reached:

* Hide the content entirely
* Replace it with a custom message
* Redirect the visitor to another page or website

Ideal for:
* Time-limited promotions and announcements
* Expiring offers or seasonal content
* Redirecting outdated pages to newer content
* Ensuring stale information disappears automatically

Key features:
* Set an expiration date/time directly in the post editor
* Choose between hide / replace / redirect actions
* Add a custom expiration message or redirect URL
* Shortcode support: `[smart_expire expires="YYYY-MM-DD HH:MM"]...[/smart_expire]`
* Admin page listing all expiring content (Tools → Expiring Content)

No background processes or cron jobs — all logic runs when a visitor views a post.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the "Plugins" menu
3. Edit any post or page to set an expiry rule in the sidebar

== Frequently Asked Questions ==

= Does this plugin delete posts after expiry? =
No. It only hides, replaces, or redirects the content. Your posts remain in the database.

= Is there a shortcode to handle inline expiration? =
Yes! Use `[smart_expire expires="2025-12-31 23:59" action="replace" message="This content has expired."]Hidden content[/smart_expire]`.

= Can I bulk-set expiry for multiple posts? =
Not yet — but it’s planned for a future PRO version. Right now, expiry must be set per-post.

== Screenshots ==

1. Post editor settings for expiry
2. Admin listing of content with expiration dates

== Changelog ==

= 1.0.3 =
* Fixed nonce validation and naming inconsistency
* Added `wp_unslash()` before sanitizing user input (complies with WP Coding Standards)
* Improved escaping in admin UI and frontend output
* Optimized content listing query with `meta_key` and `posts_per_page` limit
* Confirmed compatibility with WordPress 6.8

= 1.0.2 =
* Improved shortcode behavior with redirects
* Minor UI text tweaks and translation support

= 1.0.1 =
* Initial public release

== Upgrade Notice ==

= 1.0.3 =
Security and performance improvements. Fixes input sanitization, optimizes expiry listings, and enhances WordPress Coding Standards compliance.