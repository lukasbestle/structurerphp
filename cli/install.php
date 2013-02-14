#!/usr/bin/php
?>
<?php

echo "\033[0;32mstructurer \033[0;34mv1.2\033[0m\n\n";

echo "Getting the CLI...\n";
$cli = file_get_contents("https://raw.github.com/vis7mac/structurerphp/master/cli/cli.php");

echo "Getting the library...\n";
$cli = str_replace("<?php\n\nrequire(\"../structurer.php\");", file_get_contents("https://raw.github.com/vis7mac/structurerphp/master/structurer.php"), $cli);

echo "\n\033[0;32mSuccessfully fetched all required files, ready to install to /usr/bin!\nPlease enter your password to complete installation.\033[0m\n";

$errors = "";
$errors = shell_exec("echo " . str_replace('\\"', '\\\\"', escapeshellarg($cli)) . " | sudo tee /usr/bin/structurer > /dev/null");
$errors .= shell_exec("sudo chmod +x /usr/bin/structurer");

if($errors == "") {
	echo "\n\n\033[0;32m\033[4mSuccessfully installed structurer to /usr/bin, please now restart your console.\033[0m\n";
} else {
	echo "\n\n\033[0;31m\033[4mError installing.\033[0m\n\n";
	exit(1);
}