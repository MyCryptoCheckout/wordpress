## Introduction

This is the PHP API for the MyCryptoCheckout service. The API uses JSON objects for communication.

To use the API you will have to implement:

* Communication (GET POST requests)
* Storage
* Marking of payments as completed or canceled

There is already a complete Wordpress implementation included. To see the API in action, please see the [MyCryptoCheckout Wordpress plugin](https://wordpress.org/plugins/mycryptocheckout/).

## Requirements

* Composer
* PHP 5.6

## Installation

`composer require mycryptocheckout/api`

## The API components

The **API** component is the main class. It handles communication with the API server and provides basic services to the other components such as data storage and retrieval.

The **Account** component provides functions relating to the account data that is stored. Currency information, exchange rates, payment information, reserved amounts, etc.

The **Payments** component handles adding and canceling of payment data into the API server. It receives messages from the API component regarding canceling or completion of payments.

### Other classes ###

**Client_Account_Data** is what is sent to the server during account retrieval, containing information about the installation.

**Payment** contains data about the order that is sent to the server: the currency, amount, recipient wallet address, etc. It has a $data property that can be used to store arbitrary properties.

**Payment_Data** is a smart data handler for use within the Payment class.

## Communication with the API server

This section explains the theory behind communication with the server. The basic principles are the following:

* Communication is done by sending JSON objects back and forth.
* The client has a secret key.
* The API server knows the client's secret key.
* The key is used by both the client and the client to authorize themselves.
* The client must keep track of its own account data.
* The client can request its account data from the server.
* This *retrieve_account* request uses a *retrieve_key* to ensure that *only* the server is the one sending the client the account data.
* The client sends Payment info to the server.
* The server sends payment cancellation / completion updates to the client.
* The secret key ensures that *only* the server can send payment updates to the client.

## Usage ##

For the sake of keeping this document flexible, when referring to the namespace basic namespace mycryptocheckout\api\vx, the x is the version number of the API you wish to implement.

The base classes are mostly abstract, requiring you to implement various methods.

### API ###

Begin by creating a class that extends *mycryptocheckout\api\vx\API*. You will have up implement the following methods:

* delete_data - deletes data from persistent storage.
* get_data - retrieves data from persistent storage.
* get_client_url - return the URL that this server users.
* is_retrieve_key_valid - returns whether the specified retrieve key matches the one stored in storage.
* save_data - stores data into persistent storage.
* send_get - send a GET request to a URL.
* send_post - send a POST request, with a data array, to a URL.

After creating your API class, you can instantiate it.

`$api = new \mytest\api\API();`

The API can then create other components.

`$account = $api->account();`
`$payments = $api->payments();`

Since messages are sent from the server, the client must be able to process them if they arrive:

`$api->maybe_process_messages();`

The above method will check the `$_POST` variable for any messages related to MyCryptoCheckout and will process them. If the `$_POST` doesn't contain anything from the server, it will just return.

### Account ###

The account component handles retrieval of the account data from the server. To fetch or update the account data:

`$account->retrieve();`

This will result in the following events:

* A retrieve_key is generated locally.
* The key is placed in Client_Account_Data.
* The data is sent to the server.
* The server replies by contacting the client in another thread with the new account data.
* Since this is done in another thread, the retrieve_key must be placed somewhere where the two thread can both access it.
* The new thread examines the new data and checks to see if the retrieve_key matches the one stored.
* If so, store the new data.
* Original thread receives OK from the server. All done.

The reason for the server sending the data in a separate call is to ensure that:

* The client URL is correct
* Sender is not spoofing

After the data is retrieved, you can access the various parts.

`$account->get_currency_data();`
or
`$account->has_payments_left();`

See the *Account.php* file for more methods.

### Payments ###

The Payments component handles sending of payment data to the server and handling of payment updates.

A *payment* for MyCryptoCheckout is an expected transaction on the blockchain. It has a unique ID that the server will send back to the client when a payment is created, and when the status of a payment changed. This unique ID is what you need to store.

You should also store the other Payment information, such as the expected amount, currency, wallet address, etc.

Your class will extend the Payments class and have to implement the following:

* cancel_local - The server is telling the client to mark a payment as canceled.
* complete_local - The server is telling the client to mark a payment as completed.

Begin by creating a `Payment` object.

`$payment = $api->payments()->create_new();`

Then fill it with data:

`$payment->set_currency_id( 'BTC' );`
`$payment->set_amount( 50 );`
`$payment->set_to( 'my_wallet_address' );`

And then send it to the API.

Before setting the amount, check that the amount is not already reserved by another transcation. The account data stores an array of reserved amounts for each currency. If the amount you desire is reserved, increase it by a decimal point until you find an unreserved amount.

`$payment_id = $api->payments()->add( $payment );`

Store the `$payment_id` somewhere related to whatever is being sold. In Wordpress WooCommerce, this would be the order meta.

Do you need to cancel the payment on the server?

`$api->payments()->cancel( $payment_id );`

The server can cancel a payment due to it becoming to old. The server will send a `cancel_payment` message, together with the payment ID. Your implementation of the `cancel_local` method will receive a Payment object with the `payment_id` property set.

The same applies for detected payments: the client will receive a `complete_payment` message, a payment ID and transaction ID. Your implementation of the `complete_local` method will receieve a Payment object with the `payment_id` and `transaction_id` properties set.

## End of document ##

If you have any questions, please contact us on [mycryptocheckout.com](https://mycryptocheckout.com]).
