<?php

//This is the title that will appear on your site
$siteTitle = "The Big List of 3rd Party DRM on Steam";

//This should be the name of the folder you'd like your site to default to
$defaultPage = "FAQ";

//If you want your email address to be visible on the site, uncomment this and put your email address in
$contactAddress = "flibitijibibo@flibitijibibo.com";

//Uncomment to set the time zone of your choosing, supported ones can be found here: http://php.net/manual/en/timezones.php
//$timeZone = "UTC";

//Don't edit the next line :3
if( isset($timeZone) ) date_default_timezone_set($timeZone);

//This is the language/locale your site will use. Take a look at the localisation directory to find out which ones are supported.
//Format is "<language code>_<region code>"
//e.g. en_US, en_GB
//You can also use just the language code, like in the default option below. This'll ignore region-specific files.
$locale = "en";

//Don't edit the next line :3
setlocale(LC_ALL, $locale);

//This is the location that para will look in for your folders and .txt files
$contentPath = "content/";

//This is what will immediately follow links in lists before any additional text
$linkSeparator = "; ";

//This adds download links for posts. Note that they're in the source format from which Para parses them
$showSource = true;

//This adds normal links for posts.
$showPostLink = true;

//This adds the modification timestamp in the post footer.
$showTimestamp = true;

//This'll strip the text up to the first space of your page title. Useful for arranging the menu bar as e.g. 00_Home, 10_About_Us, etc.
$trimPageTitle = false;

//This'll make the parent pages that have subpages be accessible through the nav menu.
$showParentInMenu = false;

//If you want your twitter feed to be visible on the site, uncomment this and put your twitter username in
$twitterAccount = "steamdrm";

//These variables set the colours used within the twitter feed widget and are unused if the $twitterAccount variable is not set.
$twitterBackgroundColour = "#000000";
$twitterTextColour = "#b8b8b8";
$twitterLinkColour = "#787878";


//If you want to have links to external sites in your menu, add them here using the same format as the next line. You can comment out or delete this one if want.
$externalLinks["Source/Contribute"] = "https://github.com/SteamList/SteamDRM";
$externalLinks["Steam Forum Thread"] = "http://forums.steampowered.com/forums/showthread.php?t=1537801";
$externalLinks["Steam Group"] = "http://steamcommunity.com/groups/nodrm";

//If you want to have additional or different styles, you can add them here using the same format as the next line. You can comment out or delete this one if you want.
$customCSS[] = "styles/default.css";

//If you want to have additional or different javascripts, you can add them here using the same format as the next line. You can comment out or delete this one if you want.
$customJS[] = "scripts/default.js";

//This is the text that appears at the bottom of your site. Replace "yourname" with your name. Feel free to delete the "Powered by Para CMS" part if you don't want it.
$copyrightText = "&copy; " . date("Y") . " <a href = 'https://github.com/SteamList'>SteamList contributors</a>";

?>
