=== MyCryptoCheckout - Accept 85+ coins: Bitcoin, Ethereum, and more ===
Contributors: edward_plainview
Donate link: https://mycryptocheckout.com
License: GPLv3
Requires at least: 4.9
Requires PHP: 5.6
Stable tag: 2.35
Tags: bitcoin, ethereum, cryptocurrency, gateway, woocommerce
Tested up to: 5.0

Cryptocurrency payment gateway for WooCommerce and Easy Digital Downloads. Accept 85+ coins: Bitcoin, Ethereum, Litecoin, and more. Peer-to-peer transactions.

== Description ==

Cryptocurrency payment gateway for WooCommerce and Easy Digital Downloads. Receive coins directly into the wallet of your choice.

https://www.youtube.com/watch?v=nUoJ9ziaAJQ

= Key Features & Highlights =

- 0% transaction fees
- No KYC or product restrictions
- Peer-to-peer transactions
- Use any wallet(s) you want: desktop, mobile, or online
- Automagically detect unique payments from customers using one wallet address
- Optional hierarchically deterministic (HD) wallet support
- No redirection to 3rd parties, no iframes, no modal windows
- Optional donations widget shortcode generator
- Built in support for .onion addresses on Tor
- Compare MyCryptoCheckout to several other traditional and crypto solutions - <a href="https://mycryptocheckout.com/comparison/">Payment Gateway Comparison</a>
- Take MCC for a test ride by visiting our <a href="https://wpdemo.mycryptocheckout.com/">demo store</a>
- How to auto convert received <a href="https://mycryptocheckout.com/doc/auto-convert-crypto-to-fiat/">Bitcoin/Altcoin to USD/EUR/etc</a>

The free license can process three sales per month. A <a href="https://mycryptocheckout.com/pricing/">flat rate license</a> can be purchased for your account that includes unlimited transactions if you require more. <a href="https://mycryptocheckout.com/bulk-pricing/">Bulk pricing options</a> are available if you need to use MyCryptoCheckout on several domains.

= Webshops supported =

- Easy Digital Downloads
- WooCommerce

= Cryptocurrencies supported: =

- Bitcoin BTC (Including SegWit, HD wallets)
- Bitcoin Cash BCH (Including HD wallets)
- Bitcoin Gold BTG
- Bitcoin Zero BZX
- Bitsmdo BSD
- CatoCoin CATO
- CloakCoin CLOAK
- ColossusXT COLX
- CrypticCoin CRYP
- Dash DASH (Including HD wallets)
- Decred DCR
- Digibyte DGB (Including SegWit)
- Elite 1337
- Ethereum ETH (Including MetaMask)
- Ethereum Classic ETC
- Groestlcoin GRS (Including SegWit, ZPUB HD wallets)
- Litecoin LTC (Including SegWit, HD wallets)
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
- Viacoin VIA (Including SegWit, HD wallets)
- Waves WAVES (Including Waves Client)
- Zcash ZEC (T-address recipients only)
- We can now add your <a href="https://mycryptocheckout.com/add-cryptocurrency/">cryptocurrency</a>!

= ERC20 tokens supported (Including MetaMask): =

- 0x ZRX
- Acorn Collective OAK
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
- Gemini Dollar GUSD
- Gifto GTO
- Golem GNT
- Herbalist Token HERB
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
- Spendcoin SPND
- Status SNT
- Storiqa STQ
- Storm STORM
- TenXPay PAY
- Tether USD USDT
- TrueUSD TUSD
- TuneTrade TXT
- USD Coin USDC
- Veritaseum VERI
- Worldcoin1 WRD1
- Add your <a href="https://mycryptocheckout.com/custom-token/">custom ERC20 tokens</a>!

= NEM Mosaic tokens supported: =

- shelterDAO SHEL
- Add your <a href="https://mycryptocheckout.com/nem-token/">custom NEM mosaic tokens</a>!

= TRON TRC-10 / TRC-20 Tokens supported: =

- ActivEightCoin ACTIV
- TRONBITCOIN TBTC
- Add your <a href="https://mycryptocheckout.com/trc-20-token/">custom TRON tokens</a>!

= WAVES Tokens supported (Including Waves Client): =

- BLXS Blockscart
- CoffeeCoin COF
- Tokes TKS
- Waves World WAVESWORLD
- Add your <a href="https://mycryptocheckout.com/waves-token/">custom WAVES tokens</a>!

= Cryptocurrency Donations Widget =

Receive donations in any of the cryptocurrencies supported by MyCryptoCheckout. Generate a widget using our simple shortcode generator and add it into any text widget or text editor. Shortcode generation options:

- select currencies to show
- select primary currency
- show currencies with icons or a dropdown box
- show QR code
- show wallet address text

= Incompatible Plugins =

The following plugins prevent MyCryptoCheckout from working correctly:

- Plugin Organizer by Jeff Sterup. Deactivate plugin.

= Security =

Disable the MCC currencies tab: after you have wallets setup you can prevent them from being edited in the WordPress admin. Add the following code to your wp-config file-

<code>define( 'MYCRYPTOCHECKOUT_DISABLE_WALLET_EDITOR', true );</code>

= Technical disclosure =

Upon plugin activation an account is created on the MyCryptoCheckout API server: api.mycryptocheckout.com. The only data that is sent is your WordPress install's public URL and the plugin version. The URL is used by the API server to know where to send updated account info (license status, payment statistics), exchange rates, and completed purchase notifications. The plugin version is used to help answer requests made by the plugin (different plugin versions speak to the API server differently).

If your server cannot be reached by the API server this plugin will not function.

== Installation ==

1. Activate the plugin
2. Visit Admin > Settings > MyCryptoCheckout
3. Check that your account looks ok
4. Visit the currencies tab
5. Set up one or more currencies
6. Visit your WooCommerce payment gateway settings. The instructions included in receipt e-mails are taken from the WC MCC gateway instructions text boxes.
7. Or visit your EasyDigitalDownloads payment gateway settings. The instructions included in receipt e-mails can be included using the {mcc_instructions} e-mail tag. The text is taken from the EDD MCC payment gateway instructions text boxes.

View a detailed step-by-step <a href="https://mycryptocheckout.com/doc/installation/">installation guide</a>


== Screenshots ==

1. WooCommerce checkout
2. WooCommerce checkout icon colors
3. Payment page w/ payment data
4. Account overview tab
5. Currencies tab
6. Adding a Monero wallet
7. WooCommerce gateway settings
8. Easy Digital Downloads checkout
9. Easy Digital Downloads gateway settings
10. Donations widget
11. Donations generator form

== Changelog ==

* New currency: Groestlcoin GRS
* New currency: Tether USD USDT
* Fix: QRcode and Timer HTML was being escaped unnecessarily.

= 2.35 20190118 =

* New currency: Herbalist Token HERB
* New currency: Storiqa STQ

= 2.34 20190113 =

* New: Some wallets (BCH, BTC, BTG, BZX, DASH, DCR, DGB, LTC, TPAY, XVG, ZEC) now optionally support 0-conf payments.
* New: HD wallet support for Dash
* New: HD wallet support for Viacoin VIA
* New: SegWit support for Viacoin VIA

= 2.33 20190110 =

* New currency: BLXS Blockscart
* Downgrade Bitwasp library to support PHP 5.6 again.
* Add beginning of CLI. Used for automatic testing.

= 2.32 20190109 =

* New currency: Acorn Collective OAK
* New currency: Bitcoin Zero BZX
* Upgrade Bitwasp library, that handles HD wallets, updated to v1.0. This requires PHP7.

= 2.31 20181228 =

* New currency: CloakCoin CLOAK

= 2.30 20181221 =

* New currency: USD Coin USDC
* New currency: Tokes TKS
* New currency: ActivEightCoin ACTIV
* New: "open in waves" button. The button uses the Waves payments API and opens the Waves client either desktop or online version.
