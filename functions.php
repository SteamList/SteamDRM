<?php

function getPageTitle($currentPage)
{
	$returnString = str_replace("_", " ", $currentPage);
	
	return $returnString;
}

function getNavMenu($externalLinks = array())
{
	$returnString = "";
	
	//Parse subfolders
	$menuArray = getSubfolders();

	//Append external links
	$menuArray = array_merge($menuArray, $externalLinks);
	
	//Build nav and ul markup
	$returnString = "<nav>\n";
	$returnString .= buildNavMenu($menuArray);
	$returnString .= "</nav>\n\n";
	
	return $returnString;
}

function buildNavMenu($menuArray)
{
	$returnString .= "\n\t<ul>\n";
	foreach ($menuArray as $menuTitle => $menuContent)
	{
		$subMenu = "";
		$menuTitle = str_replace("_", " ", $menuTitle);

		if (is_array($menuContent))
		{
			$menuURL = "#";
			$subMenu = buildNavMenu($menuContent);
			$returnString .= "\t\t<li class = 'parentMenu'><a href = '". $menuURL . "'>" . $menuTitle . "</a>";
			$returnString .= $subMenu;
			$returnString .= "</li>\n";

		}
		else
		{
			$menuURL = $menuContent;
			
			if ((stripos($menuURL, "http://") === false) && (stripos($menuURL, "https://") === false))
			{
				$menuURL = "index.php?page=" . $menuURL;
			}
			$returnString .= "\t\t<li><a href = '". $menuURL . "'>" . $menuTitle . "</a></li>\n";
		}
		
		
	}
	
	$returnString .= "\t</ul>\n";
	
	return $returnString;
}

function getSubfolders ($currentPath = "")
{
	GLOBAL $contentPath;
	$returnValue = array();
	
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

	//TODO: We want reverse lexical ordering for news items (assuming we're using YYYY-MM-DD prefixing for filenames), but normal ordering for everything else
	$returnValue = array();
	$folderContents = scandir($contentPath . $currentPage . "/");
	foreach ($folderContents as $entry)
	{
		if (strripos($entry, ".txt") !== false)
		{
			$returnValue[] = $entry;
		}
	}
	
	return $returnValue;
}

//TODO: This is a dodgey quick-fix for SteamDRM. Need to come up with a nicer solution for this (and also make it multilevel)
function getAllArticles($articlePath)
{

	$returnValue = "";
	
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
				$returnValue .= getArticleContent($parentPath . $folder, $article);
			}
		}
	}
	return $returnValue;
}

function getArticleContent($articlePath, $articleSource)
{
	GLOBAL $contentPath;
	
	$text = file_get_contents($contentPath . $articlePath . "/" . $articleSource);
	
	if (stripos($text, "_EVERYTHING_") !== false)
	{
		return getAllArticles($articlePath);
	}
	
	$returnString = "<article id = " . $articleSource . ">\n";
	
	//Pull off the heading (it's always the first line)
	if (stripos($text, "\n") === false)
	{
		$returnString .= "\t<h1>" . $text . "</h1>\n";
		$returnString .= "\t<p class = 'downloadSourceLink'><a href = '" . $contentPath . $articlePath . "/" . $articleSource . "'>&raquo; Download Source</a></p>\n";
	}
	else
	{
		
		$returnString .= "\t<h1>" . substr($text, 0, stripos($text, "\n")) . "</h1>\n";
		$returnString .= "\t<p class = 'downloadSourceLink'><a href = '" . $contentPath . $articlePath . "/" . $articleSource . "'>&raquo; Download Source</a></p>\n";
		$returnString .= "\t<p class = 'downloadSourceLink'><a href = '#" . $articleSource . "'>&raquo; Link To This</a></p>\n";
		
		$text = substr($text, stripos($text, "\n") + 1);
		
		//break it up by \n\n in case there are multiple text/ul pairs
		$text = explode("\n\n", $text);
		
		
		//for each text/ul pair, explode on ------, and explode the first segment on \n (encapsulating in <p>), and explode the second segment on \n parsing as a ul
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
								if ($listItem[2] != "")
								{
									$returnString .= "; " . $listItem[2];
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
			
		}
	}
	
	//Note: This assumes that the server has appropriate and correct timezone information
//	$returnString .= "<p class = 'modifiedDate'>Last updated on " . gmdate("D, d M Y H:i:s", filemtime($contentPath . $articlePath . "/" . $articleSource)) . " GMT</p>";
	$returnString .= "<p class = 'modifiedDate'>Last updated: " . gmdate("d M Y", filemtime($contentPath . $articlePath . "/" . $articleSource)) . "</p>";
	$returnString .= "</article>\n\n";
	return $returnString;
}
?>
