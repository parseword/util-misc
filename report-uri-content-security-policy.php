<?php
/*
 * Copyright 2017-2018 Shaun Cummiskey, <shaun@shaunc.com> <https://shaunc.com>
 * <https://github.com/parseword/util-misc/>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and 
 * limitations under the License.
 */
 
/*
 * This script accepts incoming Content-Security-Policy reports and emails their
 * contents to the site administrator. For more about this header, see:
 *
 * <https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP>
 * <https://www.owasp.org/index.php/Content_Security_Policy_Cheat_Sheet>
 *
 * Point to this script in the "report-uri" parameter of your header.
 *
 * Some browsers send an OPTIONS request first, prior to POSTing the report,
 * to ensure the server is willing to accept a POST in the first place. This 
 * is known as a "preflight," as defined here:
 *
 * <https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS#Preflighted_requests>
 *
 * The appropriate response will be sent depending upon the HTTP verb.
 */

//Import ADMINISTRATOR_RECIPIENT (or define your own recipient below)
require_once('/etc/config/admin.conf');
//define('ADMINISTRATOR_RECIPIENT', 'www-meta@example.com');

//Report signatures that we don't want to be emailed about
$filter_scripts = [
    //Privacy badger injects these scripts into every page
    'const V8_STACK',
    'if (window.google && (window.google.sn',
    //This appears to be uBlock Origin and similar plugins 
    "Object.defineProperty(window, \'ysmm\',",
    //Kaspersky Internet Security
    'window.klTabId_kis',
    //Adblock Plus <https://issues.adblockplus.org/ticket/5953#comment:6>
    '(function injected(eventName, injectedIn',
    //Adblock Plus on Safari
    'safari-extension://org.adblockplus.adblockplussafari',
    //This is either an Adblock blocker, or an Adblock blocker blocker (trace 
    //buster buster!), maybe <https://greasyfork.org/en/scripts/15947-adblock/code>
    'var BlockAdBlock = function',
    'var FuckAdBlock = function',
    //Vim Vixen <https://addons.mozilla.org/en-US/firefox/addon/vim-vixen/>
    '.vimvixen',
    //Unknown
    'makes some black text light colored',
    //ChromeIPass extension <https://github.com/pfn/passifox/blob/master/chromeipass/>
    '.cip-genpw-icon.cip-icon-key-small',
    //Ghostery
    '@media print {#ghostery-purple-box',
    //Evernote Clipper extension <https://evernote.com/products/webclipper>
    'Copyright 2014 Evernote Corporation',
];

//Document URIs and/or blocked URIs that we don't want to be emailed about
$filter_uris = [
    //Don't generate reports when Google Translate embeds an image
    'https://www.gstatic.com/images/branding/product/2x/translate_24dp.png',
    //Don't report when visitors have forced a Google font through a user style
    'https://fonts.gstatic.com/',
    //In Firefox, "View Source" generates a CSP violation
    //<https://bugs.chromium.org/p/chromium/issues/detail?id=699108>
    'view-source',
];

//Test for a CORS preflight
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    
    //Confirm we accept POST in addition to OPTIONS
    header('Access-Control-Allow-Methods: OPTIONS, POST');
    
    //Numerous domains point here, so indicate that any origin is acceptable
    header('Access-Control-Allow-Origin: *');
    
    //Explicitly allow whatever custom headers, if any, the requestor hinted
    foreach (getallheaders() as $key=>$val) {
        if (strcasecmp($key, 'Access-Control-Request-Headers') == 0) {
            header('Access-Control-Allow-Headers:' . htmlentities($val, ENT_QUOTES));
            break;
        }
    }
    exit;
}

//Test for a posted report
elseif ($_SERVER['REQUEST_METHOD'] == 'POST'
    && strlen($json = @file_get_contents('php://input')) > 0) {
        
    //Turn the JSON report into an array
    $report = json_decode($json, true);
    
    //See if we want to suppress this report based on script-sample
    if (count($filter_scripts) > 0 && !empty($report['csp-report']['script-sample'])) {
        foreach ($filter_scripts as $filter) {
            if (strpos($report['csp-report']['script-sample'], $filter) !== false) {
                exit;
            }
        }
    }
    
    //See if we want to suppress this report based on document-uri
    if (count($filter_uris) > 0 && !empty($report['csp-report']['document-uri'])) {
        foreach ($filter_uris as $filter) {
            if (strpos($report['csp-report']['document-uri'], $filter) !== false) {
                exit;
            }
        }
    }
    
    //See if we want to suppress this report based on blocked-uri
    if (count($filter_uris) > 0 && !empty($report['csp-report']['blocked-uri'])) {
        foreach ($filter_uris as $filter) {
            if (strpos($report['csp-report']['blocked-uri'], $filter) !== false) {
                exit;
            }
        }
    }
    
    //Flatten the array into a string
    $report = var_export($report, true);

    //Grab the headers, too
    $headers = var_export(getallheaders(), true);
    
    //Build a message
    $body = <<<EOT
    A Content-Security-Policy report was posted by {$_SERVER['REMOTE_ADDR']}.
    
    Headers follow:
    
    $headers
    
    Report follows: 
    
    $report
EOT;
    
    //Send the email
    @mail(ADMINISTRATOR_RECIPIENT, 
        '[' . $_SERVER['SERVER_NAME'] . '] Content-Security-Policy Report',
        $body, 'From: ' . ADMINISTRATOR_RECIPIENT);
    exit;
}
