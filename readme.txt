=== MyCryptoCheckout ===
Contributors: edward_plainview
Donate link: https://mycryptocheckout.com
License: GPLv3
Requires at least: 4.9
Requires PHP: 5.4
Stable tag: trunk
Tags: cryptocurrency, checkout, gateway, woocommerce, easydigitaldownloads, bitcoin
Tested up to: 4.9.4

Cryptocurrency payment gateway for WooCommerce and EasyDigitalDownloads.

== Description ==

MyCryptoCheckout is a cryptocurrency payment gateway for WooCommerce and EasyDigitalDownloads. It allows you to receive funds directly into the wallet of your choice, without exposing yourself to a risk by having to use a highly vulnerable online wallet service. Other features:

- 0% transaction fees
- No product restrictions
- Use any wallet(s) you want: desktop, mobile or online
- Visitors stay on your site during checkout

With the free license you can process three sales per month. If you require more, a flat rate license can be purchased for your account.

Transaction processing is free! Unlike other payment gateways we do not charge any percentages nor fixed amounts per transaction. The license cost is the same whether you have 10 or 10,000 sales a month.

The following webshops are supported:

- EasyDigitalDownloads
- WooCommerce

The following cryptocurrencies are supported:

- Bitcoin BTC
- Bitcoin Cash BCH
- Ethereum ETH
- Litecoin LTC

After checkout, payment instructions are automatically included in the purchase receipt.

The instructions included in EasyDigitalDownloads e-mails can be included using the {mcc_instructions} e-mail tag. The text is taken from the EDD MCC payment gateway option "Instructions".

The instructions included in WooCommerce e-mails is taken from the WC MCC gateway "Instructions" option.

= Technical disclosure =

Upon plugin activation an account is created on the MyCryptoCheckout API server: api.mycryptocheckout.com. The only data that is sent is your Wordpress install's public URL.

The URL is used by the API server to know where to send updated account info (license status, payment statistics), exchange rates and purchase notifications.

If your server cannot be reached by the API server this plugin will not function.

== Installation ==

1. Activate the plugin
2. Visit settings > MyCryptoCheckout
3. Check that your account looks ok
4. Visit the currencies tab
5. Set up one or more currencies
6. Visit your WooCommerce payment gateway settings
7. Visit your EasyDigitalDownloads payment gateway settings

To configure EDD, edit your purchase receipt e-mail and add the {mcc_instructions} tag.

== Screenshots ==

1. WooCommerce checkout
2. EasyDigitalDownloads checkout
3. Account tab
4. Currencies tab
5. Settings tab
6. WooCommerce gateway settings
7. EasyDigitalDownloads gateway settings
8. Payment info in WooCommerce
9. Payment info in EasyDigitalDownloads
10. WooCommerce purchase confirmation page with payment data

== Changelog ==

= 1.7 20180213 =

* New: Added a test mode allowing you to make purchases without having to use any monthly payments. This will allow you to edit the payment instructions until they suit your business better. The orders are created but will never be marked as paid.

= 1.6 20180206 =

* Fix: Rename a method to prevent a PHP error. The method name use() is apparently reserved in PHP 5.5, but not in PHP 7. Renamed to use_it().
* Fix: Some servers don't report the content type of requests. Work around that.

= 1.5 20180130 =

* New: Amount and address on order confirmation page can now be copied using buttons (javascript)!
* New: Add "Hide order overview" to Woocommerce gateway settings. This hides the order overview table, allowing the payment instructions to be shown higher up on the page. This function uses javascript.
* Fix: Link to wallet recommendation page on Wallets page.
* Fix: ETH decimals are now 8 due to Coinbase restrictions.
* Fix: Split payment instructions into instructions for e-mail and online (order confirmation page). Check your gateway settings after updating!

= 1.4 20180126 =

* Code: Cleanup checkout code for WooCommerce.

= 1.3 20180123 =

* Fix: Do not unnecessarily create payment data for non-crypto payments in WooCommerce.

= 1.2 20180116 =

* Fix: Allow for temporary account locking if a payment is unable to be sent to the API server. Account will automatically unlock when contact is reestablished.

= 1.1 20180115 =

* Fix: Better EDD installation instruction text for e-mail tag.
* Fix: Display currency ID when editing the wallet.

= 1 20180112 =

Initial release.
