#!/usr/bin/env php
<?php

echo PHP_EOL . "  .htaccess Password generator" . PHP_EOL . PHP_EOL;

$dir = getcwd();

// Ignore the first argument
$argv = array_splice($_SERVER["argv"], 1);
$argc = count($argv);

// Switch action, depending on argument count
switch ($argc)
{
    case 2:
        generate($argv[0], $argv[1], null);
        break;

    case 3:
        generate($argv[0], $argv[1], $argv[2]);
        break;


    // No argument: show usage
    case 0:
        usage();
        break;

    default:
        usage("Please provide 2-3 arguments, {$argc} given.");
        break;
}

echo PHP_EOL . PHP_EOL;


function generate ($name, $user, $password)
{
    global $dir;
    if (is_null($password))
    {
        $passwordGenerated = true;
        $password = generatePassword();
    }
    else
    {
        $passwordGenerated = false;
    }

    echo "  Generate password" . PHP_EOL;


    if (is_file("{$dir}/.htpasswd"))
    {
        echo "  - Existing .htpasswd found" . PHP_EOL;
    }
    else
    {
        if (is_file("{$dir}/.htaccess"))
        {
            echo "  - Existing .htaccess found" . PHP_EOL;
            $htaccessContent = file_get_contents("{$dir}/.htaccess") . PHP_EOL . PHP_EOL;
        }
        else
        {
            echo "  - Generating new .htaccess" . PHP_EOL;
            $htaccessContent = "";
        }

        $htaccessContent .= "AuthUserFile {$dir}/.htpasswd" . PHP_EOL;
        $htaccessContent .= "AuthType Basic" . PHP_EOL;
        $htaccessContent .= "AuthName \"{$name}\"" . PHP_EOL;
        $htaccessContent .= "Require valid-user";

        file_put_contents("{$dir}/.htaccess", $htaccessContent);
    }


    echo "  - Generating user & password in .htpasswd" . PHP_EOL;

    if (is_file("{$dir}/.htpasswd"))
    {
        $htpasswd = file_get_contents("{$dir}/.htpasswd") . PHP_EOL;
    }
    else
    {
        $htpasswd = "";
    }

    $htpasswd .= $user . ':{SHA}' . base64_encode(hash("sha1", $password, true));
    file_put_contents("{$dir}/.htpasswd", $htpasswd);

    if ($passwordGenerated)
    {
        echo PHP_EOL;
        echo "  Generated Password: {$password}";
    }
}



/**
 * Generates a random password
 */
function generatePassword ($length = 8)
{
    $string = implode("", range("a", "z")) . implode("", range(0, 9));
    $max = strlen($string) - 1;
    $pwd = "";

    while ($length--)
    {
        $pwd .= $string[ mt_rand(0, $max) ];
    }


    return $pwd;
}



/**
 * Displays the usage
 */
function usage ($error = null)
{
    if (!is_null($error))
    {
        echo "  ERROR: {$error}" . PHP_EOL . PHP_EOL;
    }

    echo "  ./htpasswd_gen name user [password]" . PHP_EOL . PHP_EOL;
    echo "  Parameters:" . PHP_EOL;
    echo "      name        The name of the restricted area" . PHP_EOL;
    echo "      user        The user name" . PHP_EOL;
    echo "      password    The password - if omitted, a password will be generated" . PHP_EOL;
    echo PHP_EOL . PHP_EOL;

    echo "  Generates the .htpasswd & .htaccess file in the current directory and adds the users." . PHP_EOL;
    echo "  If the .htpasswd file already exists, the new user is just appended to the file." . PHP_EOL;
    echo "  If the .htaccess file already exists, the Auth block is just appended to the .htaccess file";
}