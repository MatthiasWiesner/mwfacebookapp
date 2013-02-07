# mwfacebookapp

Hi Matthew,

I built this mini-app accordingly to your documentation. Basically I didn't change your code, but there are still some changes:

* get the facebook credentials from config addon
* seperate the HTML parts from the PHP parts to get a nicer documentation (in my opinion ;)

## facebook credentials

after creating the application you have to set the credentials by:
~~~bash
cctrlapp APP_NAME/default addon.add config.free --APP_ID=YOUR_APP_ID --SECRET_KEY=YOUR_SECRET_KEY
~~~

then, in the `index.php`
~~~php
function getFacebookConfig(){
    $facebookCredentials = array(
        'appUrl' => "http://apps.facebook.com/mwfacebookapp",
        'cookies' => 'true',        
    );
    if (!empty($_SERVER['HTTP_HOST']) && isset($_ENV['CRED_FILE'])) {
        $string = file_get_contents($_ENV['CRED_FILE'], false);
        if ($string == false) {
            throw new Exception('Could not read credentials file');
        }
        $creds = json_decode($string, true);
        $error = json_last_error();
        if ($error != JSON_ERROR_NONE){
            $json_errors = array(
                JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
                JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
                JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
                JSON_ERROR_SYNTAX => 'Syntax error',
                JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
            );
            throw new Exception(sprintf('A json error occured while reading the credentials file: %s', $json_errors[$error]));
        }
        if (!array_key_exists('CONFIG', $creds)){
            throw new Exception('No Config credentials found. Please make sure you have added the config addon.');
        }
        $facebookCredentials['appId'] = $creds['CONFIG']['CONFIG_VARS']['APP_ID'];
        $facebookCredentials['secret'] = $creds['CONFIG']['CONFIG_VARS']['SECRET_KEY'];
    }
    return $facebookCredentials;
}
~~~

## seperate html from php

I think this documentation can be written more clearly, if you seperate the html parts from the php parts.
Basically, this works by capturing the output with `ob_start`, `ob_get_contents` and `ob_end_clean` and including the files in the `views` folder. All this output will be captured and assigned to the `$content` variable and finally printed in the `views/base.php` file.

For example:

in `index.php`
~~~php
ob_start();

... call another function
if (!empty($applicationList)) {
    include 'views/applist.php';
} else {
    $errormsg = "No applications available";
    include 'views/error.php';
}
...

// catch everything that has been printed (or included views)
$content = ob_get_contents();

ob_end_clean();
// finally, we show altogether
include 'views/base.php';
~~~

## remarks

the picture in your origin documentation show the cloudcontrol's appname with a dash "cloudcontrol-hw". On cloudControl you cannot create an application with this name, only the [a-z0-9] chars are allowed. Can you change it in your picture?

I renamed `src` folder to `lib` to avoid confusions.

## finally...

I think it is a very good idea to making this documentation! __Thank you__.
