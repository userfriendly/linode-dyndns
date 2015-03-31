# linode-dyndns
PHP script using the Linode API for assigning a domain to a machine with a dynamic IP address

The purpose of this script is not to hammer the Linode API with update requests that aren't necessary if the IP address hasn't been changed.

The script checks whether the address has changed before making calls to the API.

If the address did change, it also sends an email if it's configured to do that.
