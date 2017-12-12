<?php
/*
 * Copyright 2017 Shaun Cummiskey, <shaun@shaunc.com> <http://shaunc.com>
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
 * This script accepts incoming Expect-CT compliance reports and emails their
 * contents to the site administrator. For more about the Expect-CT header, see:
 *
 * <https://tools.ietf.org/html/draft-ietf-httpbis-expect-ct-02>
 * <https://scotthelme.co.uk/a-new-security-header-expect-ct/>
 *
 * Point to this script in the "report-uri" parameter of your Expect-CT header.
 *
 * Some browsers send an OPTIONS request first, prior to POSTing the report,
 * to ensure the server is willing to accept a POST in the first place. This 
 * is known as a "preflight," as defined here:
 *
 * <https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS#Preflighted_requests>
 *
 * The appropriate response will be sent depending upon the HTTP verb.
 */

//Import ADMINISTRATOR_RECIPIENT (or define your own recipient below, //shaunc)
require_once('/etc/config/admin.conf');

//Test for a CORS preflight
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    
    //Confirm we accept POST in addition to OPTIONS
    header('Access-Control-Allow-Methods: OPTIONS, POST');
    
    //Numerous domains point here, so indicate that any origin is acceptable
    header('Access-Control-Allow-Origin: *');
    
    //Explicitly allow whatever custom headers, if any, the requestor hinted
    foreach (getallheaders() as $key=>$val) {
        if (strcasecmp($key, 'Access-Control-Request-Headers') == 0) {
            header('Access-Control-Allow-Headers:' . $val);
            break;
        }
    }
    exit;
}

//Test for a posted report
elseif ($_SERVER['REQUEST_METHOD'] == 'POST'
    && strlen($json = @file_get_contents('php://input')) > 0) {
        
    //Turn the JSON report into a slightly more readable array
    $report = var_export(json_decode($json), true);

    //Grab the headers, too
    $headers = var_export(getallheaders(), true);
    
    //Build a message
    $body = <<<EOT
    An Expect-CT compliance report was posted by {$_SERVER['REMOTE_ADDR']}.
    
    Headers follow:
    
    $headers
    
    Report follows: 
    
    $report
EOT;
    
    //Send the email
    @mail(ADMINISTRATOR_RECIPIENT, 
        '[' . $_SERVER['SERVER_NAME'] . '] Expect-CT Compliance Report',
        $body, 'From: ' . ADMINISTRATOR_RECIPIENT);
    exit;
}
