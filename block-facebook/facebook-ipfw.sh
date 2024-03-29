#!/usr/local/bin/bash
#
# Known Facebook IP ranges announced by AS32934, AS54115, AS63293
# ipfw firewall script
# To change the ipfw rule number, edit the RULE_NUMBER variable declaration.
# Data source: BGP
# Last update: 2024-02-18
# <https://github.com/parseword/util-misc/raw/master/block-facebook/facebook-ipfw.sh>
#

RULE_NUMBER="71"

if [[ ! `ipfw show ${RULE_NUMBER} 2>&1 | head -1` =~ .*exist.* ]]
then
   echo "Flushing existing rule ${RULE_NUMBER}"
   ipfw delete ${RULE_NUMBER}
fi

#begin-ipv4
for IPV4CIDR in           \
    "31.13.24.0/21"       \
    "31.13.64.0/18"       \
    "45.64.40.0/22"       \
    "57.144.0.0/14"       \
    "66.220.144.0/20"     \
    "69.63.176.0/20"      \
    "69.171.224.0/19"     \
    "74.119.76.0/22"      \
    "102.132.96.0/20"     \
    "102.132.112.0/21"    \
    "102.132.120.0/24"    \
    "102.132.122.0/23"    \
    "102.132.125.0/24"    \
    "102.132.126.0/23"    \
    "102.221.188.0/22"    \
    "103.4.96.0/22"       \
    "129.134.0.0/17"      \
    "129.134.128.0/24"    \
    "129.134.130.0/23"    \
    "129.134.132.0/24"    \
    "129.134.135.0/24"    \
    "129.134.136.0/22"    \
    "129.134.140.0/24"    \
    "129.134.143.0/24"    \
    "129.134.144.0/24"    \
    "129.134.147.0/24"    \
    "129.134.148.0/23"    \
    "129.134.150.0/24"    \
    "129.134.154.0/23"    \
    "129.134.156.0/22"    \
    "129.134.160.0/24"    \
    "129.134.163.0/24"    \
    "129.134.164.0/23"    \
    "129.134.168.0/21"    \
    "129.134.176.0/23"    \
    "129.134.180.0/24"    \
    "129.134.183.0/24"    \
    "129.134.184.0/23"    \
    "157.240.0.0/17"      \
    "157.240.128.0/24"    \
    "157.240.157.0/24"    \
    "157.240.158.0/23"    \
    "157.240.169.0/24"    \
    "157.240.170.0/24"    \
    "157.240.174.0/23"    \
    "157.240.176.0/23"    \
    "157.240.179.0/24"    \
    "157.240.181.0/24"    \
    "157.240.182.0/24"    \
    "157.240.192.0/18"    \
    "163.70.128.0/17"     \
    "163.77.128.0/17"     \
    "163.114.128.0/20"    \
    "173.252.64.0/18"     \
    "179.60.192.0/22"     \
    "185.60.216.0/22"     \
    "185.89.216.0/22"     \
    "199.201.64.0/22"     \
    "204.15.20.0/22"      \
;
do
    ipfw add ${RULE_NUMBER} reset log ip from any to ${IPV4CIDR}
    ipfw add ${RULE_NUMBER} reset log ip from ${IPV4CIDR} to any
done
#end-ipv4
#begin-ipv6
for IPV6CIDR in                 \
    "2620:10d:c090::/44"        \
    "2a03:2880::/32"            \
    "2a03:2887:ff02::/47"       \
    "2a03:2887:ff19::/48"       \
    "2a03:2887:ff1b::/48"       \
    "2a03:2887:ff1c::/46"       \
    "2a03:2887:ff23::/48"       \
    "2a03:2887:ff25::/48"       \
    "2a03:2887:ff27::/48"       \
    "2a03:2887:ff28::/46"       \
    "2a03:2887:ff2f::/48"       \
    "2a03:2887:ff30::/48"       \
    "2a03:2887:ff35::/48"       \
    "2a03:2887:ff37::/48"       \
    "2a03:2887:ff38::/46"       \
    "2a03:2887:ff3f::/48"       \
    "2a03:2887:ff40::/48"       \
    "2a03:2887:ff43::/48"       \
    "2a03:2887:ff44::/47"       \
    "2a03:2887:ff48::/46"       \
    "2a03:2887:ff4d::/48"       \
    "2a03:2887:ff4e::/47"       \
    "2a03:2887:ff50::/47"       \
    "2a03:2887:ff52::/48"       \
    "2a03:2887:ff55::/48"       \
    "2a03:2887:ff58::/47"       \
    "2a03:2887:ff60::/48"       \
    "2c0f:ef78:1::/48"          \
    "2c0f:ef78:3::/48"          \
    "2c0f:ef78:5::/48"          \
    "2c0f:ef78:6::/48"          \
    "2c0f:ef78:9::/48"          \
    "2c0f:ef78:d::/48"          \
    "2c0f:ef78:e::/47"          \
    "2c0f:ef78:10::/47"         \
    "2c0f:ef78:12::/48"         \
;
do
    ipfw add ${RULE_NUMBER} reset log ip from any to ${IPV6CIDR}
    ipfw add ${RULE_NUMBER} reset log ip from ${IPV6CIDR} to any
done
#end-ipv6
