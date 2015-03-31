<?php

function ipHasChanged( $currentIp )
{
    $fileName = dirname( __FILE__ ) . '/linode-dyndns.ip';
    if ( !file_exists( $fileName )) touch( $fileName );
    if ( is_writable( $fileName ))
    {
        $oldIp = file_get_contents( $fileName );
        if ( $oldIp != $currentIp )
        {
            if ( $fileStream = fopen( $fileName, 'w' ))
            {
                fwrite( $fileStream, $currentIp );
                fclose( $fileStream );
                return file_get_contents( $fileName );
            }
            else echo "Cannot open file for writing\n";
        }
    }
    else echo "Cannot write to IP address file\n";
    return false;
}

function readConfig()
{
    $configFileName = dirname( __FILE__ ) . '/linode-dyndns.conf';
    if ( !file_exists( $configFileName ))
    {
        echo "First configure this script by copying linode-dyndns.conf-dist to linode-dyndns.conf and changing the values\n";
        exit;
    }
    $configLines = file( dirname( __FILE__ ) . '/linode-dyndns.conf', FILE_IGNORE_NEW_LINES );
    $config = array();
    foreach ( $configLines as $line )
    {
        list( $key, $value ) = explode( '=', $line );
        $config[$key] = $value;
    }
    return $config;
}

function updateLinodeDns( $ip, $config )
{
    $apiCallStr = $config['API_URL'] . '/?'
                   . 'api_key=' . $config['API_KEY']
                   . '&domainid=' . $config['DOMAIN_ID'];
    $updateActionSuffix = '&api_action=domain.resource.update&target=[remote_addr]&resourceid=';
    $resources = explode( ',', $config['RESOURCES'] );
    foreach ( $resources as $resourceId )
    {
        $url = $apiCallStr . $updateActionSuffix . $resourceId;
        file_get_contents( $url );
    }
    return file_get_contents( $apiCallStr . '&api_action=domain.resource.list' );
}

$config = readConfig();
$ip = file_get_contents( $config['IP_CHECK_URL'] );
if ( $ip && ipHasChanged( $ip ))
{
    $status = updateLinodeDns( $ip, $config );
    if ( $config['NOTIFICATIONS'] )
    {
        $to = $config['NOTIFICATION_EMAIL_TO'];
        $subject = "Home IP address has been changed: " . $ip;
        $headers = 'From: ' . $config['NOTIFICATION_EMAIL_FROM'];
        mail( $to, $subject, $status, $headers );
    }
}

