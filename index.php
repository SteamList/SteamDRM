<?php
/*
Copyright 2012 Josh "Cheeseness" Bush

This file is part of Para CMS.

Para CMS is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Para CMS is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Para CMS.  If not, see <http://www.gnu.org/licenses/>.
*/


include_once("config.php");
include_once("functions.php");


$currentPage = $defaultPage;
if (isset($_GET['page']))
{
	//TODO: Some sanitising please
	$currentPage = $_GET['page'];
}

$pageTitle = getPageTitle($currentPage);

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset=utf-8>
	<meta name="viewport" content="width=device-width">

	<title><?php echo $pageTitle; ?> &raquo; <?php echo $siteTitle; ?></title>

	<link rel="shortcut icon" href="images/fav.png" type="image/x-icon">
	<link rel="stylesheet" href="styles/default.css" type="text/css">
</head>
<body>
<div id = 'wrapper'>
<?php
echo "<hgroup>\n";
echo "\t<h1><a href = 'index.php'>" . $siteTitle . "</a></h1>\n";
echo "</hgroup>\n";

echo getNavMenu($externalLinks);
?>

<div id = 'content'>
<?php
//TODO: Pagination?
$articleList = getArticleList($currentPage);
foreach ($articleList as $article)
{
	echo getArticleContent($currentPage, $article);
}

?>
</div>
<?php
if (isset($twitterAccount))
{
?>
<div id = 'twitterWidget'>
<script charset="utf-8" src="http://widgets.twimg.com/j/2/widget.js"></script>
<script>
new TWTR.Widget({
  version: 2,
  type: 'profile',
  rpp: 6,
  interval: 30000,
  width: 250,
  height: 500,
  theme: {
    shell: {
      background: '#a0a078',
      color: '#efefef'
    },
    tweets: {
      background: '#a0a078',
      color: '#505020',
      links: '#efefef'
    }
  },
  features: {
    scrollbar: false,
    loop: false,
    live: true,
    behavior: 'all'
  }
}).render().setUser('<?php echo $twitterAccount;?>').start();
</script>
</div>
<?php
}
?>
<footer>
<?php
echo $copyrightText;
?>
</footer>
</div>
</body>
</html>
