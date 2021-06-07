# wp-https-update
Wordpress Database Urls update to https. With ability to fix www and non www urls and permalinks.
<br/><br/>
How to run:<br/>
1.copy https-update.php on your wordpress root folder (site home), via ftp.<br/>
2.Make a databse backup (Just in case).<br/>
3.run script from your browser (yoursite.com/https-update.php)<br/>
<br/>
<br/>
// This code is provided 'as is' without any guarantee. Use it in your own risk. Remember to backup Before running this script.<br/>
// How it works:<br/>
// This php script loads database connection data from wp-config.php file on the website root.<br/>
// After database connection, the script will run a wutery to get 'siteurl' from wp_options table. <br/>
// 4 links are created that may have been set on posts url's. With www, without www, with www and https, witout www and https.<br/>
// All these cases are updated to current siteurl with https.<br/>
// This script will also correct urls and permalinks that have or not www.<br/>
