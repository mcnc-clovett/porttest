# Port Tester
## Description
These configuration files allow you to create a server that listens on all 65,535 TCP ports. It has been tested on Debian Linux 12. This project was inspired by [Marc Maurice's](http://marcmaurice.fr/) [portquiz.net](http://portquiz.net).

This implementation uses a [netfilter](https://www.nftables.org) firewall configuration to redirect all ports to the Nginx web server listening on port 80.

## Installation
### Initial set up
Install Nginx, PHP-fpm and git, then clone the repo
```sh
cd ~
apt install -y nginx php-fpm git
git clone https://github.com/mcnc-clovett/porttest.git
```

Copy firewall configuration to /etc
```sh
cd porttest
cp nftables.conf /etc/
```

Set up web server configuration
```sh
# Copy port test page into place
mkdir /var/www/porttest
cp index.php /var/www/porttest/
# Copy port test config into place
cp porttest /etc/nginx/sites-available/
cd /etc/nginx/sites-enabled/
rm default
ln -s ../sites-available/porttest .
```

### Firewall configuration
Use nano ( or your preferred editor) to edit the /etc/nftables.conf file.
```sh
nano /etc/nftables.conf
```

Edit the management network section to reflect network addresses or ranges that you wish to allow SSH access from.
```go
set management {
        type ipv4_addr
        counter
        flags interval
        elements = {
                # Network address or ranges, seperated by comma
                192.168.100.0/24
        }
}
```

Edit the trusted network section to reflect networks you wish to allow port testing from. Leave 0.0.0.0/0 to allow from any IPv4 source.
```go
set trusted {
        type ipv4_addr
        counter
        flags interval
        elements = {
                # Network address or ranges, seperated by comma
                0.0.0.0/0
        }
}
```

Edit the banned IP section with address or networks that are abusive or that you wish to deny access to.
```go
set bannedips {
        type ipv4_addr
        counter
        flags interval
        elements = {
                # Network address or ranges, seperated by comma
                192.0.2.0/24,
                198.51.100.0/24,
                203.0.113.0/24
        }
}
```

Apply the firewall configuration. If any errors occur, check the configuration again. If you are connected to the server via SSH, ensure you can connect using a seperate SSH session after applying the config.
```sh
# Running the file applies the configuration
/etc/nftables.conf
```

### Web server configuration
Use nano ( or your preferred editor) to edit the /etc/nginx/sites-available/porttest file.
```sh
nano /etc/nginx/sites-available/porttest
```

Set the "server\_name" line to the URL you'll be using for the server.
```nginx
server_name porttest.example.com
```

Save and close the file, then reload the Nginx service.
```sh
systemctl reload nginx
```

### Testing the server
You should now be able to visit the server in a browser at http://\<ipaddress\>:\<port\>
