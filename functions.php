<?php
/*
Copyright 2012 Para CMS contributors (see AUTHORS)

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

// a function for countering directory traversal
function deTraverse($path)
{
	// in case people are silly and use backslashes, or someone runs this
	// on a Windows box, we'll turn them into normal slashes
	$path = str_replace('\\', '/', $path);
	$arr = explode('/', $path);
	foreach ($arr as $key => $value) {
		if ($value === ".." || $value === "." || empty($value)) {
			unset($arr[$key]);
		}
		unset($key);
		unset($value);
	}
	$path = implode('/', $arr);
	return $path;
}

function genLocaleStrings($locale)
{
	$defaultStrings = array();
	$customStrings = array();
	$defaultLocaleStrings = array();
	$regionLocaleStrings = array();
	$customLocaleStrings = array();

	// global default strings
	include_once("localisation/default.php");
	$defaultStrings = $strings;
	// global custom strings
	include_once("localisation/custom.php");
	$customStrings = $strings;
	// if our locale isn't empty, we'll use its specific translations as well
	if (!empty($locale))
	{
		$locale=explode("_", $locale, 2);
		// the locale's default strings
		include_once("localisation/$locale[0]/default.php");
		$defaultLocaleStrings = $strings;
		if (isset($locale[1]) && empty($locale[1]) === FALSE)
		{
			// the locale's region-specific strings
			include_once("localisation/$locale[0]/$locale[1].php");
			$regionLocaleStrings = $strings;
		}
		// custom locale strings
		include_once("localisation/$locale[0]/custom.php");
		$customLocaleStrings = $strings;
	}

	// the earlier mentioned arrays will be overrided by those latter on the line
	return array_merge($defaultStrings, $customStrings, $defaultLocaleStrings, $regionLocaleStrings, $customLocaleStrings);
}

function getLocaleString($string)
{
	GLOBAL $localeStrings;
	return $localeStrings["$string"];
}

function getPageTitle($currentPage)
{
	GLOBAL $trimPageTitle;

	$returnString = str_replace("_", " ", $currentPage);
	$returnString = htmlspecialchars($returnString);
	if ($trimPageTitle)
	{
		$returnString = preg_replace("/^(\H+) /", "", $returnString);
		$returnString = preg_replace("#/(\H+) #", "/", $returnString);
	}

	return $returnString;
}

function getNavMenu($externalLinks = array(), $currentPage = "")
{
	$returnString = "";

	//Parse subfolders
	$menuArray = getSubfolders();

	//Append external links
	$menuArray = array_merge($menuArray, $externalLinks);

	//Build nav and ul markup
	$returnString = "<nav id = 'navPrimary'>\n";
	$returnString .= buildNavMenu($menuArray, $currentPage);
	$returnString .= "</nav>\n\n";

	return $returnString;
}

function buildNavMenu($menuArray, $currentPage = "")
{
	GLOBAL $trimPageTitle;
	GLOBAL $showParentInMenu;

	$returnString = "\n\t<ul>\n";
	foreach ($menuArray as $menuTitle => $menuContent)
	{
		$subMenu = "";
		$originalMenuTitle = $menuTitle;
		$menuTitle = str_replace("_", " ", $menuTitle);
		if ($trimPageTitle)
		{
			$menuTitle = preg_replace("/^(\H+) /", "", $menuTitle);
		}

		if (is_array($menuContent))
		{
			if ($showParentInMenu)
			{
				$menuURL = "index.php?page=" . $originalMenuTitle;
			}
			else
			{
				$menuURL = "#";
			}
			$subMenu = buildNavMenu($menuContent, $currentPage);
			$class = "parentMenu";
			if (substr($currentPage, 0, stripos($currentPage, "/")) == $menuTitle || substr($currentPage, 0) == $menuTitle)
			{
				$class .= " current";
			}
			$returnString .= "\t\t<li class = '" . $class. "' id = '" . $originalMenuTitle . "'><a href = '". $menuURL . "'>" . $menuTitle . "</a>";
			$returnString .= $subMenu;
			$returnString .= "</li>\n";

		}
		else
		{
			$menuURL = $menuContent;
			$menuTitleFull = str_replace("_", " ", $menuURL);
			$class = "";

			if ($currentPage == $menuTitleFull)
			{
				$class .= " current";
			}

			if ((stripos($menuURL, "http://") === false) && (stripos($menuURL, "https://") === false))
			{
				$fullMenuURL = "index.php?page=" . $menuURL;
			}
			else
			{
				$fullMenuURL = $menuURL;
				$class .= " external";
			}

			$returnString .= "\t\t<li class = '" . $class . "' id = '" . $menuURL . "'><a href = '". $fullMenuURL . "'>" . $menuTitle;
			if (stripos($class, "external") !== false)
			{
				$returnString .= " »";
			}
			$returnString .= "</a></li>\n";
		}
	}

	$returnString .= "\t</ul>\n";

	return $returnString;
}

function getContentsMenu($currentPage)
{
	//FIXME: I didn't really do any design work for this. It feels a lot like a hack.
	$articleList = getArticleList($currentPage);

	$tempList = array();
	foreach($articleList as $article)
	{
		$tempList[$article] = getArticleContent($currentPage, $article, true);
	}
	$contentsList = array();
	foreach($tempList as $article => $heading)
	{
		if (is_array($heading))
		{
			$contentsList = array_merge($contentsList, $heading);
		}
		else
		{
			$contentsList[$article] = $heading;
		}
	}

	$returnString = "";
	if (count($contentsList) > 1)
	{
		$returnString = "\t<nav id = 'navContents'>\n";
		$returnString .= "\t\t<h2>" . getLocaleString("contentmenu") . "</h2>\n";
		$returnString .= buildContentsMenu($contentsList);
		$returnString .= "\t</nav>\n";
	}

	return $returnString;
}

function buildContentsMenu($contentsList)
{
	$returnString = "\t\t<ol>\n";

	foreach($contentsList as $url => $title)
	{
		$returnString .= "<li><a href = '#" . $url . "'>" . $title . "</a></li>";
	}

	$returnString .= "\t\t</ol>\n";

	return $returnString;
}

function getSubfolders ($currentPath = "")
{
	GLOBAL $contentPath;
	$returnValue = array();

	//I don't see this happening, but just in case, let's bail if we can't find anything
	if (!file_exists($contentPath . $currentPath. "/"))
	{
		return $returnValue;
	}
	$folderContents = scandir($contentPath . $currentPath);
	foreach ($folderContents as $entry)
	{
		if ($entry != "." && $entry != "..")
		{
			if (is_dir($contentPath . $currentPath . $entry))
			{
				$returnValue[$entry] = $currentPath . $entry;
				$subFolders = getSubfolders($currentPath . $entry . "/");
				if (!empty($subFolders))
				{
					$returnValue[$entry] = $subFolders;
				}
			}
		}
	}
	return $returnValue;
}

function getArticleList($currentPage)
{
	GLOBAL $contentPath;
	GLOBAL $errorState;

	//TODO: We want reverse lexical ordering for news items (assuming we're using YYYY-MM-DD prefixing for filenames), but normal ordering for everything else
	$returnValue = array();

	//When somebody gets a
	if (!file_exists($contentPath . $currentPage . "/"))
	{
		$errorState = 1;
		return $returnValue;
	}
	$folderContents = scandir($contentPath . $currentPage . "/");
	foreach ($folderContents as $entry)
	{
		if (strlen($entry) >= 4)
		{
			if (strripos($entry, ".txt", strlen($entry) - 4) !== false)
			{
				$returnValue[] = $entry;
			}
		}
	}

	return $returnValue;
}

//TODO: This is a dodgey quick-fix for SteamDRM. Need to come up with a nicer solution for this (and also make it multilevel)
function getAllArticles($articlePath, $headingsOnly = false)
{

	$returnValue = "";
	$contentList = array();

	$parentPath = substr($articlePath, 0, strripos($articlePath, "/") + 1);
	$callingPath = substr($articlePath, strripos($articlePath, "/") + 1);

	$folders = getSubfolders($parentPath);
	foreach ($folders as $folder => $subfolder)
	{
		if ($parentPath . $folder == $articlePath)
		{
			//We're going to wind up in an infinite loop if we do this, so bail.
		}
		else
		{
			$articles = getArticleList($parentPath . $folder);
			foreach ($articles as $article)
			{
				if ($headingsOnly)
				{
					$contentList[$article] = getArticleContent($parentPath . $folder, $article, $headingsOnly);
				}
				else
				{
					$returnValue .= getArticleContent($parentPath . $folder, $article, $headingsOnly);
				}
			}
		}
	}

	if ($headingsOnly)
	{
		return $contentList;
	}
	else
	{
		return $returnValue;
	}
}


//TODO: This needs to be more generic.
function getArticleError($id, $title, $text, $detail)
{
	//TODO: Localisation
	//TODO: It'd be nice to have something generic based on $errorState (an array of messages for which $errorState was the index?) in addition to the passed arguments
	$returnString = "<article class = 'error' id = '" . $id . "'>\n";
	$returnString .= "\t<h1>" . $title . "</h1>\n";
	$returnString .= "\t<p class = 'errorDescription'>" . $text . "</p>\n";
	$returnString .= "\t<p class = 'errorDetail'>" . $detail . "</p>\n";
	$returnString .= "</article>\n";
	return $returnString;
}

function getArticleContent($articlePath, $articleSource, $headingsOnly = false)
{
	GLOBAL $contentPath;
	GLOBAL $linkSeparator;
	GLOBAL $showSource;
	GLOBAL $showPostLink;
	GLOBAL $showTimestamp;

	//If the article we're trying to show doesn't exist, let's tell someone about it
	if (!file_exists($contentPath . $articlePath . "/" . $articleSource))
	{
		if ($headingsOnly)
		{
			return "Error";
		}

		return getArticleError($articleSource, getLocaleString("articleerrortitle"), getLocaleString("articleerrortext"), getLocaleString("articleerrordetails") . $articlePath . "/" . $articleSource);
	}
	$text = file_get_contents($contentPath . $articlePath . "/" . $articleSource);


	if ($text === false)
	{
		//TODO: Localisation
		if ($headingsOnly)
		{
			return "Error";
		}
		return getArticleError($articleSource, getLocaleString("articleerrortitle"), getLocaleString("articleerrortext"), getLocaleString("articleerrordetails") . $articlePath . "/" . $articleSource);

	}

	if (stripos($text, "_EVERYTHING_") !== false)
	{
		return getAllArticles($articlePath, $headingsOnly);
	}

	$returnString = "<article id = '" . $articleSource . "'>\n";

	//Pull off the heading (it's always the first line)
	if (stripos($text, "\n") === false)
	{
		if ($headingsOnly)
		{
			return $text;
		}
		$returnString .= "\t<h1>" . $text . "</h1>\n";
		if ($showSource)
		{
			$returnString .= "\t<p class = 'downloadSourceLink'><a href = '" . $contentPath . $articlePath . "/" . $articleSource . "'>» " . getLocaleString("downloadsource") . "</a></p>\n";
		}
	}
	else
	{
		if($headingsOnly)
		{
			return substr($text, 0, stripos($text, "\n"));
		}

		$returnString .= "\t<h1>" . substr($text, 0, stripos($text, "\n")) . "</h1>\n";
		if ($showSource)
		{
			$returnString .= "\t<p class = 'downloadSourceLink'><a href = '" . $contentPath . $articlePath . "/" . $articleSource . "'>» " . getLocaleString("downloadsource") . "</a></p>\n";
		}
		if ($showPostLink)
		{
			$returnString .= "\t<p class = 'downloadSourceLink'><a href = '#" . $articleSource . "'>» " . getLocaleString("linktothis") . "</a></p>\n";
		}

		$text = substr($text, stripos($text, "\n") + 1);

		//break it up by \n\n in case there are multiple text/ul pairs
		$text = explode("\n\n", $text);


		//for each text/ul pair, explode on -----, and explode the first segment on \n (encapsulating in <p>), and explode the second segment on \n parsing as a ul
		foreach ($text as $segment)
		{
			if (stripos($segment, "-----") !== false)
			{
				$elements = explode("-----", $segment);

				//Captions
				$caption = explode("\n", $elements[0]);
				foreach ($caption as $captionLine)
				{
					if ($captionLine != "")
					{
						$returnString .= "\t<p>" . $captionLine . "</p>\n";
					}
				}

				//List
				$list = explode("\n", $elements[1]);
				unset($list[0]);

				$returnString .= "\t<ul>\n";
				foreach ($list as $listItem)
				{
					if ($listItem != "")
					{
						$returnString .= "\t\t<li>";

						if (stripos($listItem, "|") !== false)
						{
							$listItem = explode("|", $listItem);
							if ($listItem[0] != "")
							{
								$returnString .= "<a href = '" . $listItem[1] . "'>" . $listItem[0] . "</a>";
								if (isset($listItem[2]) && $listItem[2] != "")
								{
									$returnString .=  $linkSeparator . $listItem[2];
								}
							}
						}
						else
						{
							$returnString .= $listItem;
						}
						$returnString .= "</li>\n";
					}
				}
				$returnString .= "\t</ul>\n";

			}
			else
			{
				//Captions
				$caption = explode("\n", $segment);
				foreach ($caption as $captionLine)
				{
					if ($captionLine != "")
					{
						$returnString .= "\t<p>" . $captionLine . "</p>\n";
					}
				}
			}
		}
	}

	if ($showTimestamp)
	{
		//Note: This assumes that the server has appropriate and correct timezone information
		$returnString .= "<p class = 'modifiedDate'>" . getLocaleString("lastupdated") . ": " . strftime(getLocaleString("dateformat"), filemtime($contentPath . $articlePath . "/" . $articleSource)) . "</p>";
	}
	$returnString .= "</article>\n\n";
	return $returnString;
}
?>
