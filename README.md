# util-misc
Miscellaneous utilities for Linux and FreeBSD, mostly written in PHP.

### find-repos

This script finds the top-level Subversion and Git repositories on a system. 
If you use Subversion 1.8 or newer, you don't really need this; you can get the 
same results with `locate .svn | grep -E "\/\.svn$"`. Older Subversion clients 
would litter repositories with many `.svn` subdirectories; the main purpose of 
find-repos is to filter those out.

If you xargs the output to `svn status`, you can look for "dirty" or uncommitted 
changes across your entire filesystem at once:

    [root@dallas03 /tmp]# findrepos svn | xargs svn status
    M       /etc/postfix/header_checks
    M       /etc/postfix/virtual
    ?       /etc/ssh/moduli.orig
    M       /usr/local/apache2/conf/httpd.conf

### nsa-dhs-news.php

A script to generate an RSS "news feed" full of DHS watchlist terms and NSA 
cryptonyms. For use with the [TrackMeNot](https://cs.nyu.edu/trackmenot/) browser 
extension.

### ports-update-check

This script checks for available ports updates on a FreeBSD system. It first 
brings the ports tree current using Subversion, then runs `portmaster -L` to 
query for updates. If any are found, you get an email notification containing 
the necessary `portmaster` commands to upgrade each port, like this:

    Updates are available for the following ports:
    
    libiconv-1.14_11
    perl5-5.24.3
    
    To upgrade all, run: portmaster -avw
    
    To upgrade individual ports,
    
    portmaster -Kvw libiconv-1.14_10
    portmaster -Kvw perl5-5.24.2

Drop the script into 
`/etc/periodic/daily` and set the execute bit.

You'll need to define a constant named `ADMINISTRATOR_RECIPIENT` and, optionally, 
one named `ADMINISTRIVIA_SUBJECT_PREPEND`. I keep these in an external config file 
that isn't included here.

### remote-repository-check.php

In my environments, every website is a checked-out copy of a version control 
repository. 

This script runs "svn status" or "git status" against any number of remote 
repositories, and sends an email notification if any repository isn't clean, 
providing an early warning of potential website compromise or file tampering. 
I suggest setting it as a cron job that runs at least hourly.

You need to have passwordless key-based SSH set up between your "monitor" 
server and any target servers. Configuring the script is straightforward, 
just follow the examples to define your remote repositories. For more, see 
the article [*Website integrity monitoring through version control*](https://shaunc.com/go/kjj6wxDh18gS).

### report-uri-content-security-policy.php

This script acts as a reporting endpoint for 
the [`Content-Security-Policy`](https://www.owasp.org/index.php/Content_Security_Policy_Cheat_Sheet) HTTP 
security header. If you run a website that sets this header, you can use this
script to receive error reports from your users' browsers. Place this file on
your server and set the `Content-Security-Policy` header's `report-uri` value to point there.
The script accepts and replies to both `OPTIONS` and `POST` requests.

You'll need to define a constant named `ADMINISTRATOR_RECIPIENT` and comment
out the `require_once()` line, I keep this and other constants in an external
config file that isn't included here.

### report-uri-expect-ct.php

This script acts as a reporting endpoint for 
the [`Expect-CT`](https://tools.ietf.org/html/draft-ietf-httpbis-expect-ct-02) HTTP 
security header. If you run a website that sets this header, you can use this 
script to receive error reports from your users' browsers. Place this file on 
your server and set the `Expect-CT` header's `report-uri` value to point there. 
The script accepts and replies to both `OPTIONS` and `POST` requests.

You'll need to define a constant named `ADMINISTRATOR_RECIPIENT` and comment 
out the `require_once()` line, I keep this and other constants in an external 
config file that isn't included here.

