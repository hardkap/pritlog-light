<?php

/*#######################################################################
# 	PRITLOG		                                                #
#	                                                                #
#       The idea of this blog, is directly taken from a	                #
#	very simple yet powerful blog software called PPLOG             #
#       (Perl Powered Blog). PPLOG is a creation of			#
#	Federico Ram�rez (fedekun) - fedekiller@gmail.com               #
#   	                                                                #
#       I just wanted to experiment with creating		        #
#	a similar blog in PHP. Hence PRITLOG.		                #
#	prithish@hardkap.com				                #
#							                #
#	PRITLOG uses the GNU Public Licence v3		                #
#	http://www.opensource.org/licenses/gpl-3.0.html	                #
#							                #
#	Powered by YAGNI (You Ain't Gonna Need It)	                #
#	YAGNI: Only add things, when you actually 	                #
#	need them, not because you think you will.	                #
#							                #
#	Version: 0.411                                                  #
#######################################################################*/


  require("config.php");

  $entries=getPosts();
  $lastEntry=explode($separator,$entries[0]);
  $newPostNumber    =$lastEntry[3]+1;
  $newFullPostNumber=str_pad($newPostNumber, 5, "0", STR_PAD_LEFT);
  $newPostFile      =$newFullPostNumber.$config_dbFilesExtension;
  $option=isset($_GET['option'])?$_GET['option']:"mainPage";

if($option == 'RSS')
{
	$base = 'http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['REQUEST_URI'],0,-3);

	echo header('Content-type: text/xml').'<?xml version="1.0" encoding="ISO-8859-1"?><rss version="2.0">';
	echo '<channel><title>'.$config_blogTitle.'</title><description>'.$config_metaDescription.'</description>';
	echo '<link>http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['REQUEST_URI'],0,strlen($_SERVER['REQUEST_URI'])-7).'</link>';

	if($config_entriesOnRSS == 0)
	{
		$limit = count($entries);
	}
	else
	{
		$limit = $config_entriesOnRSS;
	}

	for($i = 0; $i < $limit; $i++)
	{
		$entry      = explode($separator, $entries[$i]);
		$rssTitle   =$entry[0];
                $rssContent =$entry[1];
                $rssEntry   =$entry[3];
                $rssCategory=$entry[4];
		echo '<item><link>'.$base.htmlentities('viewEntry&filename=').$rssEntry.'</link>';
		echo '<title>'.$rssTitle.'</title><category>'.$rssCategory.'</category>';
		echo '<description>'.htmlentities($rssContent).'</description></item>';
	}
	
	echo '</channel></rss>';
}
else
{

?>
<html>
<head>
<title><?php echo $config_blogTitle; ?> - Powered by PRITLOG</title>
<meta name="Keywords" content="<?php echo $config_metaKeywords; ?>"/>
<meta name="Description" content="<?php echo $config_metaDescription; ?>"/>
<link href=style.css rel=stylesheet type=text/css>
</head>
<body>
<div id=all>
<div id="myhead">
<center><br><table><tr><td><img src="dog.gif"/></td><td style="padding-bottom:0px;padding-top:8px;"><h1><?php echo $config_blogTitle; ?></h1></td></tr></table></center>
</div>
<script src="http://js.nicedit.com/nicEdit-latest.js" type="text/javascript"></script>
<div id="menu">
<h1>Main menu</h1>
<a href=<?php $_SERVER['PHP_SELF']; ?>?option=mainPage>Home</a>
<a href=<?php $_SERVER['PHP_SELF']; ?>?option=newEntry>New Entry</a>
<a href=<?php $_SERVER['PHP_SELF']; ?>?option=viewArchive>Archive</a>
<a href=<?php $_SERVER['PHP_SELF']; ?>?option=RSS>RSS Feeds</a>
<h1>Categories</h1>
<?php sidebarCategories(); ?>
<h1>Search</h1>
<form name="form1" method="post" action=<?php echo $_SERVER['PHP_SELF']; ?>?option=searchPosts>
<input type="text" name="searchkey">
<input type="hidden" name="do" value="search">
<input type="submit" onclick="javascript:this.disabled=true" name="Submit" value="Search"><br />
</form>
<h1>Latest Entries</h1>
<?php sidebarListEntries(); ?>
<h1>Pages</h1>
<?php sidebarPageEntries() ?>
<h1>Share</h1>
	<a target="_blank" href="http://reddit.com/submit?url=<?php echo "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; ?>">
	Reddit This <img border="0" src="reddit.gif" /></a>
<h1>Links</h1>
<?php sidebarLinks(); ?>
<h1>Latest Comments</h1>
<?php
      sidebarListComments();
      echo '<a href="'.$_SERVER['PHP_SELF'].'?option=listAllComments">List All Comments</a>';
?>
<h1>Stats</h1>
<?php sidebarStats() ?>
<h1>Pritlog Version</h1>
<script type="text/javascript">
var clientVersion=0.41;
</script>
<script src="http://hardkap.net/pritlog/checkversion.js" type="text/javascript"></script>

</div>
<div id=content>
<?php
  //echo getcwd()."<br/>";
}
  function getPosts() {
      global $postdir;
      if (file_exists($postdir)) {
          if ($handle = opendir($postdir)) {
              $file_array_unsorted = array();
              $file_array_sorted   = array();
              while (false !== ($file = readdir($handle))) {
                  array_push($file_array_unsorted,$file);
              }
              rsort($file_array_unsorted);
              foreach ($file_array_unsorted as $value) {
                  $filename=$postdir.$value;
                  if ((file_exists($filename)) && ($filename !== $postdir.".") && ($filename !== $postdir."..")) {
                    $fp = fopen($filename, "rb");
                    array_push($file_array_sorted,fread($fp, filesize($filename)));
                    fclose($fp);
                  }
              }
              closedir($handle);
          }
      }
      return $file_array_sorted;
  }

  function sidebarStats() {
      global $commentdir, $config_usersOnlineTimeout,$config_statsDontLog, $separator, $config_dbFilesExtension;
      $ip=$_SERVER['REMOTE_ADDR'];
      $currentTime=time();
      $statsContent=$ip.$separator.$currentTime."\n";
      $statsFile=$commentdir."online$config_dbFilesExtension.dat";
      $logThis=0;
      foreach ($config_statsDontLog as $value) {
          //echo "ip = $ip,value=$value,<br>";
          if ($ip == $value ) {
              $logThis=1;
          }
      }
      if ($logThis != 1) {
          //echo "ip=$ip,logThis=$logThis,<br>";
          if (file_exists($commentdir)) {
              $fp=fopen($statsFile,"a");
              fwrite($fp,$statsContent);
              fclose($fp);
          }
      }
      if (file_exists($statsFile)) {$statsRead=file($statsFile);}
      $hits=0;
      $online=0;
      $already=array();
      if (is_array($statsRead)) {
          foreach ($statsRead as $value) {
              $log=explode($separator,$value);
              $logIP=$log[0];
              $logTime=$log[1];
              $timeOnline=$currentTime-$logTime;
              //echo "logIP = $logIP, logTime = $logTime,<br>";
              if ($timeOnline < $config_usersOnlineTimeout) {
                   if (array_search($logIP,$already)===FALSE) {$online++;}
                   array_push($already,$logIP);
              }
              $hits++;
          }
      }
      echo "Users Online: $online<br>";
      echo "Hits: $hits<br>";
  }

  function listPosts() {
      global $separator, $postdir, $entries, $config_entriesPerPage, $requestCategory, $config_maxPagesDisplayed;
      global $commentdir,$config_dbFilesExtension, $userFileName;
      $filterEntries=array();
      $totalEntries=0;
      $totalPosts=0;
      if (is_array($entries)) {
          foreach ($entries as $value)
          {
               $entry   =explode($separator,$value);
               $title   =$entry[0];
               $content =$entry[1];
               $date1   =$entry[2];
               $fileName=$entry[3];
               $category=$entry[4];
               $postType=$entry[5];
               if ($requestCategory == "") {
                   if ($postType!="page") {
                        array_push($filterEntries,$value);
                        $totalEntries++;
                        $totalPosts++;
                   }
               }
               else {
                   if ($category == $requestCategory) {
                        array_push($filterEntries,$value);
                        $totalEntries++;
                   }
               }
          }
      }

      if ($totalEntries == 0) {
          $justCommentsDir=str_replace("/","",(substr($commentdir,strlen(getcwd()),strlen($commentdir))));
          $justPostsDir=str_replace("/","",(substr($postdir,strlen(getcwd()),strlen($postdir))));
          $errorMessage="<br><span style=\"color: rgb(204, 0, 51);\">Unable to create posts and comments directories<br>Please create them manually. <br>";
          $errorMessage=$errorMessage."Here are the directory names: <br>- ".$justPostsDir."<br>- ".$justCommentsDir."<br>";
          $errorMessage=$errorMessage."<br>Make sure the permissions on these directories and the main blog directory are set to 755.<br></span>";
          

          if (is_writable(getcwd())) {
              if (!file_exists($commentdir) || !file_exists($postdir)) {  /* Looks like first time running */
                  mkdir($commentdir,0755) or die($errorMessage);
                  mkdir($postdir,0755) or die($errorMessage);
              }

              echo '<br><br>No posts yet. Why dont you <a href="'.$_SERVER['PHP_SELF'].'?option=newEntry">make one</a>?<br>';
          }
          else {
              exit($errorMessage);
          }    
      }

      # Pagination - This is the so called Pagination
      $page = isset($_POST['page'])?$_POST['page']:$_GET['page'];
      if($page == ''){ $page = 1; }

      # What part of the array should i show in the page?
      $arrayEnd = ($config_entriesPerPage*$page);
      $arrayStart = $arrayEnd-($config_entriesPerPage-1);
      # As arrays start from 0, i will lower 1 to these values
      $arrayEnd--;
      $arrayStart--;
      $totalEntries--;

      $i = $arrayStart;
      if ($arrayEnd > $totalEntries) {$arrayEnd = $totalEntries;}

      while($i<=$arrayEnd)
      {
           $entry  =explode($separator,$filterEntries[$i]);
           $title  =$entry[0];
           $content=$entry[1];
           $date1  =$entry[2];
           $fileName=$entry[3];
           $category=$entry[4];
           $postType=$entry[5];
           echo "<h1><a class=\"postTitle\" href=".$_SERVER['PHP_SELF']."?option=viewEntry&filename=".$fileName.">".$title."</a></h1>";
           //echo "<h1>".$title."</h1>";
           echo $content;
           /* 0.45 Changing '.' to ' ' */
           $categoryText=str_replace("."," ",$category);
           echo "<center><br/><i>Posted on ".$date1."&nbsp;-&nbsp; Category: <a href=".$_SERVER['PHP_SELF']."?option=viewCategory&category=".$category.">".$categoryText."</a></i><br/>";
           $commentFile=$commentdir.$fileName.$config_dbFilesExtension;
           if (file_exists($commentFile)) {
               $commentLines=file($commentFile);
               $commentText=count($commentLines)." Comments";
           }
           else {$commentText="No Comments";}
           /* 0.45 #Comments*/
           echo "<a href=".$_SERVER['PHP_SELF']."?option=viewEntry&filename=".$fileName."#Comments>".$commentText."</a>";
           echo "&nbsp;-&nbsp;<a href=".$_SERVER['PHP_SELF']."?option=editEntry&filename=".$fileName.">Edit</a>";
           echo "&nbsp;-&nbsp;<a href=".$_SERVER['PHP_SELF']."?option=deleteEntry&filename=".$fileName.">Delete</a></center><br/><br/><br/>";
           $i++;
      }
      $totalEntries++;
      $totalPages = ceil(($totalEntries)/($config_entriesPerPage));
      if($totalPages >= 1)
      {
	   echo '<center> Pages: ';
      }
      else
      {
  	   //echo '<center> No more posts under this category.';
      }
      $startPage = $page == 1 ? 1 : ($page-1);
      $displayed = 0;
      for($i = $startPage; $i <= (($page-1)+$config_maxPagesDisplayed); $i++)
      {
      	   if($i <= $totalPages)
	   {
	 	if($page != $i)
		{
			if($i == (($page-1)+$config_maxPagesDisplayed) && (($page-1)+$config_maxPagesDisplayed) < $totalPages)
			{
				echo  '<a href='.$_SERVER['PHP_SELF'].'?option=viewCategory&category='.$requestCategory.'&page='.$i.'>['.$i.']</a> ...';
			}
			elseif($startPage > 1 && $displayed == 0)
			{
				echo '... <a href='.$_SERVER['PHP_SELF'].'?option=viewCategory&category='.$requestCategory.'&page='.$i.'>['.$i.']</a> ';
	 			$displayed = 1;
			}
			else
			{
				echo '<a href='.$_SERVER['PHP_SELF'].'?option=viewCategory&category='.$requestCategory.'&page='.$i.'>['.$i.']</a> ';
			}
		}
		else
		{
			echo '['.$i.'] ';
		}
	    }
	}
	print '</center>';
  }


  function sidebarListEntries() {
      global $separator, $postdir, $entries, $config_menuEntriesLimit;
      $i=0;
      if (is_array($entries)) {
          foreach ($entries as $value) {
              if ($i < $config_menuEntriesLimit) {
                  $entry  =explode($separator,$value);
                  $title  =$entry[0];
                  $content=$entry[1];
                  $date1  =$entry[2];
                  $fileName=$entry[3];
                  $postType=$entry[5];
                  if ($postType!="page") {
                     echo "<a href=".$_SERVER['PHP_SELF']."?option=viewEntry&filename=".$fileName.">".$title."</a>";
                     $i++;
                  }
              }
          }
      }

  }

  function sidebarListComments() {
      global $separator, $postdir, $entries, $config_menuEntriesLimit;
      global $commentdir,$config_dbFilesExtension;
      $latestCommentsFile=$commentdir."latest".$config_dbFilesExtension;
      if (file_exists($latestCommentsFile)) {
          $allComments=file($latestCommentsFile);
          $allCommentsReversed=array_reverse($allComments);
          $i=0;
          foreach ($allCommentsReversed as $value) {
              if ($i < $config_menuEntriesLimit) {
                  $entry  =explode($separator,$value);
                  $commentFileName=$entry[0];
                  $commentTitle   =$entry[1];
                  $commentNum     =$entry[2];
                  /* Added #$commentNum below for 0.45 */
                  echo "<a href=".$_SERVER['PHP_SELF']."?option=viewEntry&filename=".$commentFileName."#".$commentNum.">".$commentTitle."</a>";
                  $i++;
              }
          }
      }
  }

  function sidebarPageEntries() {
      global $separator, $postdir, $entries, $config_menuEntriesLimit;
      $i=0;
      if (is_array($entries)) {
          foreach ($entries as $value) {
              if ($i < $config_menuEntriesLimit) {
                  $entry  =explode($separator,$value);
                  $title  =$entry[0];
                  $content=$entry[1];
                  $date1  =$entry[2];
                  $fileName=$entry[3];
                  $postType=$entry[5];
                  if ($postType=="page") {
                     echo "<a href=".$_SERVER['PHP_SELF']."?option=viewEntry&filename=".$fileName.">".$title."</a>";
                     $i++;
                  }
              }
          }
      }

  }

  function sidebarCategories() {
      global $separator, $postdir, $entries, $config_menuEntriesLimit;
      $category_array_unsorted=array();
      if (is_array($entries)) {
          foreach ($entries as $value) {
             $entry  =explode($separator,$value);
             $category=$entry[4];
             array_push($category_array_unsorted,$category);
          }
          $category_array_unique = array_unique($category_array_unsorted);
          //$category_array_sorted = sort($category_array_unique);
          //echo "Testing Category ..".$category_array_sorted[0]."<br>";
          // Sorting is not working. I need to check later .. todo
          foreach ($category_array_unique as $value) {
             /* 0.45 Changing ' ' to '.' */
             $categoryText=str_replace("."," ",$value);
             echo "<a href=".$_SERVER['PHP_SELF']."?option=viewCategory&category=".$value.">".$categoryText."</a>";
          }
      }
  }

  function sidebarLinks() {
      global $config_menuLinks;
      foreach ($config_menuLinks as $value) {
          $fullLink=explode(",",$value);
          echo '<a href="'.$fullLink[0].'">'.$fullLink[1].'</a>';
      }
  }

  function listAllComments() {
      global $commentdir, $separator;
      global $config_dbFilesExtension;
      $latestCommentsFile=$commentdir."latest".$config_dbFilesExtension;
      $userFileName=$commentdir."users$config_dbFilesExtension.dat";
      echo "<h1>All Comments</h1>";
      if ($handle = opendir($commentdir)) {
          $file_array_unsorted = array();
          $file_array_sorted   = array();
          while (false !== ($file = readdir($handle))) {
              array_push($file_array_unsorted,$file);
          }
          $file_array_sorted=array_reverse($file_array_unsorted);
          $commentCount=count($file_array_sorted)-4;
          if ($commentCount > 0) {
              echo "<table><tr><th>Comment Title</th><th>Date</th><th>Posted By</th></tr>";
          }
          else {
              echo "No Comments posted yet!<br>";
          }

          $statsFile=$commentdir."online$config_dbFilesExtension.dat";
          foreach ($file_array_sorted as $value) {
              $filename=$commentdir.$value;
              if ((file_exists($filename)) && ($filename !== $commentdir.".") && ($filename !== $commentdir."..") && ($filename !== $latestCommentsFile) && ($filename !== $userFileName) && ($filename !== $statsFile)){
                $fileContents = file($filename);
                foreach ($fileContents as $commentContents) {
                    $commentSplit=explode($separator,$commentContents);
                    $commentTitle=$commentSplit[0];
                    $commentAuthor=$commentSplit[1];
                    $commentDate=explode(" ",$commentSplit[3]);
                    $commentDateFormatted=$commentDate[0]." ".$commentDate[1]." ".$commentDate[2];
                    $commentFile=$commentSplit[4];
                    echo "<tr><td><a style='font-style:normal' href=".$_SERVER['PHP_SELF']."?option=viewEntry&filename=".$commentFile.">".$commentTitle."</a></td>";
                    echo "<td>".$commentDateFormatted."</td><td>".$commentAuthor."</td></tr>";
                }
              }
          }
          echo "</table>";
          closedir($handle);
      }
      return $file_array_sorted;
  }

  function viewCategory() {
      global $separator, $postdir, $entries;
      $requestedCategory=isset($_POST['category'])?$_POST['category']:$_GET['category'];
      foreach ($entries as $value) {
          $entry  =explode($separator,$value);
          $title  =$entry[0];
          $content=$entry[1];
          $date1  =$entry[2];
          $fileName=$entry[3];
          $category=$entry[4];
          $postType=$entry[5];
          if ($category==$requestedCategory) {
              echo "<h1>".$title."</h1>";
              echo $content;
              echo "<center><br/><i>Posted on ".$date1."&nbsp;-&nbsp; Category: <a href=".$_SERVER['PHP_SELF']."?option=viewCategory&category=".$category.">".$category."</a></i><br/>";
              echo "<a href=".$_SERVER['PHP_SELF']."?option=viewEntry&filename=".$fileName.">Comments</a>";
              echo "&nbsp;-&nbsp;<a href=".$_SERVER['PHP_SELF']."?option=editEntry&filename=".$fileName.">Edit</a>";
              echo "&nbsp;-&nbsp;<a href=".$_SERVER['PHP_SELF']."?option=deleteEntry&filename=".$fileName.">Delete</a><br/><br/></center>";
          }
      }

  }

  function newEntryForm() {
      global $postdir, $separator, $newPostFile, $newFullPostNumber, $debugMode, $config_textAreaCols, $config_textAreaRows;;
      $newPostFileName=$postdir.$newPostFile;
      echo "<h1>Making new entry...</h1>";
      if ($debugMode=="on") {
         echo $_SERVER['PHP_SELF']."<br>";
         echo "Post will be written to ".$newPostFileName."  ".$newFullPostNumber;
      }
      echo '<script type="text/javascript">';
      echo '    bkLib.onDomLoaded(function(){';
      echo "          new nicEditor({fullPanel : true}).panelInstance('posts');";
      echo "          });";
      echo "</script>";
      echo "<form method=\"post\" action=".$_SERVER['PHP_SELF']."?option=newEntrySubmit>";
      echo "<table><tr><td>Title</td><td>";
      echo "<input name=title type=text id=title></td></tr>";
      echo "<tr><td>Content<br /></td>";
      echo "<td><textarea name=\"posts\" cols=\"".$config_textAreaCols."\" rows=\"".$config_textAreaRows."\"";
      echo ' style="height: 400px; width: 400px;" id="posts"></textarea>';
      echo "</td></tr><tr><td>Category<br />";
      echo '<td><input name="category" type="text" id="category"></td>';
      echo '</tr><tr><td>Is A Page <a href="javascript:alert(\'A page is basically a post which is linked in the menu and not displayed normally\')">(?)</a></td>';
      echo '<td><input type="checkbox" name="isPage" value="1"></td>';
      echo '</tr><tr><td>Pass</td><td><input name="pass" type="password" id="pass">';
      echo '<input name="process" type="hidden" id="process" value="newEntry"></td></tr><tr><td>&nbsp;</td><td>';
      echo '<input type="submit" name="Submit" value="Add Entry"></td></tr></table>';
      echo "</form>";
  }

  function newEntrySubmit() {
      global $postdir, $separator, $newPostFile, $newFullPostNumber, $debugMode, $configPass;
      $newPostFileName=$postdir.$newPostFile;
      $postTitle=$_POST["title"];
      $postContent=$_POST["posts"];
      $postDate=date("d M Y h:i");
      $isPage=$_POST["isPage"];
      /* 0.45 added strtolower to make category case insensitive */
      $postCategory=strtolower($_POST["category"]);
      echo "<h1>Making new entry...</h1>";
      $do = 1;
      /* 0.45 added strstr($postCategory,'.') and trim*/
      if(trim($postTitle) == '' || trim($postContent) == '' || trim($postCategory) == '' || strstr($postCategory,'.'))
      {
      	   echo 'All fields are neccessary. Please fill them all.<br>';
      	   /* 0.45 Back button */
      	   echo "Also, category names cannot have '.' in them<br>";
      	   echo 'Hit the back button on your browser to go back, change and submit again';
	   $do = 0;
      }
      if ($do == 1) {
          if ($_POST['pass']===$configPass) {
              /* 0.45 Changing ' ' to '.' */
              $postCategory=str_replace(" ",".",$postCategory);
              if ($debugMode=="on") {echo "Writing to ".$newPostFileName;}
              $errorMessage='<br><span style="color: rgb(204, 0, 51);">Error opening or writing to newPostFile '.$newPostFileName.'. <br>Please check the folder permissions<br>';
              $errorMessage=$errorMessage."<br>If this problem continues, please report as a bug to the author of PRITLOG<br>";
              if ($isPage == 1) {
                  $postType="page";
              }
              else {
                  $postType="post";
              }
              $content=$postTitle.$separator.str_replace("\\","",$postContent).$separator.$postDate.$separator.$newFullPostNumber.$separator.$postCategory.$separator.$postType;
              $fp = fopen($newPostFileName,"w") or die($errorMessage);
              fwrite($fp, $content) or die($errorMessage);
              fclose($fp);
              echo "New post added ..";
          }
          else {
              echo "Password Incorrect .. <br/>";
              /* 0.45 Back button */
              echo "Hit the back button on your browser to go back, change and submit again";
          }
      }
  }

  function deleteEntryForm() {
      global $debugMode;
      $fileName = isset($_POST['filename'])?$_POST['filename']:$_GET['filename'];
      echo "<h1>Deleting entry...</h1>";
      echo "<form name=\"form1\" method=\"post\" action=".$_SERVER['PHP_SELF']."?option=deleteEntry>";
      echo "<table><td>Pass</td>";
      echo "<td><input name=\"pass\" type=\"password\" id=\"pass\">";
      echo "<input name=\"process\" type=\"hidden\" id=\"process\" value=\"deleteEntrySubmit\">";
      echo "<input name=\"fileName\" type=\"hidden\" id=\"fileName\" value=\"".$fileName."\"></td>";
      echo "</tr><tr><td>&nbsp;</td><td><input type=\"submit\" name=\"Submit\" value=\"Delete Entry\"></td>";
      echo "</tr></table></form>";
  }

  function deleteEntrySubmit() {
       global $postdir, $separator, $newPostFile, $newFullPostNumber, $configPass, $debugMode, $config_dbFilesExtension;
       if ($debugMode=="on") {echo "Inside deleteEntrySubmit ..<br>";}
       $entryName= $_POST['fileName'];
       $fileName = $postdir.$entryName.$config_dbFilesExtension;
       echo "<h1>Deleting entry...</h1>";
       $errorMessage='<br><span style="color: rgb(204, 0, 51);">There was an error deleting entry '.$entryName.'. <br>Please check the folder permissions<br>';
       $errorMessage=$errorMessage."If this problem continues, please report as a bug to the author of PRITLOG<br>";
       if ($_POST['pass']===$configPass) {
          if (file_exists($fileName)) {unlink($fileName);}
          if (deleteEntryComment($entryName)) {
             echo "Entry ".$entryName." deleted succesfully...<br/>";
          }
          else { exit($errorMessage); }
       }
       else {
          echo "Password Incorrect .. <br/>";
       }
  }

  function deleteEntryComment($entryName) {
       global $commentdir,$config_dbFilesExtension,$separator;
       $commentFullName=$commentdir.$entryName.$config_dbFilesExtension;
       $thisCommentFileName=$entryName;
       if (file_exists($commentFullName)) {unlink($commentFullName);}
       //echo "<br>$commentFullName deleted<br>";
       $latestFileName=$commentdir."/latest".$config_dbFilesExtension;
       if (file_exists($latestFileName)) {
             $latestLines= file($latestFileName);
             $errorMessage='<br><span style="color: rgb(204, 0, 51);">Error opening or writing to latestFileName '.$latestFileName.'. <br>Please check the folder permissions<br>';
             $errorMessage=$errorMessage."If this problem continues, please report as a bug to the author of PRITLOG<br>";
             $fp=fopen($latestFileName, "w") or die($errorMessage);
             $i=0;
             foreach ($latestLines as $value) {
                 $latestSplit=explode($separator,$value);
                 $commentFileName=trim($latestSplit[0]);
                 if (trim($value) != "") {
                     //echo "commentFileName=$commentFileName,thisCommentFileName=$thisCommentFileName,<br>";
                     //echo "commentSeq=$commentSeq,thisCommentSeq=".trim($thisCommentSeq).",<br>";
                     if (($commentFileName == $thisCommentFileName)){
                         //echo "Deleted Indeed!<br>";
                     }
                     else {
                         fwrite($fp,$value);
                     }
                  }
                  $i++;
             }
             fclose($fp);
         }
         if (file_exists($commentFullName)) {return false;}
         else {return true;}
  }

  function editEntryForm() {
      $fileName   = isset($_POST['filename'])?$_POST['filename']:$_GET['filename'];
      global $postdir, $separator, $newPostFile, $newFullPostNumber, $debugMode, $config_textAreaCols, $config_textAreaRows;
      global $config_dbFilesExtension;
      $editFileName=$postdir.$fileName.$config_dbFilesExtension;
      echo "<h1>Editing entry...</h1>";
      if ($debugMode=="on") {echo "Editing .. ".$editFileName."<br>";}
      if (file_exists($editFileName)) {
        $fp = fopen($editFileName, "rb");
        $fullpost=explode($separator,fread($fp, filesize($editFileName)));
        fclose($fp);
        $title=$fullpost[0];
        $content=$fullpost[1];
        $category=$fullpost[4];
        $postType=$fullpost[5];
        if ($postType == "page") {
            $checking='checked="checked"';
        }
        else {
            $checking='';
        }
        echo '<script type="text/javascript">';
        echo '    bkLib.onDomLoaded(function(){';
                  /*nicEditors.allTextAreas);*/
        echo "          new nicEditor({fullPanel : true}).panelInstance('posts');";
        echo "          });";
        echo "</script>";
        echo "<form method=\"post\" action=".$_SERVER['PHP_SELF']."?option=editEntry>";
        echo "<table><tr><td>Title</td><td>";
        echo "<input name=title type=text id=title value='".$title."'></td></tr>";
        echo "<tr><td>Content<br /></td>";
        echo "<td><textarea name=\"posts\" cols=\"".$config_textAreaCols."\" rows=\"".$config_textAreaRows."\"";
        echo ' style="height: 400px; width: 400px;" id="posts">';
        echo $content;
        echo '</textarea>';
        echo "</td></tr><tr><td>Category<br />";
        /* 0.45 Changing '.' to ' ' */
        $category=str_replace("."," ",$category);
        echo '<td><input name="category" type="text" id="category" value="'.$category.'"></td>';
        echo '</tr><tr><td>Is A Page <a href="javascript:alert(\'A page is basically a post which is linked in the menu and not displayed normally\')">(?)</a></td>';
        echo '<td><input type="checkbox" name="isPage" value="1" '.$checking.'></td>';
        echo "<input name=\"fileName\" type=\"hidden\" id=\"fileName\" value=\"".$fileName."\">";
        echo '</tr><tr><td>Pass</td><td><input name="pass" type="password" id="pass">';
        echo '<input name="process" type="hidden" id="process" value="editEntrySubmit"></td></tr><tr><td>&nbsp;</td><td>';
        echo '<input type="submit" name="Submit" value="Edit Entry"></td></tr></table>';
        echo "</form>";

      }
      else {echo "File is not available...<br>";}
  }

  function editEntrySubmit() {
      global $postdir, $separator, $newPostFile, $newFullPostNumber, $configPass, $debugMode;
      global $config_dbFilesExtension;
      if ($debugMode=="on") {echo "Inside editEntrySubmit ..".$_POST['fileName']."<br>";}
      echo "<h1>Editing entry...</h1>";
      $entryName= $_POST['fileName'];
      $fileName = $postdir.$entryName.$config_dbFilesExtension;
      $postTitle=$_POST["title"];
      $postContent=$_POST["posts"];
      $postDate=date("d M Y h:i");
      $isPage=$_POST["isPage"];
      /* 0.45 added strtolower to make category case insensitive */
      $postCategory=strtolower($_POST["category"]);
      $do = 1;
      /* 0.45 add trim */
      if(trim($postTitle) == '' || trim($postContent) == '' || trim($postCategory) == '' || strstr($postCategory,'.'))
      {
      	   echo "All fields are neccessary. Please fill them all.<br>";
      	   echo "Also, category names cannot have '.' in them<br>";
      	   echo 'Hit the back button on your browser to go back, change and submit again';
	   $do = 0;
      }
      if ($do == 1) {
          if ($isPage == 1) {
              $postType="page";
          }
          else {
              $postType="post";
          }
          if ($debugMode=="on") {echo "Writing to ".$fileName;}
          $errorMessage='<br><span style="color: rgb(204, 0, 51);">Error opening or writing to PostFile '.$fileName.'. <br>Please check the folder permissions<br>';
          $errorMessage=$errorMessage."<br>If this problem continues, please report as a bug to the author of PRITLOG<br>";
          if ($_POST['pass']===$configPass) {
              /* 0.45 Changing ' ' to '.' */
              $postCategory=str_replace(" ",".",$postCategory);
              $content=$postTitle.$separator.str_replace("\\","",$postContent).$separator.$postDate.$separator.$entryName.$separator.$postCategory.$separator.$postType;
              $fp = fopen($fileName,"w") or die($errorMessage);
              fwrite($fp, $content) or die($errorMessage);
              fclose($fp);
              echo "Entry edited successfully .. <br/>";
          }
          else {
              echo "Password Incorrect .. <br/>";
          }
      }
  }

  function viewEntry() {
      $fileName   = isset($_POST['filename'])?$_POST['filename']:$_GET['filename'];
      global $postdir, $separator, $newPostFile, $newFullPostNumber, $debugMode, $config_textAreaCols, $config_textAreaRows;
      global $config_allowComments, $config_commentsSecurityCode, $config_CAPTCHALength, $config_randomString;
      global $commentdir,$config_dbFilesExtension, $config_onlyNumbersOnCAPTCHA;
      $viewFileName=$postdir.$fileName.$config_dbFilesExtension;
      $cool=true;
      if ($debugMode=="on") {echo "Editing .. ".$viewFileName."<br>";}
      if (strstr($fileName,'%') || strstr($fileName,'.')) {
        $cool=false;
        echo "<br>Sorry. Invalid URL!<br>";
      }
      if (file_exists($viewFileName) && $cool) {
          $fp = fopen($viewFileName, "rb");
          $entry   =explode($separator,fread($fp, filesize($viewFileName)));
          fclose($fp);
          $title   =$entry[0];
          $content =$entry[1];
          $date1   =$entry[2];
          $fileName=$entry[3];
          $category=$entry[4];
          $postType=$entry[5];
          echo "<h1>".$title."</h1>";
          echo $content;
          echo "<center><br/><i>Posted on ".$date1."&nbsp;-&nbsp; Category: <a href=".$_SERVER['PHP_SELF']."?option=viewCategory&category=".$category.">".$category."</a></i><br/>";
          $commentFile=$commentdir.$fileName.$config_dbFilesExtension;
          if (file_exists($commentFile)) {
              $commentLines=file($commentFile);
              $commentText=count($commentLines)." Comments";
          }
          else {$commentText="No Comments";}
          /* 0.45 #Comments */
          echo "<a href=".$_SERVER['PHP_SELF']."?option=viewEntry&filename=".$fileName."#Comments>".$commentText."</a>";
          echo "&nbsp;-&nbsp;<a href=".$_SERVER['PHP_SELF']."?option=editEntry&filename=".$fileName.">Edit</a>";
          echo "&nbsp;-&nbsp;<a href=".$_SERVER['PHP_SELF']."?option=deleteEntry&filename=".$fileName.">Delete</a><br/><br/></center>";


          $commentFullName=$commentdir.$fileName.$config_dbFilesExtension;
          $i=0;
          echo "<a name='Comments'></a><h1>Comments:</h1>";
    	  if (file_exists($commentFullName)) {
               $fp = fopen($commentFullName, "rb");
    	       $allcomments=explode("\n",fread($fp, filesize($commentFullName)));
               fclose($fp);
               foreach ($allcomments as $value) {
                    if (trim($value) != "") {
                        $comment = explode($separator,$value);
                        $title   = $comment[0];
                        $author  = $comment[1];
                        $content = $comment[2];
                        $date    = $comment[3];
                        /* Added $comment[5] below for 0.45 */
                        echo '<a name="'.$comment[5].'">Posted on <b>'.$date.'</b> by <b>'.$author.'</b><br /><i>'.$title.'</i><br /></a>';
                        echo $content;
                        echo '<br><a href="'.$_SERVER['PHP_SELF'].'?option=deleteComment&filename='.$fileName.'&commentNum='.$i.'">Delete</a><br><br><br>';
                        $i++;
                    }
               }

    	  }
          else {echo "No comments posted yet!<br>";}


          if($config_allowComments == 1)
          {
	 	echo '<br /><br /><h1>Add Comment</h1>';
	 	echo '<script type="text/javascript">';
                echo '    bkLib.onDomLoaded(function(){';
                echo "          new nicEditor({buttonList : ['bold','italic','underline','link','unlink']}).panelInstance('comment');";
                echo "          });";
                echo "</script>";
		echo '<form name="submitform" method="post" action="'.$_SERVER['PHP_SELF'].'?option=sendComment">';
		echo '<table><tr><td>Title</td><td><input name="commentTitle" type="text" id="commentTitle"></td>';
		echo '</tr><tr><td>Author</td><td><input name="author" type="text" id="author"></td></tr>';
		echo '<tr><tr><td>Content</td>';
		echo '<td><textarea name="comment" id="comment" cols="'.$config_textAreaCols.'" rows="'.$config_textAreaRows.'"></textarea></td></tr><tr>';
		if($config_commentsSecurityCode == 1)
		{
			$code = '';
			if($config_onlyNumbersOnCAPTCHA == 1)
			{
				$code = substr(rand(0,999999),1,$config_CAPTCHALength);
			}
			else
			{
				$code = strtoupper(substr(crypt(rand(0,999999), $config_randomString),1,$config_CAPTCHALength));
			}
			echo '<td>Security Code</td><td><font face="Verdana, Arial, Helvetica, sans-serif" size="2">'.$code.'</font>';
                        echo '<input name="originalCode" value="'.$code.'" type="hidden" id="originalCode"></td>';
			echo '</tr><tr><td></td><td><input name="code" type="text" id="code"></td></tr>';
		}
		echo '<tr><td>Password (So people cannot steal your identity)</td>';
		echo '<td><input name="pass" type="password" id="pass"></td></tr><tr><td>&nbsp;</td>';
		//echo '<td><input type="submit" onclick="javascript:this.disabled=true" name="Submit" value="Add Comment">';
		echo '<td><input type="submit" name="Submit" value="Add Comment">';
                echo '<input name="sendComment" value="'.$fileName.'" type="hidden" id="sendComment"></td></tr></table></form>';
          }
      }
  }

  function sendComment() {
	# Send Comment Process
        global $commentdir, $separator, $config_commentsMaxLength, $config_dbFilesExtension, $config_sendMailWithNewComment;
        global $config_sendMailWithNewCommentMail, $config_commentsForbiddenAuthors, $config_commentsSecurityCode;
	echo "<h1>Add Comment</h1>";
        $commentFileName= isset($_POST['sendComment'])?$_POST['sendComment']:$_GET['sendComment'];
	$commentTitle   = isset($_POST['commentTitle'])?$_POST['commentTitle']:$_GET['commentTitle'];
	$author  = isset($_POST['author'])?$_POST['author']:$_GET['author'];
	$comment = isset($_POST['comment'])?$_POST['comment']:$_GET['comment'];
	$pass    = isset($_POST['pass'])?$_POST['pass']:$_GET['pass'];
	$date    = getdate($config_gmt);
	$code    = $_POST['code'];
	$originalCode = $_POST['originalCode'];
	$do = 1;
	$triedAsAdmin = 0;
	//echo '<br>Comment Length='.strlen($comment).',<br>';
	//echo 'Comment='.$comment.',<br>';
	//echo 'Author='.$author.',<br>';

        /* 0.45 Author cannot be spaces anymore */
	if(trim($commentTitle) == '' || trim($author) == '' || trim($comment) == '' || trim($pass) == '')
	{
		echo 'All fields are neccessary. Please fill them all.<br>';
		echo 'Hit the back button on your browser to go back, change and submit again';
		$do = 0;
	}

	if($config_commentsSecurityCode == 1)
	{
		$code = $_POST['code'];
		$originalCode = $_POST['originalCode'];
		if ($code !== $originalCode)
		{
			echo 'Security Code does not match. Please, try again';
			$do = 0;
		}
	}

	$hasPosted = 0;

        foreach($config_commentsForbiddenAuthors as $value)
	{
		if($value == $author)
		{
                     echo "The user name ".$author." is not permitted. Please go back and choose a different username";
  		     $do=0;
		}
	}

	# Start of author checking, for identity security
        $userFileName=$commentdir."users$config_dbFilesExtension.dat";
	//$fp = fopen($userFileName, "rb");
	//$users=explode("\n",fread($fp, filesize($userFileName)));
	//fclose($fp);
	$newUser = 1;
	if (file_exists($userFileName)) {
            $users=file($userFileName);
  	    $data = '';
  	    if ($do == 1) {
                foreach($users as $value)
                {
        		$userLine=explode($separator,$value);
        		if ($userLine[0] == $author) {
                            $newUser=0;
                            if (crypt($pass,trim($userLine[1])) !== trim($userLine[1])) {echo "Password is incorrect, please try again";$do=0;}
                            else {$do=1;}
                        }
                }
	    }
	}

	if ($newUser == 1 && $do ==1)
	{
                $fp = fopen($userFileName, "a");
		$userContent=$author.$separator.crypt($pass)."\n";
		fwrite($fp,$userContent);
		fclose($fp);
		echo 'You are a new user posting here... You will be added to a database so nobody can steal your identity. Remember your password!<br>';
	}

	if($do == 1)
	{
		if(strlen($comment) > $config_commentsMaxLength)
		{
		     echo 'The content is too long! Max characters is '.$config_commentsMaxLength.' you typed '.strlen($comment);
		}
                else
		{
                     $commentFullName=$commentdir.$commentFileName.$config_dbFilesExtension;
 		     if (file_exists($commentFullName)) {$commentLines=file($commentFullName);}
 		     if (trim($commentLines[0])=="") {
                         $thisCommentSeq=1;
                     }
                     else {
 		         $thisCommentSeq=count($commentLines)+1;
                     }
                     $comment=str_replace("\n","",$comment);
                     $commentContent = $commentTitle.$separator.$author.$separator.str_replace("\\","",$comment).$separator.date("d M Y h:i").$separator.$commentFileName.$separator.$thisCommentSeq."\n";
 		     #  Add comment
 		     $errorMessage='<br><span style="color: rgb(204, 0, 51);">Error opening or writing to commentFileName '.$commentFullName.'. <br>Please check the folder permissions<br>';
 		     $errorMessage=$errorMessage."<br>If this problem continues, please report as a bug to the author of PRITLOG<br>";
 		     $fp = fopen($commentFullName, "a") or die($errorMessage);
		     fwrite($fp,$commentContent) or die($errorMessage);
		     fclose($fp);

                     # Add coment number to a file with latest comments
                     $errorMessage='<br><span style="color: rgb(204, 0, 51);">Error opening or writing to commentFileName '.$commentFileName.'. <br>Please check the folder permissions<br>';
                     $errorMessage=$errorMessage."<br>If this problem continues, please report as a bug to the author of PRITLOG<br>";
		     $fp=fopen($commentdir."/latest".$config_dbFilesExtension,"a") or die($errorMessage);
                     fwrite($fp,$commentFileName.$separator.$commentTitle.$separator.$thisCommentSeq."\n") or die($errorMessage);
		     fclose($fp);
                     echo 'Comment added. Thanks '.$author.'!<br />';

                     # If Comment Send Mail is active
		     if($config_sendMailWithNewComment == 1)
                     {
		 	 $content = "Hello, i am sending this mail because $author commented on your blog. \r\nTitle: $commentTitle\r\nComment: ".str_replace("\\","",$comment)."\r\nDate: ".date("d M Y h:i")."\r\nRemember you can disallow this option changing the ".'$config_sendMailWithNewComment Variable to 0';
		 	 if(mail($config_sendMailWithNewCommentMail,
                         "PRITLOG: New Comment",
                         $content
                         ,"FROM: PRITLOG")) {
                               echo '<br>Comment email has been sent.';
                         }
                         else {
                             echo '<br>Comment mail could not be sent. Please make sure your server allows this.<br>';
                             echo 'You can change $config_sendMailWithNewComment to change the setting.';
                         }
  		     }
		}
	}
  }

  function deleteCommentForm() {
      global $debugMode;
      $fileName = isset($_POST['filename'])?$_POST['filename']:$_GET['filename'];
      $commentNum = isset($_POST['commentNum'])?$_POST['commentNum']:$_GET['commentNum'];
      echo "<h1>Deleting comment...</h1>";
      echo "<form name=\"form1\" method=\"post\" action=".$_SERVER['PHP_SELF']."?option=deleteComment>";
      echo "<table><td>Pass</td>";
      echo "<td><input name=\"pass\" type=\"password\" id=\"pass\">";
      echo "<input name=\"process\" type=\"hidden\" id=\"process\" value=\"deleteCommentSubmit\">";
      echo "<input name=\"fileName\" type=\"hidden\" id=\"fileName\" value=\"".$fileName."\">";
      echo "<input name=\"commentNum\" type=\"hidden\" id=\"commentNum\" value=\"".$commentNum."\"></td>";
      echo "</tr><tr><td>&nbsp;</td><td><input onclick=\"javascript:this.disabled=true\" type=\"submit\" name=\"Submit\" value=\"Delete Comment\"></td>";
      echo "</tr></table></form>";
  }

  function deleteCommentSubmit() {
       global $postdir, $separator, $newPostFile, $newFullPostNumber, $configPass, $debugMode;
       global $commentdir,$fileName,$config_dbFilesExtension;
       if ($debugMode=="on") {echo "Inside deleteCommentSubmit ..<br>";}
       $fileName = $_POST['fileName'];
       echo "<h1>Deleting comment...</h1>";
       $commentNum=$_POST['commentNum'];
       if ($_POST['pass']===$configPass) {
            $commentFullName=$commentdir.$fileName.$config_dbFilesExtension;
            $i=0;
            $j=0;
            if (file_exists($commentFullName)) {
            $allcomments=file($commentFullName);
            $errorMessage='<br><span style="color: rgb(204, 0, 51);">Error opening or writing to commentFile '.$commentFullName.'. <br>Please check the folder permissions<br>';
            $errorMessage=$errorMessage."<br>If this problem continues, please report as a bug to the author of PRITLOG<br>";
            $fp=fopen($commentFullName, "w");
            foreach ($allcomments as $value) {
                //echo "value=$value,";
                if (trim($value) != "") {
                    //echo "commentNum=$commentNum,i=$i,<br>";
                    if ($commentNum != $i) {
                        //$value=$value."\n";
                        if (fwrite($fp,$value)===FALSE) {
                             echo "Cannot write to comment file<br>";
                        }
                        else { $j++;}
                    }
                    else {
                        $commentSplit=explode($separator,$value);
                        $thisCommentFileName=$commentSplit[4];
                        $thisCommentSeq=$commentSplit[5];
                        echo "Comment deleted ...<br>";
                    }
                }
                $i++;
             }
             fclose($fp);
             //echo "Final i=$i<br>";
             $i=$i-2;
             if ($j == 0) {unlink($commentFullName);}
             $latestFileName=$commentdir."/latest".$config_dbFilesExtension;
             if (file_exists($latestFileName)) {
                   $latestLines= file($latestFileName);
                   $errorMessage='<br><span style="color: rgb(204, 0, 51);">Error opening or writing to latestFileName '.$latestFileName.'. <br>Please check the folder permissions<br>';
                   $errorMessage=$errorMessage."If this problem continues, please report as a bug to the author of PRITLOG<br>";
                   $fp=fopen($latestFileName, "w") or die($errorMessage);
                   $i=0;
                   foreach ($latestLines as $value) {
                        $latestSplit=explode($separator,$value);
                        $commentFileName=trim($latestSplit[0]);
                        $commentSeq     =trim($latestSplit[2]);
                        if (trim($value) != "") {
                           //echo "commentFileName=$commentFileName,thisCommentFileName=$thisCommentFileName,<br>";
                           //echo "commentSeq=$commentSeq,thisCommentSeq=".trim($thisCommentSeq).",<br>";
                           if (($commentFileName == $thisCommentFileName) && ($commentSeq == trim($thisCommentSeq))){
                               //echo "Deleted Indeed!<br>";
                           }
                           else {
                               fwrite($fp,$value);
                           }
                        }
                        $i++;
                   }
                   fclose($fp);
               }
    	  }
          //else {echo "No comments posted yet!<br>";}
       }
       else {
          echo "Password Incorrect .. <br/>";
       }
  }


  function viewArchive() {
      global $separator, $postdir, $entries, $config_menuEntriesLimit;
      $i=0;
      echo "<h1>Archive</h1>";
      echo "<table>";
      foreach ($entries as $value) {
          $entry  =explode($separator,$value);
          $title  =$entry[0];
          $content=$entry[1];
          $date1  =$entry[2];
          $fileName=$entry[3];
          $postType=$entry[5];
          echo "<tr><td>".$date1.":&nbsp;</td><td><a style='font-style:normal' href=".$_SERVER['PHP_SELF']."?option=viewEntry&filename=".$fileName.">".$title."</a></td></tr>";
      }
      echo "</table>";

  }

  function searchPosts() {
      global $separator, $postdir, $entries;
      $searchkey   = isset($_POST['searchkey'])?$_POST['searchkey']:$_GET['searchkey'];
      echo "<h1>Search Results</h1>";
      $i=0;
      foreach ($entries as $value) {
          $entry  =explode($separator,$value);
          $title  =$entry[0];
          $content=$entry[1];
          $date1  =$entry[2];
          $fileName=$entry[3];
          $category=$entry[4];
          $postType=$entry[5];
          if ((stristr($title,$searchkey)) || (stristr($content,$searchkey))) {
              echo "<a style='font-style:normal' href=".$_SERVER['PHP_SELF']."?option=viewEntry&filename=".$fileName.">".$title."</a><br/>";
              $i++;
          }
      }
      if ($i == 0) {echo "Sorry no matches found!";}
  }


  switch ($option) {
  case "newEntry":
      if ($debugMode=="on") {echo "Calling newEntryForm()";}
      newEntryForm();
      break;
  case "newEntrySubmit":
      newEntrySubmit();
      break;
  case "mainPage":
      $requestCategory = '';
      listPosts();
      break;
  case "deleteEntry":
      if ($debugMode=="on") {echo "deleteEntry  ".$_POST['process']."<br>";}
      if ($_POST['process']!=="deleteEntrySubmit") {
          deleteEntryForm();
      }
      else {
          deleteEntrySubmit();
      }
      break;
  case "editEntry":
      if ($debugMode=="on") {echo "editEntry  ".$_POST['process']."<br>";}
      if ($_POST['process']!=="editEntrySubmit") {
          editEntryForm();
      }
      else {
          editEntrySubmit();
      }
      break;
  case "viewEntry":
      viewEntry();
      break;
  case "commentEntry":
      echo "<h1>Ooops</h1>";
      echo "Sorry! This functionality is not coded yet.<br>";
      break;
  case "viewArchive":
      viewArchive();
      break;
  case "viewCategory":
      $requestCategory=isset($_POST['category'])?$_POST['category']:$_GET['category'];
      listPosts();
      break;
  case "searchPosts":
      searchPosts();
      break;
  case "sendComment":
      sendComment();
      break;
  case "listAllComments":
      listAllComments();
      break;
  case "deleteComment":
      if ($debugMode=="on") {echo "deleteEntry  ".$_POST['process']."<br>";}
      if ($_POST['process']!=="deleteCommentSubmit") {
          deleteCommentForm();
      }
      else {
          deleteCommentSubmit();
      }
      break;
  }

if($option !== 'RSS') {
?>
</div>
<?php /* PLEASE DONT REMOVE THIS COPYRIGHT WITHOUT PERMISSION FROM THE AUTHOR */ ?>
<?php echo '</div><div id="footer">Copyright '.$config_blogTitle.' 2008 - All Rights Reserved - Powered by <a href="http://hardkap.net/pritlog/">PRITLOG</a></div></div>'; ?>
</body>
</html>
<?php } ?>