<?php
/**
 * common.php
 *
 * @author     mobikwikpg
 * @copyright  mobikwik
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 */
//// Create user agent string
// User agent constituents (for cURL)
define( 'PF_SOFTWARE_NAME', 'PrestaShop' );
define( 'PF_SOFTWARE_VER', Configuration::get('PS_INSTALL_VERSION') );
define( 'PF_MODULE_NAME', 'mobikwikpg-Prestashop' );
define( 'PF_MODULE_VER', '1.0' );

// Features
// - PHP
$mbkFeatures = 'PHP '. phpversion() .';';

// - cURL
if( in_array( 'curl', get_loaded_extensions() ) )
{
    define( 'PF_CURL', '' );
    $mbkVersion = curl_version();
    $mbkFeatures .= ' curl '. $mbkVersion['version'] .';';
}
else
    $mbkFeatures .= ' nocurl;';

// Create user agrent
define( 'PF_USER_AGENT', PF_SOFTWARE_NAME .'/'. PF_SOFTWARE_VER .' ('. trim( $pbFeatures ) .') '. PF_MODULE_NAME .'/'. PF_MODULE_VER );

// General Defines
define( 'PF_TIMEOUT', 15 );
define( 'PF_EPSILON', 0.01 );



function mbkGetData()
{
    // Posted variables from ITN
    $mbkData = $_POST;

    // Strip any slashes in data
    foreach( $mbkData as $key => $val )
        $mbkData[$key] = stripslashes( $val );

    // Return "false" if no data was received
    if( sizeof( $mbkData ) == 0 )
        return( false );
    else
        return( $mbkData );
}


?>
