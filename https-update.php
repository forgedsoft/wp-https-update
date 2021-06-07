<?php
// ForgedSoft(tm) Development 2021
// WORDPRESS UPDATE LINKS TO HTTPS
// THIS CODE IS FREE OF USE (NON COMMERCIAL). ForgedSoft(tm). 
//
//
//$$$$$$$$\                                            $$\  $$$$$$\             $$$$$$\    $$\     
//$$  _____|                                           $$ |$$  __$$\           $$  __$$\   $$ |    
//$$ |    $$$$$$\   $$$$$$\   $$$$$$\   $$$$$$\   $$$$$$$ |$$ /  \__| $$$$$$\  $$ /  \__|$$$$$$\   
//$$$$$\ $$  __$$\ $$  __$$\ $$  __$$\ $$  __$$\ $$  __$$ |\$$$$$$\  $$  __$$\ $$$$\     \_$$  _|  
//$$  __|$$ /  $$ |$$ |  \__|$$ /  $$ |$$$$$$$$ |$$ /  $$ | \____$$\ $$ /  $$ |$$  _|      $$ |    
//$$ |   $$ |  $$ |$$ |      $$ |  $$ |$$   ____|$$ |  $$ |$$\   $$ |$$ |  $$ |$$ |        $$ |$$\ 
//$$ |   \$$$$$$  |$$ |      \$$$$$$$ |\$$$$$$$\ \$$$$$$$ |\$$$$$$  |\$$$$$$  |$$ |        \$$$$  |
//\__|    \______/ \__|       \____$$ | \_______| \_______| \______/  \______/ \__|         \____/ 
//                           $$\   $$ |                                                            
//                           \$$$$$$  |                                                            
//         
//
// This code is provided 'as is' without any guarantee. Use it in your own risk. Remember to backup Before running this script.
// How it works:
// This php script loads database connection data from wp-config.php file on the website root.
// After database connection, the script will run a wutery to get 'siteurl' from wp_options table. 
// 4 links are created that may have been set on posts url's. With www, without www, with www and https, witout www and https.
// All these cases are updated to current siteurl with https.
// This script will also correct urls and permalinks that have or not www.
// https://www.forgedsoft.com/


// DB connection (WP Config file)
include_once('wp-config.php');

// Change character set to utf8
mysqli_set_charset($conn,"utf8");

// Attempt to connect to MySQL database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


// Ignore closed window
ignore_user_abort(true);

// Unset exec time limit
set_time_limit(0);

$siteurl = '';


//get site url
$ssqlc= "SELECT option_value FROM wp_options WHERE option_name = 'siteurl'";
$sresult = $conn->query($ssqlc) or die($conn->error .'<br>'.$ssqlc);
if (mysqli_num_rows($sresult)!=0) {
    while($mrow = $sresult->fetch_assoc()) {
        //remove https (in case already updated)
    	$siteurl =  str_replace('https://','http://',$mrow["option_value"]);
    }
}


if ($siteurl !=''){
    msg('Updating '.$siteurl);
}
else {
    msg('Site Url not found. Aborting. ');
    return;
}

// https url
$siteurl_s = str_replace('http://','https://',$siteurl);

//http and www
$siteurl_w ='';
$siteurl_ww ='';
if(substr($siteurl, 0, 11) === "http://www."){
    $siteurl_w = $siteurl;
    $siteurl_ww = str_replace('http://www.','http://',$siteurl);
} else {
    $siteurl_w = str_replace('http://','http://www.',$siteurl);
    $siteurl_ww = $siteurl;
}

//https and www
$siteurl_sw ='';
$siteurl_sww ='';
if(substr($siteurl, 0, 11) === "http://www."){
    $siteurl_w = $siteurl;
    $siteurl_ww = str_replace('http://www.','https://',$siteurl);
} else {
    $siteurl_w = str_replace('http://','https://www.',$siteurl);
    $siteurl_ww = $siteurl;
}

// Update siteurl
msg('Updating siteurl: '.$siteurl_s );
$ssqlc = "UPDATE wp_options SET option_value = REPLACE(option_value, 'http://', 'https://') WHERE option_name = 'siteurl'";
$sresult = $conn->query($ssqlc) or die($conn->error .'<br>'.$ssqlc);

// Update home url
msg('Updating home url: '.$siteurl_s );
$ssqlc = "UPDATE wp_options SET option_value = REPLACE(option_value, 'http://', 'https://') WHERE option_name = 'home'";
$sresult = $conn->query($ssqlc) or die($conn->error .'<br>'.$ssqlc);


// Update posts
updateposts($siteurl);

//Update post urls (Permalinks)
updateposturls($siteurl);


msg('Update Funnished.');



function updateposts($siteurl){
    
    $conn = $GLOBALS['conn'];
    $siteurl_s = $GLOBALS['siteurl_s'];
    $siteurl_w = $GLOBALS['siteurl_w'];
    $siteurl_ww = $GLOBALS['siteurl_ww'];
    $siteurl_sw = $GLOBALS['siteurl_sw'];
    $siteurl_sww = $GLOBALS['siteurl_sww'];
    
    //get total http urls
    $ssqlc= "SELECT COUNT(*) AS totalrows FROM `wp_posts` WHERE `guid` LIKE '%".$siteurl_w."%' OR `guid` LIKE '%".$siteurl_ww."%' ";
    
    $sresult = $conn->query($ssqlc) or die($conn->error .'<br>'.$ssqlc);
    if (mysqli_num_rows($sresult)!=0) {
        while($mrow = $sresult->fetch_assoc()) {
        	$totalrows =  $mrow["totalrows"];
        }
    }
    
    msg('Updating '.$totalrows.' Permalinks');
    
    // Update http www urls to https (if any)
    // In some cases, urls starting with www, but site url not. This will convert http://www.yoursite.com url's to http://yoursite.com in case that there is no www on siteurl.
    if ($siteurl_w !=''){
        $ssqlc = "UPDATE wp_posts SET guid = REPLACE(guid, '".$siteurl_w."', '".$siteurl_s."')";
        $sresult = $conn->query($ssqlc) or die($conn->error .'<br>'.$ssqlc);
    }

    // Update http non www urls to https www (if any)
    // In some cases, urls not starting with www, but site url does. This will convert http://yoursite.com url's to http://www.yoursite.com in case that there is www on siteurl.
    if ($siteurl_ww !=''){
        $ssqlc = "UPDATE wp_posts SET guid = REPLACE(guid, '".$siteurl_ww."', '".$siteurl_s."')";
        $sresult = $conn->query($ssqlc) or die($conn->error .'<br>'.$ssqlc);
    }

    // Update https www urls to current https siterurl (if any)
    // In some cases, urls starting with www, but site url not. This will convert http://www.yoursite.com url's to http://yoursite.com in case that there is no www on siteurl.
    if ($siteurl_w !=''){
        $ssqlc = "UPDATE wp_posts SET guid = REPLACE(guid, '".$siteurl_w."', '".$siteurl_s."')";
        $sresult = $conn->query($ssqlc) or die($conn->error .'<br>'.$ssqlc);
    }

    // Update https non www urls to https www (if any)
    // In some cases, urls not starting with www, but site url does. This will convert http://yoursite.com url's to http://www.yoursite.com in case that there is www on siteurl.
    if ($siteurl_ww !=''){
        $ssqlc = "UPDATE wp_posts SET guid = REPLACE(guid, '".$siteurl_ww."', '".$siteurl_s."')";
        $sresult = $conn->query($ssqlc) or die($conn->error .'<br>'.$ssqlc);
    }
}



function updateposturls($siteurl){
    
    $conn = $GLOBALS['conn'];
    $siteurl_s = $GLOBALS['siteurl_s'];
    $siteurl_w = $GLOBALS['siteurl_w'];
    $siteurl_ww = $GLOBALS['siteurl_ww'];
    $siteurl_sw = $GLOBALS['siteurl_sw'];
    $siteurl_sww = $GLOBALS['siteurl_sww'];
    
    //get total http posts
    $ssqlc= "SELECT COUNT(*) AS totalrows FROM `wp_posts` WHERE `post_content` LIKE '%".$siteurl_w."%' OR `post_content` LIKE '%".$siteurl_ww."%' ";
    
    $sresult = $conn->query($ssqlc) or die($conn->error .'<br>'.$ssqlc);
    if (mysqli_num_rows($sresult)!=0) {
        while($mrow = $sresult->fetch_assoc()) {
        	$totalrows =  $mrow["totalrows"];
        }
    }
    
    msg('Updating '.$totalrows.' Post content urls');
    
    // Update http www urls to https (if any)
    // In some cases, permalinks starting with www, but site url not. This will convert http://www.yoursite.com url's to http://yoursite.com in case that there is no www on siteurl.
    if ($siteurl_w !=''){
        $ssqlc = "UPDATE wp_posts SET post_content = REPLACE(post_content, '".$siteurl_w."', '".$siteurl_s."')";
        $sresult = $conn->query($ssqlc) or die($conn->error .'<br>'.$ssqlc);
    }

    // Update http non www urls to https www (if any)
    // In some cases, permalinks not starting with www, but site url does. This will convert http://yoursite.com url's to http://www.yoursite.com in case that there is www on siteurl.
    if ($siteurl_ww !=''){
        $ssqlc = "UPDATE wp_posts SET post_content = REPLACE(post_content, '".$siteurl_ww."', '".$siteurl_s."')";
        $sresult = $conn->query($ssqlc) or die($conn->error .'<br>'.$ssqlc);
    }

    // Update https www urls to current https siterurl (if any)
    // In some cases, permalinks starting with www, but site url not. This will convert http://www.yoursite.com url's to http://yoursite.com in case that there is no www on siteurl.
    if ($siteurl_w !=''){
        $ssqlc = "UPDATE wp_posts SET post_content = REPLACE(post_content, '".$siteurl_sw."', '".$siteurl_s."')";
        $sresult = $conn->query($ssqlc) or die($conn->error .'<br>'.$ssqlc);
    }

    // Update https non www urls to https www (if any)
    // In some cases, permalinks not starting with www, but site url does. This will convert http://yoursite.com url's to http://www.yoursite.com in case that there is www on siteurl.
    if ($siteurl_ww !=''){
        $ssqlc = "UPDATE wp_posts SET post_content = REPLACE(post_content, '".$siteurl_sww."', '".$siteurl_s."')";
        $sresult = $conn->query($ssqlc) or die($conn->error .'<br>'.$ssqlc);
    }  
}


function msg($message){
    
    echo '<br>'.$message.'<br>';
    
}

?>
