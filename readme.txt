=== MyCryptoCheckout ===
Contributors: edward_plainview
Donate link: https://mycryptocheckout.com
License: GPLv3
Requires at least: 4.9
Requires PHP: 5.4
Stable tag: trunk
Tags: bitcoin, ethereum, cryptocurrency, gateway, woocommerce
Tested up to: 4.9.4

Cryptocurrency payment gateway for WooCommerce and Easy Digital Downloads.

== Description ==

MyCryptoCheckout is a cryptocurrency payment gateway for WooCommerce and Easy Digital Downloads. It allows you to receive coins directly into the wallet of your choice.

= Key Features & Highlights =

- 0% transaction fees
- No product restrictions
- Use any wallet(s) you want: desktop, mobile or online
- Fully automated peer-to-peer transactions
- No redirection to 3rd parties, no iframes nor modal windows
- See the <a href="https://mycryptocheckout.com/comparison/">feature comparison table</a> on our website to see why you should use MyCryptoCheckout instead of other crypto payment gateways.

The free license can process three sales per month. A <a href="https://mycryptocheckout.com/pricing/">flat rate license</a> can be purchased for your account if you require more. The flat rate license includes unlimited transactions. <a href="https://mycryptocheckout.com/bulk-pricing/">Bulk pricing options</a> are available if you need to use MyCryptoCheckout on several domains.

After checkout, payment instructions are automatically included in the purchase receipt.

= Webshops supported =

- Easy Digital Downloads
- WooCommerce

= Cryptocurrencies supported: =

- Bitcoin BTC (Including SegWit addresses)
- Bitcoin Cash BCH
- Dash DASH
- Ethereum ETH
- Litecoin LTC

= ERC20 tokens supported: =

- Basic Attention Token BAT
- Bancor BNT
- Digix Global DGD
- EOS
- FunFair FUN
- Golem GNT
- Icon ICX
- Kyber Network Crystal KNC
- Maker MKR
- Omisego OMG
- Populous PPT
- QASH
- QTUM
- Augur REP
- <a href="https://www.stake-it.com/">STAKE</a>
- Status Network SNT
- Tronix TRX
- 0xProject ZRX
- We can now add your <a href="https://mycryptocheckout.com/custom-token/">custom ERC20 tokens</a!

= Technical disclosure =

Upon plugin activation an account is created on the MyCryptoCheckout API server: api.mycryptocheckout.com. The only data that is sent is your WordPress install's public URL and the plugin version.

The URL is used by the API server to know where to send updated account info (license status, payment statistics), exchange rates and completed purchase notifications.

The plugin version is used to help answer requests made by the plugin (different plugin versions speak to the API server differently).

If your server cannot be reached by the API server this plugin will not function.

== Installation ==

1. Activate the plugin
2. Visit settings > MyCryptoCheckout
3. Check that your account looks ok
4. Visit the currencies tab
5. Set up one or more currencies
6. Visit your WooCommerce payment gateway settings. The instructions included in receipt e-mails are taken from the WC MCC gateway instructions text boxes.
7. Visit your EasyDigitalDownloads payment gateway settings. The instructions included in receipt e-mails can be included using the {mcc_instructions} e-mail tag. The text is taken from the EDD MCC payment gateway instructions text boxes.


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

* New ERC20 token: <a href="https://www.stake-it.com/">STAKE</a>
* Fix: Nicer rounding of amounts.

= 1.11 20180307 =

* New: Added DASH coin.

= 1.10 20180306 =

* New: Display exchange rates for your used currencies in the account info tab.
* Fix: EDD orders were not being sent to the API server the first try.
* Fix: More robust cancellation handling, will now report non-existent orders to the API server.
* Code: Replaced the old wordpress mycryptocheckout_payment_complete and mycryptocheckout_cancel_payment actions with their newer object equivalents: mycryptocheckout_cancel_payment and  mycryptocheckout_complete_payment. See the src/actions directory.

<a href="https://mycryptocheckout.com/bulk-pricing/">Bulk license pricing</a> is now available.

We can now add your <a href="https://mycryptocheckout.com/custom-token/">custom ERC20 tokens</a>!

= 1.9 20180228 =

* New: Added support for BitCoin Segwit addresses (42 and 62 characters long).

= 1.8 20180226 =

* New: ERC20 tokens introduced. The most popular tokens added were:

- Basic Attention Token BAT
- Bancor BNT
- Digix Global DGD
- EOS
- FunFair FUN
- Golem GNT
- Icon ICX
- Kyber Network Crystal KNC
- Maker MKR
- Omisego OMG
- Populous PPT
- QASH
- QTUM
- Augur REP
- Status Network SNT
- Tronix TRX
- 0xProject ZRX
