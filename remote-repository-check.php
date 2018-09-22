#!/usr/local/bin/php
<?php
/*
 * Copyright 2016 Shaun Cummiskey, <shaun@shaunc.com> <https://shaunc.com>
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
 * This script runs "svn status" or "git status" against any number of remote
 * repositories, and sends an email notification if any repository isn't clean,
 * providing an early warning of potential website compromise or file tampering.
 *
 * To run non-interactively (as a cron job), you'll need to set up passwordless
 * key-based SSH across all of the involved servers. For more information, see
 * the article "Website integrity monitoring through version control" at:
 * https://shaunc.com/go/kjj6wxDh18gS
 */

//Mail configuration
define('MAIL_FROM', 'administrivia@it.example.com');
define('MAIL_SUBJ', '[critical] Repository is not pristine');
define('MAIL_RCPT', 'operations@it.example.com,pager@it.example.com');

//Mail message body
$message = null;

//Remote repository configuration. Define an array [] for each repository.
//Valid 'type' values are either 'svn' or 'git'. 'user' is the user the
//script will use to invoke ssh to 'host', inspecting the repo at 'path'.
$repos = [
    //Example subversion repository
    [
        'type' => 'svn',
        'user' => 'opsuser',
        'host' => 'aws-instance-1234.example.com',
        'path' => '/var/www/example.com/htdocs',
    ],
    //Example git repository
    [
        'type' => 'git',
        'user' => 'root',
        'host' => 'static04.example.com',
        'path' => '/home/web/static04.example.com',
    ],
];

//Check the status of each repository
foreach ($repos as $repo) {

    //Common command prefix regardless of target or repo type
    $cmd = "ssh -t {$repo['user']}@{$repo['host']} ";

    //Build the rest of the command based on the repo type
    switch ($repo['type']) {
        case 'svn':
            $cmd .= "'svn status {$repo['path']}' 2>/dev/null";
            break;
        case 'git':
            $cmd .= "'cd {$repo['path']} && git status' 2>/dev/null";
            break;
        default:
            $message .= 'Unrecognized repo type ' . join(' ', $repo);
            continue;
    }

    //Execute the command
    $output = null;
    exec($cmd, $output);

    //svn status gives no output when nothing has changed
    if (empty($output)) {
        continue;
    }

    //git status says "working directory clean" when nothing has changed
    if (preg_match('|working directory clean|', join("\n", $output))) {
        continue;
    }

    //If we get here, this repository isn't clean
    $message .= "Changes detected in repository:\n" . join(' ', $repo);
    $message .= "\n" . join("\n", $output) . "\n\n";
}

//If any repository wasn't clean, send an email notification
if (!empty($message)) {
    mail(MAIL_RCPT, MAIL_SUBJ, $message, 'From: ' . MAIL_FROM);
}
