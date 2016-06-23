=== Plugin Name ===
Contributors: cteitzel
Tags: encrypt, encryption, security, API, key, password,
Requires at least: 2.7
Tested up to: 4.5.3
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Lockr is the first API & Encryption key management service for WordPress, providing an affordable solution to secure keys used by plugins.

== Description ==

# API & ENCRYPTION KEY MANAGEMENT FOR WORDPRESS #

Lockr is the first hosted API & Encryption key management solution for WordPress, providing an affordable solution for all sites to properly manage API and encryption keys used by their plugins. Lockr's offsite key management solution protects against critical vulnerabilities, delivers best-practice security to help sites comply with many industry regulations, and provides a Defense in Depth approach to securing your data. And best of all, even though it delivers enterprise-grade key management, your first key and 1500 key requests are free! Learn more at http://www.lockr.io.

## Lockr Features: ##
Easy to configure and setup in WordPress
Safe and Secure offsite key storage
Works with any API and encryption key
99.9% uptime guarantee (SLA Available for Enterprise Customers)
Regular Backups
Multiple Region Redundancy
Backed by Townsend Security's FIPS 140-2 compliant key manager, your keys are secured to industry standards. 

## Lockr is the first key management service for WordPress. ##
More and more plugins are leveraging 3rd party APIs. To securely access these APIs, a token, secret key, or password is necessary. Until now, these highly sensitive keys were stored right in your database. We’ve seen a major need to secure sensitive data and communications by removing these API keys from your database, encrypting them, and storing safely in an offsite key vault. This limits the damage that could be done if your site is compromised or a developer has a local copy of your database. Lockr makes key management easy. Just install the plugin for WordPress, configure your account and begin securely storing your keys. Lockr provides patches for the major plugins used by hundreds of thousands of sites and with WP-CLI a single command will make sure your plugins use Lockr.

## Who is Lockr for? ##
Lockr is available for WordPress sites of all sizes. Easy to use for the novice site owner and advanced enough for the expert developer, Lockr secures web transactions and data at rest by protecting API and encryption keys.
For Site Builders: fill out a single registration form and you’re set. To use with other plugins, look for those that have Lockr available or use our patch library to update your favorite plugin to use Lockr.
For Developers: Lockr provides an easy to use framework to “get and set” keys from your custom plugin. Additionally, Lockr provides a simple to use encryption function, ensuring your data is encrypted according to industry best-practices and securely stored. Using Lockr helps keep the developer safe, by removing the sensitive passwords and key secrets from the central code base, following security best practices, protecting you as the developer if a site were to be compromised. 

## Is Lockr Safe? ##
Lockr can secure any API key, secret key, and other types of credentials. Once enabled in WordPress, keys entered are encrypted, then sent over to the Lockr system and removed from the code repository and database. This encryption teamed with hosting provider based authentication prevents your key from being used outside your website. Lockr also manages keys on a “per environment" basis which helps eliminate the potential of keys being shared from production to development environments. No longer will you have to worry about sending a notification from development to your production users, or having production data decrypted in development environments.

Leveraging proven enterprise-grade key management technology from Townsend Security, Lockr's offsite key management delivers best-practice security to protect against critical vulnerabilities and help sites meet PCI DSS, HIPAA and other security requirements and regulations.

This plugin is designed, written and maintained by experts in security, to the end user it is easy to use and understand. Let Lockr handle the difficult part of securing your site, so you can focus on delivering the best experience possible to your users.

== Installation ==

Installation of Lockr is simple, and if you are on a supported hosting partner, it is done seamlessly and within seconds.

1. Upload the Lockr directory to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate Lockr through the 'Plugins' screen in your WordPress 
3. Visit Settings > Lockr
4a. Existing User: enter the email address you’ve used in the past
4b. New User: enter your email address and we’ll take care of the rest

You're set! Start entering your keys through the Lockr config!


== Frequently Asked Questions ==

= How is my key stored? =

Before transmitting your key to Lockr, it is encrypted and then sent via a secure connection to the Lockr server where it is held in a FIPS 140-2 compliant key manager. By encrypting it before it leaves the site, Lockr has no way of knowing or accessing your key. Only your site can unlock the key for it to be used.

= Will this slow down my site? =

Not to any noticeable effect. The connection to the Lockr server depends on the speed of your servers connection but on average we see round trips of under 100ms. This is about the same time that some database queries take.

= What is the uptime guarantee of Lockr = 

We know your keys are critical to your site. To ensure you have your keys whenever you need it our cloud is built to scale, and we back that with a 99.9% uptime guarantee. A dedicated SLA is available for enterprise clients.

== Screenshots ==


== Changelog ==

= 2.0 =
* Hello WordPress! Lockr is happy to be a part of the community and officially in the plugin directory. 
* To celebrate our release we have provided a function to encrypt/decrypt data based on a key stored in Lockr. Simply use lockr_encrypt() and lockr_decrypt() to secure your data. More features around encryption are planned for future releases
