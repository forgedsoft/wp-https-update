# wp-https-update
Wordpress Database Urls update to https. With ability to fix www and non www urls and permalinks.

How to run:
1.copy https-update.php on your wordpress root folder (site home), via ftp.
2.Make a databse backup (Just in case).
3.run script from your browser (yoursite.com/https-update.php)


// This code is provided 'as is' without any guaratee. Use it in your own risk. Remember to backup Before running this script.
// How it works:
// This php script loads database connection data from wp-config.php file on the website root.
// After database connection, the script will run a wutery to get 'siteurl' from wp_options table. 
// 4 links are created that may have been set on posts url's. With www, without www, with www and https, witout www and https.
// All these cases are updated to current siteurl with https.
// This script will also correct urls and permalinks that have or not www.
