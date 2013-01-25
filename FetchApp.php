<?php

/**
 * This class connects to the FetchApp API
 *
 * @author Jason T. Wong <developer@jasontwong.com>
 *
 */
class FetchApp
{
    // {{{ constants
    /**
     * FetchApp Version
     */
    const VERSION = '1.0';

    /**
     * Version of Fetch API this was designed for
     */
    const API_VERSION = '2.0';
    // }}}
    // {{{ properties
    protected static $api = array();
    protected static $endpoints = array(
        'account',
        'downloads',
        'new_token',
        'orders',
        'products'
    );
    // }}}

    // {{{ public function __construct( $uri, $key, $token )
    /**
     * This is the constructor class
     *
     * @param string $uri API URI
     * @param string $key API Key
     * @param string $token API Token
     * @return void
     *
     */
    public function __construct( $uri, $key, $token )
    {
        self::$api = array(
            'uri' => $uri,
            'key' => $key,
            'token' => $token
        );
    }
    // }}}
    // {{{ public function __call( $name, $args )
    /** 
     * Magic method which chooses the proper API call
     *
     * @param string $name
     * @param array $args
     * @return SimpleXMLObject|FALSE
     */
    public function __call( $name, $args )
    {
        if ( in_array( $name, self::$endpoints ) )
        {
            $arg_idx = 0;
            $extras = array();
            $post_data = NULL;
            $request['page'] = '/' . $name;
            $request['method'] = 'GET';

            // {{{ build request endpoint
            foreach ( $args as $k => &$arg )
            {
                if ( is_array( $arg ) || ( is_object( $arg ) && get_class( $arg ) === 'SimpleXMLElement' ) )
                {
                    $extras = $arg;
                    break;
                }
                $request['page'] .= '/' . $arg;

                // change request method based on endpoint
                switch ( $arg )
                {
                    case 'create':
                    case 'send_email':
                        $request['method'] = 'POST';
                    break;
                    case 'delete':
                        $request['method'] = 'DELETE';
                    break;
                    case 'update':
                        $request['method'] = 'PUT';
                    break;
                }
            }
            // }}}
            // {{{ build page query or post data
            if ( $extras )
            {
                if ( is_array( $extras ) )
                {
                    $request['page'] .= '?' . http_build_query( $extras );
                }
                else
                {
                    $post_data = $extras->asXML();

                }
            }
            // }}}

            $req_args = array(
                $request
            );

            if ( isset( $post_data ) )
            {
                $req_args[] = $post_data;
            }

            $data = call_user_func_array( array( $this, 'send_request' ), $req_args );
            return simplexml_load_string( $data );
        }
        throw new FetchAppException( 'That method does not exist' );
    }
    // }}}

    // {{{ protected function send_request( $request, $data = NULL )
    /**
     * This function handles the curl call to the API endpoint
     *
     * @param array $request API endpoint and method
     * @param array|NULL $post_data data to send to the endpoint
     * @return string XML response from endpoint
     */
    protected function send_request( $request, $data = NULL )
    {
        $request_uri = '/api/v2' . $request['page'];
        $credentials = self::$api['key'] . ':' . self::$api['token'];

        $headers = array(
            'Content-type: application/xml',
            'Authorization: Basic ' . base64_encode( $credentials ),
        );

        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, self::$api['uri'] . $request_uri );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 600 );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $request['method'] );

        if ( !is_null( $data ) )
        {
            // Apply the XML to our curl call
            curl_setopt( $ch, CURLOPT_POST, 1 );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data ); 
        }

        $ch_data = curl_exec( $ch );

        if ( curl_errno( $ch ) )
        {
            throw new FetchAppException( curl_error( $ch ) );
        }
        curl_close( $ch );
        return $ch_data;
    }
    // }}}
}

class FetchAppException extends Exception
{
}
