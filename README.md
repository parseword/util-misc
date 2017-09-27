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
