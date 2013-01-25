fetchapp
========
This is a library for the [FetchApp](http://www.fetchapp.com/) version 2 API

Usage
=====
This API is essentially a URI builder. You can use one of the "top level" endpoints (orders, account, downloads, products, new_token) as a function call. Every parameter passed to the function will append to the endpoint unless it is an Array or a SimpleXMLElement, which would then be passed to the API as POST data. The examples below will use the "orders" endpoint as it has all the possible combinations needed for the FetchApp API.

Require the library for use and instantiate:

    require 'FetchApp.php';
    $fetch_app = new FetchApp( 'https://demo.fetchapp.com', 'demokey', 'demotoken' );

Create an Order
---------------
This passes a SimpleXMLElement object as the last parameter so it knows to stop the URL building.

    $new_order = simplexml_load_file( 'order_create.xml' );
    $order_create_response = $fetch_app->orders( 'create', $new_order );

Modify an Order
---------------
    $update_order = simplexml_load_file( 'order_update.xml' );
    $order_update_response = $fetch_app->orders( '1', 'update', $update_order);
    $order_delete_response = $fetch_app->orders( '1', 'delete' );
    $order_expire_response = $fetch_app->orders( '1', 'expire' );

Send an email of an Order
-------------------------
This is how you can pass query params to an API call.

    $params = array(
        'expiration_date' => '2011-03-07T15:08:02+00:00',
        'download_limit' => '10',
    );
    $order_send_response = $fetch_app->orders( '1', 'send_email', $params );

Get information about an Order(s)
---------------------------------
    $orders = $fetch_app->orders();
    $single_order = $fetch_app->orders( 'M0001', 'stats' );

Get information of Order Items in an Order
------------------------------------------
This shows how you can add or change paramters easily to get different results

    $order_items = $fetch_app->orders( 'M0001', 'order_items' );
    $single_order_item = $fetch_app->orders( 'M0001', 'order_items', '1056064' );
    $downloads_of_single_order_item = $fetch_app->orders( 'M0001', 'order_items', '1056064', 'downloads' );
    $files_of_single_order_item = $fetch_app->orders( 'M0001', 'order_items', '1056064', 'files' );

Things to note
==============
- The main endpoints are hard coded. This is done because there is no endpoint for the listing of other endpoints. Also, in order for the magic method to properly block endpoints that don't exist. This could be changed in the future to leave it open and throw an exception if the data returned from the call is not an XML string.
- This sends the proper HTTP command (PUT, GET, POST, etc) depending on what is found in the parameters (e.g. POST for "create" and PUT for "update"). These will need to be modified if new types of commands get added or changed in any way.
