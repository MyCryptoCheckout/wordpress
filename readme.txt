=== MyCryptoCheckout - Accept 95+ coins: Bitcoin, Ethereum, and more ===
Contributors: edward_plainview
Donate link: https://mycryptocheckout.com
License: GPLv3
Requires at least: 4.9
Requires PHP: 5.6
Stable tag: 2.86
Tags: bitcoin, ethereum, cryptocurrency, gateway, woocommerce
Tested up to: 5.6

Cryptocurrency payment gateway for WooCommerce and Easy Digital Downloads. Accept 95+ coins: Bitcoin, Ethereum, Litecoin, and more. Peer-to-peer transactions.

== Description ==

Cryptocurrency payment gateway for WooCommerce and Easy Digital Downloads. Receive coins directly into the wallet of your choice.

https://www.youtube.com/watch?v=nUoJ9ziaAJQ

= Key Features & Highlights =

- 0% transaction fees
- No KYC or product restrictions
- Peer-to-peer transactions
- No redirection to 3rd parties or iframes
- Use any crypto wallet you want
- Automagically detect unique payments using one wallet address
- Hierarchically deterministic (HD) wallet support
- 1-Click payment buttons, MetaMask, Waves Client, etc.
- Fiat autosettlement enables you to connect to exchange(s) and instantly convert selected coins to fiat or stablecoins
- Donations widget shortcode generator
- Tor support
- 0-conf (mempool) support for some coins
- Compare MyCryptoCheckout to several other traditional and crypto solutions - <a href="https://mycryptocheckout.com/comparison/">Payment Gateway Comparison</a>
- Take MCC for a test ride by visiting our <a href="https://wpdemo.mycryptocheckout.com/">demo store</a>

The free license can process 3 sales per month. A <a href="https://mycryptocheckout.com/pricing/">flat rate license</a> can be purchased for your account that includes unlimited transactions if you require more. <a href="https://mycryptocheckout.com/bulk-pricing/">Bulk pricing</a> is available if you need to use MyCryptoCheckout on several domains.

= eCommerce platforms supported =

- Easy Digital Downloads
- WooCommerce

= Cryptocurrencies supported: =

- Binance Coin BNB
- Bitcoin BTC (Including SegWit, HD wallets)
- Bitcoin Gold BTG
- Dash DASH (Including HD wallets)
- Decred DCR
- Digibyte DGB (Including SegWit)
- Electra ECA
- EOS
- Ethereum ETH (Including MetaMask)
- Footballcoin XFC
- Groestlcoin GRS (Including SegWit, ZPUB HD wallets)
- Litecoin LTC (Including SegWit, HD wallets)
- MktCoin MLM
- Monero XMR
- NEM XEM
- Stellar XLM
- TRON TRX
- Ultragate ULG
- Verge XVG
- Waves WAVES (Including Waves Client)
- Zcash ZEC (T-address recipients only)
- We can now add your <a href="https://mycryptocheckout.com/add-cryptocurrency/">cryptocurrency</a>!

= ERC20 tokens supported (Including MetaMask): =

- 0x ZRX
- Aeternity AE
- Aragon ANT
- Bancor BNT
- Basic Attention Token BAT
- CGBBank CGB
- Chainlink LINK
- Connect Coin XCON
- Crypto.com Coin CRO
- CuraDAI CURA
- Devil's Dragon DDGN
- Multi-collateral DAI DAI
- Decentraland MANA
- DigitexFutures DGTX
- DigixDAO DGD
- Dogecoin DOGE
- Dragonchain DRGN
- Enjin Coin ENJ
- FRTS Coin FRTS
- FunFair FUN
- Gemini Dollar GUSD
- Gifto GTO
- Golem GNT
- Golfcoin GOLF
- Huobi Token HT
- IMSmart Token IMT
- InnovaMinex MINX
- Invacio INV
- Jobchain JOB
- Kubo Coin KUBO
- Kyber Network KNC
- Loom LOOM
- Maker MKR
- MetalPay MTL
- Monaco MCO
- Media Play Cash MPC
- Micro Tuber MCT
- Nexo Token NEXO
- OMG Network OMG
- Polymath Network POLY
- Populous PPT
- Pundi X NPXS
- QASH
- Raiden RDN
- Request REQ
- Roks ROK
- SALT
- Status SNT
- Storm STORM
- TenXPay PAY
- Tether USD USDT
- TBC Mart Token TMT
- TBC Shopping Token TST
- TheCash TCH
- Traders Token TRDS
- TrueUSD TUSD
- USD Coin USDC
- VcashPay VCP
- Veritaseum VERI
- Add your <a href="https://mycryptocheckout.com/custom-token/">custom ERC20 tokens</a>!

= NEM Mosaic tokens supported: =

- Add your <a href="https://mycryptocheckout.com/nem-token/">custom NEM mosaic tokens</a>!

= TRON TRC-10 / TRC-20 Tokens supported: =
- CryptoHours CHS

- BitTorrent BTT
- Add your <a href="https://mycryptocheckout.com/trc-20-token/">custom TRON tokens</a>!

= WAVES Tokens supported (Including Waves Client): =

- CoffeeCoin COF
- CopyrightCoins CCIM
- Neutrino USD USDN
- Waves World WW
- Add your <a href="https://mycryptocheckout.com/waves-token/">custom WAVES tokens</a>!

= Stellar Tokens supported: =

- Add your <a href="https://mycryptocheckout.com/stellar-token/">custom Stellar tokens</a>!

= Fiat Autosettlements =

Autosettlement is a feature that enables you to connect MyCryptoCheckout to exchange(s) and automatically sell any received cryptocurrencies into fiat or stablecoins (USD, USDC, USDT, TUSD). This is a great tool for merchants who want to accept bitcoin/altcoins but prefer to cash out immediately to avoid market volatility.

Supported exchanges:

- Binance
- Bittrex
- More coming soon!

= Cryptocurrency Donations Widget =

Receive donations in any of the cryptocurrencies supported by MyCryptoCheckout. Generate a widget using our simple shortcode generator and add it into any text widget or text editor. Shortcode generation options:

- select currencies to show
- select primary currency
- show currencies with icons or a dropdown box
- show QR code
- show wallet address text

= Code snippets =

We have various code snippets that allow you to customize MyCryptoCheckout together with your e-commerce solution:

<a href="https://mycryptocheckout.com/doc/snippets/">See all available code snippets</a>

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

1. Checkout
2. Payment page
3. Account overview tab
4. Currencies tab
5. Edit wallet
6. Autosettlement tab
7. Edit Autosettlement
8. WooCommerce settings
9. Easy Digital Downloads settings
10. Donations widget
11. Donations shortcode generator

== Frequently Asked Questions ==

= Unable to retrieve your account data =

If MyCryptoCheckout is unable to retrieve the account data for your domain, it's usually due to the MCC API server being blocked from connecting to your install. Check for:

- Maintenance plugins
- Password protection plugins
- Firewalls

If after disabling the above plugins you still can't get it working, then contact us and we'll try to find the cause of the problem.

= Where can I find full documentation? =

Full searchable docs can be found at <a href="https://mycryptocheckout.com/doc/installation/">https://mycryptocheckout.com/doc/installation/</a>

= Incompatible plugin list =

The following plugins prevent MyCryptoCheckout from working correctly:

- Plugin Organizer by Jeff Sterup. Deactivate plugin.
- <a href="dpress.org/plugins/woocommerce-checkout-manager/">WooCommerce Checkout Manager</a> breaks the currency selector during checkout. Deactivate plugin.

== Changelog ==

= 2.86 20210120 =

* New currency: CGBBank CGB
* New currency: Nexo Token NEXO

= 2.85 20210110 =

* New currency: Micro Tuber MCT

= 2.84 20201220 =

* New currency: InnovaMinex MINX
* New currency: Footballcoin XFC

= 2.83 20201027 =

* New currency: Crypto.com Coin CRO
* New currency: CryptoHours CHS
* New currency: MktCoin MLM
* New currency: Neutrino USD USDN
* New currency: VcashPay VCP
* Fix: Waves checkout link changed to new Waves exchange URL.

= 2.82 20200828 =

* Added payment spread option to EDD.

= 2.81 20200816 =

* New currency: TheCash TCH

= 2.80 20200713 =

* New currency: Devil's Dragon DDGN
* New currency: DigitexFutures DGTX
* New currency: Media Play Cash MPC

= 2.79 20200609 =

* New currency: CuraDAI CURA
* Fix: QR code warning in checkout javascript when QR codes are disabled.
* Tweak: A lot more debugging info when using WooCommerce.

= 2.78 20200404 =

* Fix: Increase compatibility with WC Subscriptions plugin.
* Tweak: Updated WC compatibility tag for v4.0. No other changes needed.

= 2.77 20200304 =

* New: Allow underpayment % for HD wallets. See: <a href="https://mycryptocheckout.com/doc/usage/hd-wallet-settings/">https://mycryptocheckout.com/doc/usage/hd-wallet-settings/</a>

= 2.76 20200223 =

* Fix: Allow for erc20 transfers with new Metamask.

= 2.75 20200219 =

* Fix: Updated Web3.js version for Metamask (Ethereum) to 1.2.5.

= 2.74 20200212 =

* New currency: FRTS Coin FRTS
* New: Added ENS Ethereum wallet address support.
* Added experimental payment spread function to the WooCommerce gateway, which helps webshops with 100s of sales per minute.

= 2.73 20200102 =

* New currency: EOS
* New currency: Binance Coin BNB
* New currency: Jobchain JOB
* New currency: Traders Token TRDS
* Fix: WooCommerce: Redirect online "pay now" links directly to "order received" page, since the payment method is already chosen.

= 2.72 20191209 =

* Fix: New URL for Waves checkout, pointing to the waves.exchange.

= 2.71 20191203 =

* New currency: ARightMesh RMESH
* New currency: ATBC Mart Token TMT
* Switched DAI to "Multi-collateral DAI" version

= 2.70 20191125 =

* Fix: Expiration notice displayed unnecessarily in some cases.

= 2.69 20191121 =

* New currency: Sinovate SIN
* New: Added communication test function under tools. Used to check for plugin conflicts preventing the MCC API from correctly communicating with your install.
* New: Added license expiration notice.

= 2.68 20191115 =

* New currency: ABYCoin ABYCOIN
* New currency: FREE Coin
* New currency: PLA Planet
* New currency: SPAZ Swapcoinz

= 2.67 20191016 =

* Fix: Metamask not opening in Chrome.

= 2.66 20191016 =

* New currency: Golfcoin GOLF
* New currency: Heat HEAT
* New currency: Enix ENIX
* New currency: Peony PNY

= 2.65 20190923 =

* New currency: Space Crowns SMC
* Fix: Clarify that no payments will be processed in test mode.

= 2.64 20190819 =

* New currency: Connect Coin XCON

= 2.63 20190805 =

* New currency: Kubo Coin KUBO
* New licensing: All subdomains, subdirectory installs and the main domain will all share the same license.

= 2.62 20190731 =

* New currency: Best Token BEST
* New currency: ChainLink Token LINK
* New currency: Hype Token HYPE

= 2.61 20190728 =

* Fix: Handle the WAVES payments redirects, bringing the buyer back to the order received page.

= 2.60 20190716 =

* New currency: BitTorrent BTT
* New currency: CopyrightCoins CCIM
* New currency: Decentraland MANA
* New currency: MeroCoin MERO
* New currency: Polymath Network POLY

= 2.59 20190616 =

* New currency: Dogecoin DOGE

= 2.58 20190615 =

* New currency: Ultragate ULG
* Fix: WooCommerce - Allow for manual payments, including WooCommerce Subscriptions support.

= 2.57 20190607 =

* New currency: Spider VPS SPDR

= 2.56 20190529 =

* New currency: 99 Masternodes NMN

= 2.55 20190521 =

* Fix: When cancelling an order, keep the MCC order ID for future use.

= 2.54 20190507 =

* New currency: SmartCash SMART

= 2.53 20190429 =

* New currency: Pi Edutainment Global PIEG
* New currency: Presearch PRE
* Fix: MCC will attempt an autosettlement in cases where customer sends wrong amount for order.

= 2.52 20190420 =

* New: Add labels for wallets and autosettlements, if you wish to give the items descriptions to help you remember them.
* Fix: Admin currency table more responsive on smaller screens.

= 2.51 20190418 =

* Fix: Metamask privacy mode now supported.

= 2.50 20190416 =

* New: Fiat Autosettlement enables you to connect to exchanges and instantly convert received coins into fiat or stablecoins ( $USD, $USDC, $USDT, $TUSD ). Supported exchanges: Binance, and Bittrex.
