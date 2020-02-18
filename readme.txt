=== Multiple Products to Cart - WooCommerce Product Table ===
Contributors: smshahriar, aikya, webfixlab
Tags: product table,products table,woocommerce product table,wc product table,multiple products
Requires at least: 4.0
Tested up to: 5.3.2
Stable tag: 2.1
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A truly lightweight and super FAST WooCommerce product table solution to add multiple products to cart at once.

== Description ==

Super FAST WooCommerce product table solution to add multiple products to cart at once. Great for accessories, restaurant or any WooCommerce shops, great for increase conversions.

A truly lightweight and simple yet powerful plugin without any unnecessary features, no huss fuss or no low page speed score!

**FEATURES**

* **NEW!** Modify the product table from your theme 
* Simple and **Variable** both products type supported
* Show any specific products using product ID
* Show any specific category items using category ID 
* Fully responsive to all modern devices
* Only in stock products will be listed
* Extremely fast to show a lot of products in a single page
* Truly lightweight, almost no impact on page speed score
* Actively developing and [taking feature requests](https://webfixlab.com/#contact-us "Taking Requests") right now

**SHORTCODE**
`[woo-multi-cart]`

**IMPORTANT**

* For quick support, contact us [here](https://webfixlab.com/#contact-us "Quick Support")

**Shortcode attributes**
limit = 50
orderby = date or title
order = desc or asc
type = simple or variable
ids = ID(s) of the products like 2568,2547
cats = ID(s) of product categories like 5,6,9

Please note that maximum 50 items will be listed per product table.

Some examples of the shortcodes.

`[woo-multi-cart limit=20 orderby="title" order="desc"]`
`[woo-multi-cart type="variable"]`
`[woo-multi-cart cats="5,6,9"]`
`[woo-multi-cart ids="2568,2547"]`

== Installation ==

1. Upload the plugin folder "multiple-products-to-cart-for-woocommerce" to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to **WooCommerce > Settings > Products > Multiple Products to Cart** to change button text and color.

== Frequently Asked Questions ==

= How can I change button text and color? =

Go to **WooCommerce > Settings > Products > Multiple Products to Cart**

= How can I modify the product table? =

The WooCommerce product table template can be overridden by copying it to `yourtheme/templates/listing-list.php`.

= Will it work on any theme or page builder? =

Yes, it will work fine on any standard theme or page builder like Gutenberg, Elementor, WPBakery Composer etc.

= Can I request for a feature? =

Sure, just send your request [here](https://webfixlab.com/#contact-us "Request Feature").


== Screenshots ==

1. Front-end View
2. Settings