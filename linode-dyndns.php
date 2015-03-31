<?php

function ipHasChanged( $currentIp )
{
    $fileName = 'linode-dyndns.ip';
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
        }
    }
    return false;
}

function readConfig()
{
    $configFileName = 'linode-dyndns.conf';
    if ( !file_exists( $configFileName ))
    {
        echo "First configure this script by copying linode-dyndns.conf-dist to linode-dyndns.conf and changing the values\n";
        exit;
    }
    $configLines = file( 'linode-dyndns.conf', FILE_IGNORE_NEW_LINES );
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
        file_get_contents( $apiCallStr . $updateActionSuffix . $resourceId );
    }
    return file_get_contents( $apiCallStr . '&api_action=domain.resource.list' );
}

$ip = file_get_contents( 'http://phihag.de/ip/' );
if ( $ip && ipHasChanged( $ip ))
{
    $config = readConfig();
    $status = updateLinodeDns( $ip, $config );
    if ( $config['NOTIFICATIONS'] )
    {
        $to = $config['NOTIFICATION_EMAIL_TO'];
        $subject = "Home IP address has been changed: " . $ip;
        $headers = 'From: ' . $config['NOTIFICATION_EMAIL_FROM'];
        mail( $to, $subject, $status, $headers );
    }
}

