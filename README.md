# linode-dyndns
PHP script using the Linode API for assigning a domain to a machine with a dynamic IP address

The purpose of this script is not to hammer the Linode API with update requests that aren't necessary if the IP address hasn't been changed. The script checks whether the address has changed before making calls to the API. If the address did change, it also sends an email if it's configured to do that.

To set this up, follow http://travismaynard.com/writing/dynamic-dns-using-the-linode-api and change the command in the cron job to execute this script instead. Note that you also have to copy the linode-dyndns.conf-dist file to linode-dyndns.conf and enter your values for the script to work correctly.
