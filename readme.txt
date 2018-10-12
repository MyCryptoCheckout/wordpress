=== MyCryptoCheckout - Accept 70+ coins: Bitcoin, Ethereum, and more ===
Contributors: edward_plainview
Donate link: https://mycryptocheckout.com
License: GPLv3
Requires at least: 4.9
Requires PHP: 5.4
Stable tag: 2.22
Tags: bitcoin, ethereum, cryptocurrency, gateway, woocommerce
Tested up to: 4.9.8

Cryptocurrency payment gateway for WooCommerce and Easy Digital Downloads. Accept 70+ coins: Bitcoin, Ethereum, Litecoin, and more. Peer-to-peer transactions.

== Description ==

Cryptocurrency payment gateway for WooCommerce and Easy Digital Downloads. Receive coins directly into the wallet of your choice.

https://www.youtube.com/watch?v=nUoJ9ziaAJQ

= Key Features & Highlights =

- 0% transaction fees
- No product restrictions
- Peer-to-peer transactions
- Use any wallet(s) you want: desktop, mobile, or online
- Automagically detect unique payments from customers using one wallet address
- Optional hierarchically deterministic (HD) wallet support
- No redirection to 3rd parties, no iframes, no modal windows
- Optional donations widget shortcode generator
- See the <a href="https://mycryptocheckout.com/comparison/">feature comparison table</a> to see why you should use MyCryptoCheckout instead of other crypto gateways
- How to auto convert received <a href="https://mycryptocheckout.com/doc/auto-convert-crypto-to-fiat/">Bitcoin/Altcoin to USD/EUR/etc</a>

The free license can process three sales per month. A <a href="https://mycryptocheckout.com/pricing/">flat rate license</a> can be purchased for your account if you require more. The flat rate license includes unlimited transactions. <a href="https://mycryptocheckout.com/bulk-pricing/">Bulk pricing options</a> are available if you need to use MyCryptoCheckout on several domains.

= Webshops supported =

- Easy Digital Downloads
- WooCommerce

= Cryptocurrencies supported: =

- Bitcoin BTC (Including SegWit addresses, Electrum HD wallet)
- Bitcoin Cash BCH (Including Electrum HD wallet)
- Bitcoin Gold BTG
- Bits'mdo' BSD
- CatoCoin CATO
- ColossusXT COLX
- CrypticCoin CRYP
- Dash DASH
- Decred DCR
- Digibyte DGB (Including SegWit addresses)
- Elite 1337
- Ethereum ETH (Including MetaMask)
- Ethereum Classic ETC
- Litecoin LTC (Including SegWit addresses, Electrum HD wallet)
- MarsCoin MARS
- Monero XMR
- NEM XEM
- New York Coin NYC
- Solaris XLR
- Straks STAK
- TokenPay TPAY
- TRON TRX
- TurtleNetwork TN
- Verge XVG
- Viacoin VIA
- Waves WAVES
- Zcash ZEC (T-address recipients only)
- We can now add your <a href="https://mycryptocheckout.com/add-cryptocurrency/">cryptocurrency</a>!

= ERC20 tokens supported (Including MetaMask): =

- 0x ZRX
- ADULTEUM ADULT
- Aeternity AE
- Aragon ANT
- Augur REP
- Bancor BNT
- Basic Attention Token BAT
- Binance Coin BNB
- Dai DAI
- DigixDAO DGD
- Dragonchain DRGN
- eBitcoin EBTC
- Flix FLIX
- FunFair FUN
- Gifto GTO
- Golem GNT
- Huobi Token HT
- Icon ICX
- intimate ITM
- Invacio INV
- Kyber Network KNC
- Latino Token LATINO
- L'île LILE
- Loom LOOM
- Maker MKR
- MetalPay MTL
- Monaco MCO
- Nebulas NAS
- Omisego OMG
- onG.social ONG
- Populous PPT
- Pundi X NPXS
- QASH
- Raiden RDN
- Request Network REQ
- SALT
- shelterDAO SHEL
- Status SNT
- Storm STORM
- TenXPay PAY
- Veritaseum VERI
- Worldcoin1 WRD1
- We can now add your <a href="https://mycryptocheckout.com/custom-token/">custom ERC20 tokens</a>!

= NEM Mosaic tokens supported: =

- shelterDAO SHEL
- We can now add your <a href="https://mycryptocheckout.com/nem-token/">custom NEM mosaic tokens</a>!

= TRON TRC-20 Tokens supported: =

- TRONBITCOIN TBTC
- We can now add your <a href="https://mycryptocheckout.com/trc-20-token/">custom TRON TRC-20 tokens</a>!

= WAVES Tokens supported: =

- CoffeeCoin COF
- We can now add your <a href="https://mycryptocheckout.com/waves-token/">custom WAVES tokens</a>!

= Cryptocurrency Donations Widget =

Receive donations in any of the cryptocurrencies supported by MyCryptoCheckout. Generate a widget using our simple shortcode generator and add it into any text widget or text editor. Shortcode generation options:

- receive donations in any or all the cryptocurrencies supported by MyCryptoCheckout
- select currencies to show
- select primary currency
- show currencies with icons or a dropdown box
- show QR code
- show wallet address text

= Technical disclosure =

Upon plugin activation an account is created on the MyCryptoCheckout API server: api.mycryptocheckout.com. The only data that is sent is your WordPress install's public URL and the plugin version.

The URL is used by the API server to know where to send updated account info (license status, payment statistics), exchange rates and completed purchase notifications.

The plugin version is used to help answer requests made by the plugin (different plugin versions speak to the API server differently).

If your server cannot be reached by the API server this plugin will not function.

== Installation ==

1. Activate the plugin
2. Visit Admin > Settings > MyCryptoCheckout
3. Check that your account looks ok
4. Visit the currencies tab
5. Set up one or more currencies
6. Visit your WooCommerce payment gateway settings. The instructions included in receipt e-mails are taken from the WC MCC gateway instructions text boxes.
7. Visit your EasyDigitalDownloads payment gateway settings. The instructions included in receipt e-mails can be included using the {mcc_instructions} e-mail tag. The text is taken from the EDD MCC payment gateway instructions text boxes.

== Screenshots ==

1. WooCommerce checkout
2. EasyDigitalDownloads checkout
3. WooCommerce purchase confirmation page with payment data
4. Account tab
5. Currencies tab
6. Adding a Monero wallet
7. Global settings tab for network and single installs
8. Local settings tab for network installs
9. WooCommerce gateway settings
10. EasyDigitalDownloads gateway settings
11. Donations generator form
12. Donations widget

== Changelog ==

= 2.22 20181012 =

* We now accept <a href="https://mycryptocheckout.com/waves-token/">custom WAVES tokens</a>!
* New currency: CoffeeCoin COF
* New currency: Invacio INV
* New: Want your webshop publically listed in our upcoming store directory? There's a checkbox for that in the account overview tab.
* New: Added Monero private view key input placeholder text.

= 2.21 20181006 =

* New: HD wallet support for Bitcoin Cash BCH
* New: HD wallet support for Litecoin LTC

= 2.20 20181004 =

* New currency: ADULTEUM ADULT
* New currency: CrypticCoin CRYP
* New currency: L'île LILE
* Fix: WooCommerce and EDD: allow having virtual currency as base currency.
* Fix: EDD: Override checkout payment method name to match checkout method name.
* Fix: Metamask: Use different dividers for different currencies.
* New snippet: <a href="https://mycryptocheckout.com/doc/snippets/refresh-page-after-payment-complete/">Refresh page after payment complete</a>

= 2.19 20180925 =

* New currency: Monero XMR

= 2.18 20180920 =

* New currency: CatoCoin CATO
* New currency: TRONBITCOIN TBTC
* New currency: TurtleNetwork TN
* Fix: EDD: Show receipt when _not_ using MCC on checkout.
* Fix: QRcode CSS on payment page.
* Test: Used BrowserStack to test payment page across browsers, operating systems, and mobile devices.

= 2.17 20180915 =

* New currency: Bits'mdo' BSD
* New currency: Latino Token LATINO
* New: Added MetaMask support for all ERC20 tokens except Flix.
* Fix: Wordpress: Checkout icon generated on a per blog basis.

= 2.16 20180904 =

* New currency: Huobi Token HT
* New currency: Viacoin VIA
* Fix: Woocommerce: Fire woocommerce_cancelled_order action upon cancelling the order.
* Fix: EDD: Fix checkout QR code.
* Fix: EDD: Don't show MCC checkout boxes when using test payments.
* Fix: Fatal error with GoURL fixed, but double currencies won't allow MCC to work.

= 2.15 20180829 =

* New currency: eBitcoin EBTC
* New currency: shelterDAO SHEL
* New: WooCommerce Metamask payment support on checkout. The MetaMask button is automatically shown during checkout if detected.
* Fix woocommerce_settings_get_option error on some installs.
* Fix Woocommerce checkout "optional" text. It should be required, of course.
* Fix removal of thousands separator when sending the amount to the API.
* Currency disabled: NEO

= 2.14 20180813 =

* New currency: Tron TRX

= 2.13 20180810 =

* New currency: Loom
* New currency: Monaco MCO
* New currency: NEM XEM
* New currency: TenXPay PAY
* New currency: Pundi X Token PNXS
* Default payment timer for new installs is now 2 hours instead of 6.

= 2.12 20180713 =

* New currency: onG.social ONG

= 2.11 20180706 =

* New currency: Elite 1337
* New currency: Binance Coin BNB
* New currency: intimate ITM
* New currency: MarsCoin MARS
* New currency: Straks STAK
* New currency: TokenPay TPAY
* New: Allow alignment selection for donations widget.
* Fix: Conflict between CSS classes that prevented the "payment complete" tick from appearing. CSS class "hidden" renamed to "mcc_hidden".
* Fix: Be more insistent sending unsent payments to the API server.
* Fix: Normalize currency amounts using commas before converting them to crypto amounts.
* Fix: Only override payment URL if using MCC. Fixes conflict with some other payment methods.

= 2.10 20180614 =

* New: Some currency QR codes will also include the amount to pay. Bitcoin Cash, Bitcoin, Ethereum, LiteCoin.
* New: WooCommerce; option for sending invoice to customer upon purchase, in addition to after payment.
* Fix: Ensure that small amounts of virtual currency (0.00000001 BTC) are displayed as small amounts, and not in scientific notation.
* Fix: WooCommerce; when using a virtual currency as the native currency, ensure that the order amount is the same as the amount MCC is expecting to receive.
* Fix: WooCommerce; when using a virtual currency as the native currency, show a warning if the WooCommerce currency decimals don't match the capabilities of the virtual currency in MCC.
* Fix: WooCommerce; during checkout, also include shipping in the virtual currency preview amount.

= 2.09 20180524 =

* New currency: Raiden (RDN)
* New currency: New York Coin (NYC)
* New currency: Verge (XVG)
* New currency: Worldcoin1 (WRD1)

= 2.08 20180518 =

* New: Donations shortcode to allow your users to send you donations via cryptocurrency.
* Fix: Allow MCC to ignore currencies that have been disabled / removed.

= 2.07 20180511 =

* New currency: Flix (FLIX)
* New currency: Digibyte (DGB)
* New currency: Solaris (XLR)

= 2.06 20180509 =

* Fix: When using cryptocurrencies as the primary WooCommerce currency, do not try to convert to fiat first.
* New currency: Aragon (ANT)

= 2.05 20180505 =

* New: Added global / local QR-code settings.
* New: Added payment countdown timer with global / local settings.
* New currency: Aeternity (AE)
* New currency: ColossusXT (COLX)
* New currency: Dai Stablecoin (DAI)
* New currency: Dragonchain (DRGN)
* New currency: Gifto (GTO)
* New currency: Nebulas (NAS)
* New currency: Request Network (REQ)
* New currency: SALT (SALT)
* New currency: Storm (STORM)
* New currency: VeChain (VEN)
* New currency: Veritaseum (VERI)

= 2.04 20180403 =

* New currency: Ethereum Classic ETC
* New currency: Decred DCR

= 2.03 20180331 =

* New: Add payment timeout hours option to EDD.
* New: Added option to choose default currencies on checkout.
* Fix: Load CSS on the checkout page for those themes that don't have WooCommerce support.
* Fix: Default WooCommerce payment timeout is now 6 hours instead of 72.

= 2.02 20180328 =

* New currency: Bitcoin Gold BTG
* New currency: MetalPay MTL
* New currency: Neo Smart Economy NEO
* New currency: Zcash ZEC
* New: Show QR code for the wallet address when checking out. If you are upgrading and want to enable the QR, you can either (1) reset your EDD or WC MyCryptoCheckout settings (in order to get the new text) or add the following to your online instructions text area before the final &lt;/div&gt;:

&lt;div class="mcc_qr_code"&gt;&lt;/div&gt;

* New: Added payment timeout setting for WooCommerce. The default is 3 days, but can be changed if you want your orders to be automatically cancelled before that. The default will be changed to 6 hours in a few versions' time.
* New: Added setting to change the status of the order when payment is complete. Use this to set your paid orders to complete if your products don't need to be processed manually.
* Fix: Currency icons in WooCoommerce checkout box are now dynamic, showing only the currencies that are available.
* Fix: No more rounding error when using BTC as main WooCommerce currency, and trying to pay in BTC.
* Fix: Try to intercept API calls earlier by raising the priority of the template_redirect hook.
* Fix: Cancelling a WC order will cancel the payment on the API server simultaenously.

= 2.01 20180315 =

* Fix: Use a different way of displaying the copy-to-clipboard icon on the checkout page, making it compatible with more themes.

= 2.0 20180313 =

* New ERC20 token: <a href="https://www.stake-it.com/">STAKE</a>
* Fix: LiteCoin addresses can now be Segwith length (43 chars).
* Fix: Add CSS to prevent the WooCommerce currency selection box from growing too big on some themes.
* Fix: Incorrect wallets key in the options table. Your wallet info will remain untouched if upgrading normally. Else: deactivate and reactive the plugin.
* Fix: Nicer rounding of amounts.
