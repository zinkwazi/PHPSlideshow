<?php
/* 
PHPSlideShow v0.9 written by Greg Lawler
from http://www.zinkwazi.com/scripts

v0.9.9.3 Jan 2008 - security fix to address xss vulnerability discovered by Jose Luis Góngora Fernández
v0.9.9.1 Nov 2006 - stop infinite auto slideshow
v0.9.9 June 2006 - added auto thumbnail creation 
v0.9.8 June 2006 - fixed the pics.txt special characters bug :)
v0.9.7 May 2006 - fixed some Notify errors...
v0.9.6 Feb 2006 - Added the <NEXT_IMAGE> tag and javascrip preloading.
v0.9.5 Christmas 2005 - added sort by name and filmstrip thumbnail view
v0.9.2 Aug 2005 - depricated $GLOBALS[] replaced with _GET
v0.9.1 Aug 2005 - bug fixed to allow use on PHP < 4.3
v0.9 June 2005 - major re-write, now template based
v0.6.2 august 2002 - minor upgrade, added javascript notes
v0.6.1 july 2002 - fixed special character bug.
v0.6 july 2002 - added lots of formatting options and a security patch
v0.5.1 march 2002 - minor bug fixes, reg exp fix...
v0.5 march 2002 - osx path fix, page headings for multi dir, cleaner...
v0.4 july 10 2001
v0.3.5 july 5 2001
v0.3.4 april 19 2001
v0.3.3 january 9 2001
v0.3.1 september 29 2000 - added support for navigation image buttons
v0.3 september 12 2000 - added support for image descriptions
v0.2 august 28 2000

PHPSlideshow is relesed under the GPL
See the license at http://www.gnu.org/licenses/gpl.txt
Feel free to use/modify this little script

IMPORTANT NOTE.... ;)
if you want to send me a token of appreciation, i like coffee so
anything from http://www.starbucks.com/card will be gladly accepted ;)
my address is:
attention: greg lawler
801 alston road, santa barbara, ca 93108 usa

INSTALLATION: See README.txt

enjoy :)
greg
*/

// NOTE: your phpslideshow.php script will work "out of the box" and 
// all layout and visual effects are controlled by the template.html file.
// you can fine tune your slideshow by editing some of these settings.

// ------------- BEGIN CONFIG SECTION --
// error reporting
// error_reporting(E_ALL);

// number of thumbnails to display f a thumbnail directory exists
// (note that this will be rounded down to an odd 
// number if you use $thumbnail_view = "circular";.)
$thumb_row = 19;

// thumbnail directory name (no slashes needed)
$thumbnail_dir = "thumbnails";

// do you want to automatically generate thumbnails?
// if this is set to true, you will need to provide your web site
// ftp login info and web root directory below.
// NOTE: Thumbnails are created the first time you view the slideshow.
// $create_thumbnails = "true";
$create_thumbnails = "false";

// ftp user information for automatic thumbnails
$ftp_username = "your_username_here";
$ftp_password = "your_password_here";

// ftp web root directory (httpdocs, wwwroot, webroot etc.)
// this is the directory that your website is located in on your FTP login.
// (no slashes needed)
$ftp_web_root = "httpdocs";

// thumbnail size
$thumbnail_max_width = 60;
$thumbnail_max_height = 40;

// $thumbnail_style:  "scale" will resize and maintain proportions to the original image
// "crop" will resize to exactly the thumbnail size you specify
$thumbnail_style = "crop";

// output quality: 60 = poor quality tiny file, 100 = best quality larger file... 
$thumbnail_quality = 80;

// this should work as localhost but if not, insert your ftp server's name
$ftp_hostname = "localhost";

// sort/display images with newest or oldest on top or alphabetically by name.
// this has no effect when pics.txt is used.
// $sort_images = "oldest"; 
//$sort_images = "newest"; 
$sort_images = "name"; 

// thumbnail view: can be "filmstrip" or "circular"
//$thumbnail_view = "circular";
$thumbnail_view = "filmstrip";

// name of file containing optional page headings
$heading_info_file = "heading.txt";

// file containing optional image descriptions
$pic_info_file="pics.txt";

// language text for various areas...
$lang_back = "back";
$lang_next = "next";
$lang_of = "of";
$lang_stop_slideshow = "stop slideshow";
$lang_start_slideshow = "start slideshow";
$lang_img_hover = "click for next image...";
$lang_img_alt = "slideshow image";

// automated slideshow options
// remember that you need <META_REFRESH> in the <head> section of your html
// AND the <AUTO_SLIDESHOW_LINK> tag in your page.
// $delay is the number of seconds to pause between slides...
$delay = 2;
// set to true to display navigation icons instead of text...
$show_navigation_buttons = "false";
$back_button = "/back.gif"; 
$next_button = "/next.gif";

// ------------- END CONFIG SECTION --

################################################################################
// begin code :: don't make changes below this line...

// initialize some stuff:
$auto_url = "";
$thumbnail_actual_width = "";
$thumbnail_actual_height = "";

// grab the variables we want set for newer php version compatability
$path_to_images = isset($_GET['directory']) ? strip_tags($_GET['directory']) : '';
$currentPic = isset($_GET['currentPic']) ? strip_tags($_GET['currentPic']) : '';
$auto = isset($_GET['auto']) ? strip_tags($_GET['auto']) : '';

// check for platform dependent path info... (for windows and mac OSX)
$path = empty($HTTP_SERVER_VARS['PATH_INFO'])?
$HTTP_SERVER_VARS['PHP_SELF']:$HTTP_SERVER_VARS['PATH_INFO'];

// this only works on php > 4.3, replacing with file()
//if( file_exists( "template.html" ) ) $template = file_get_contents("template.html");
if( file_exists( "template.html" ) ) $template = implode("", file('template.html'));
else {
    echo "<b>ERROR:</b> Can't find the template.html file!";
    exit;
}

// check that the user did not change the path...
if (preg_match(':(\.\.|^/|\:):', $path_to_images) || strpos($path_to_images, $thumbnail_dir) !== false) {
	echo "<strong>ERROR:</strong> Your request contains an invalid path.<br />
    Can not locate <strong>$path_to_images</strong> in this directory.<br />";
	exit;
}

if (empty($path_to_images)) $path_to_images = ".";
// if there is no $heading_info_file (see format above) set page heading here
if ( !file_exists("$path_to_images/$heading_info_file")) {
	$header = "PHPSlideshow by greg lawler at zinkwazi.com";
	$title = "<a href=\"http://www.zinkwazi.com/\">PHPSlideshow by greg lawler at zinkwazi.com</a>";
}
else {
	$heading_info = file("$path_to_images/$heading_info_file");
	$header = "$heading_info[0]";
	$title = $header;
}
$template = str_replace("<SHOW_TITLE>",$title,$template);
$template = str_replace("<WINDOW_TITLE>",$header,$template);

// image / text buttons
if ($show_navigation_buttons == "true") {
	$back_src = "<img src='$back_button' alt='back' class='nav_buttons' class='nav_buttons'>";
	$next_src = "<img src='$next_button' alt='next' class='nav_buttons' class='nav_buttons'>";
}
else {
	$back_src = "$lang_back";
	$next_src = "$lang_next";
}	

	if ( !file_exists("$path_to_images/$pic_info_file")) {
        $dh = opendir( "$path_to_images" );
        $pic_info = array();
        $time_info = array();
        while( $file = readdir( $dh ) ) {
								// look for these file types....
                if (preg_match('/(jpg|jpeg|gif|png)$/i',$file)) {
                        $time_info[] = filemtime("$path_to_images/$file");
                        $pic_info[] = $file;
                }
        }
        if ($sort_images == 'name') {
            natcasesort($pic_info);
            $pic_info = array_reverse(array_reverse($pic_info)); //resetting array keys
        }
        else {
            $sortorder = $sort_images == "oldest" ? SORT_ASC : SORT_DESC;
            array_multisort($time_info, $sortorder, $pic_info, SORT_ASC, $time_info);
        }
  }
  else {
    $pic_info=file("$path_to_images/$pic_info_file");
  }

// begin messing with the array
$number_pics = count ($pic_info);
if (($currentPic > $number_pics)||($currentPic == $number_pics)||!$currentPic)
	$currentPic = '0';
$item = preg_split('/;/', rtrim($pic_info[$currentPic]), 2);
$last = $number_pics - 1;
$next = $currentPic + 1;
$next_item = @preg_split('/;/', rtrim($pic_info[$next]), 2);
if ($currentPic > 0 ) $back = $currentPic - 1;
else $currentPic = "0";


$blank = empty($item[1])?'&nbsp;':$item[1];

if ($currentPic > 0 ) $nav=$back;
else $nav=$last;
$nav = "<a href='$path?directory=$path_to_images&currentPic=$nav'>$back_src</a>";
$current_show = "$path?directory=$path_to_images";
$next_link = "<a href='$path?directory=$path_to_images&currentPic=$next'>$next_src</a>";
$template = str_replace("<CURRENT_SHOW>",$current_show,$template);
$template = str_replace("<BACK>",$nav,$template);
$template = str_replace("<NEXT>",$next_link,$template);
$template = str_replace("<POSITION>","$next $lang_of $number_pics",$template);


// {{{ ------- EXIF stuff

//get comments from the EXIF data if available...
if(extension_loaded('exif')) {
	$curr_image = "$path_to_images/$item[0]";
	$all_exif = @exif_read_data($curr_image,0,true);
	$exifhtml = @$all_exif['COMPUTED'];
	$comment = @$all_exif['COMMENT'][0];
    if (!empty($comment))  {
        $template = str_replace("<EXIF_COMMENT>",$comment,$template);
    }
}
// }}}

$image_title = isset($item[1]) ? $item[1] : '';
$template = str_replace("<IMAGE_TITLE>",$image_title,$template);

// {{{ ------- my_circular($a_images, $currentPic, $thumb_row);

function my_circular($thumbnail_dir, &$template, $a_images, $currentPic, $thumb_row, $path_to_images) {
global $path;
global $auto_url;

// get size of $a_images array...
$number_pics = count($a_images);
// do a little error checking...
if ($currentPic > $number_pics) $currentPic = 0;
if ($currentPic < 0) $currentPic = 0;
if ($thumb_row < 0) $thumb_row = 1;

// check if thumbnail row is greater than number of images...
if ($thumb_row > $number_pics) $thumb_row = $number_pics;

// split the thumbnail number and make it symmetrical...
$half = floor($thumb_row/2);

// show thumbnails
// left hand thumbs
if (($currentPic - $half) < 0) { // near the start...
    $underage = ($currentPic-1) - $half; 
    for ( $x=($number_pics-abs($underage+1)); $x<$number_pics; $x++) {
        $next=$x;
        $item = preg_split('/;/', rtrim($a_images[$x]), 2);
        $out .= "\n<a href='$path?directory=$path_to_images$auto_url&currentPic=$next' class='thumbnail'><img src='$path_to_images/$thumbnail_dir/".$item[0]."' class='thumbnail'></a>";
    }
    for ( $x=0; $x<$currentPic  ; $x++ ) {
        $next=$x;
        $item = preg_split('/;/', rtrim($a_images[$x]), 2);
        $out .= "\n<a href='$path?directory=$path_to_images$auto_url&currentPic=$next' class='thumbnail'><img src='$path_to_images/$thumbnail_dir/".$item[0]."' class='thumbnail'></a>";
    }
}
else {
    for ( $x=$currentPic-$half; $x < $currentPic; $x++ ) {
        $next=$x;
        $item = preg_split('/;/', rtrim($a_images[$x]), 2);
        $out .= "\n<a href='$path?directory=$path_to_images$auto_url&currentPic=$next' class='thumbnail'><img src='$path_to_images/$thumbnail_dir/".$item[0]."' class='thumbnail'></a>";
    }
}

// show current (center) image thumbnail...
$item = preg_split('/;/', rtrim($a_images[$currentPic]), 2);
$out .= "\n<img src='$path_to_images/$thumbnail_dir/".rtrim($item[0])."' class='thumbnail_center'>";

// array for right side...
if (($currentPic + $half) >= $number_pics) { // near the end
    $overage = (($currentPic + $half) - $number_pics);
    for ( $x=$currentPic+1; $x < $number_pics; $x++) {
        $next=$x;
        $item = preg_split('/;/', rtrim($a_images[$x]), 2);
        $out .= "\n<a href='$path?directory=$path_to_images$auto_url&currentPic=$next' class='thumbnail'><img src='$path_to_images/$thumbnail_dir/".$item[0]."' class='thumbnail'></a>";
    }
    for ( $x=0; $x<=abs($overage); $x++) {
        $next=$x;
        $item = preg_split('/;/', rtrim($a_images[$x]), 2);
        $out .= "\n<a href='$path?directory=$path_to_images$auto_url&currentPic=$next' class='thumbnail'><img src='$path_to_images/$thumbnail_dir/".$item[0]."' class='thumbnail'></a>";
    }
}
else {
    for ( $x=$currentPic+1; $x<=$currentPic+$half; $x++ ) {  // right hand thumbs
        $next=$x;
        $item = preg_split('/;/', rtrim($a_images[$x]), 2);
        $out .= "\n<a href='$path?directory=$path_to_images$auto_url&currentPic=$next' class='thumbnail'><img src='$path_to_images/$thumbnail_dir/".$item[0]."' class='thumbnail'></a>";
    }
}
        $template = str_replace("<THUMBNAIL_ROW>",$out,$template);
}
// }}}
// {{{ ------- my_filmstrip($a_images, $currentPic, $thumb_row);

function my_filmstrip($thumbnail_dir, &$template, $a_images, $currentPic, $thumb_row, $path_to_images) {
    global $path;
    global $auto_url;

    // get size of $a_images array...
    $number_pics = count($a_images);
    // do a little error checking...
    if ($currentPic > $number_pics) $currentPic = 0;
    if ($currentPic < 0) $currentPic = 0;
    if ($thumb_row < 0) $thumb_row = 1;
    if ($thumb_row > $number_pics) $thumb_row = $number_pics;

    if ($currentPic >= $thumb_row) {
        $start = $currentPic - $thumb_row + 1;
    }
    else {
        $start = 0;
    }

    $out = '';
    for ($x = $start; $x < ($start + $thumb_row); $x++ ) {
        $item = preg_split('/;/', rtrim($a_images[$x]), 2);
        $class = $x == $currentPic ? 'thumbnail_center' : 'thumbnail';
        $out .= "\n<a href='$path?directory=$path_to_images$auto_url&currentPic=$x' class='thumbnail'><img src='$path_to_images/$thumbnail_dir/".$item[0]."' class='$class'></a>";
    }

    $template = str_replace("<THUMBNAIL_ROW>",$out,$template);
}
// }}}
// {{{ meta refresh stuff for auto slideshow...
// thanks to tim barmann for the bandwidth saving hack to stop auto slideshow at the last image.
if (($auto == "1") && ($currentPic < $number_pics-1)) {
        $auto_url = "&auto=1";
        $meta_refresh = "<meta http-equiv='refresh' content='".$delay;
        $meta_refresh .= ";url=".$path."?directory=".$path_to_images.$auto_url."&currentPic=".$next."'>";
        $template = str_replace("<META_REFRESH>",$meta_refresh,$template);
        $auto_slideshow = "<a href='$path?directory=$path_to_images&currentPic=$currentPic'>$lang_stop_slideshow</a>\n";
        $template = str_replace("<AUTO_SLIDESHOW_LINK>",$auto_slideshow,$template);
}
else {
        $template = str_replace("<META_REFRESH>","",$template);
        $auto_slideshow = "<a href='$path?directory=$path_to_images&auto=1&currentPic=$next'>$lang_start_slideshow</a>\n";
        $template = str_replace("<AUTO_SLIDESHOW_LINK>",$auto_slideshow,$template);
}
// }}}

$images = "<a href='$path?directory=$path_to_images$auto_url&currentPic=$next'>";
$images .= "<img src='$path_to_images/$item[0]' class='image' alt='$lang_img_alt' title='$lang_img_hover'></a>";
$template = str_replace("<IMAGE>",$images,$template);
$next_image = "$path_to_images/$next_item[0]";
$template = str_replace("<NEXT_IMAGE>",$next_image,$template); // useful for prefetching

if( file_exists( "$path_to_images/$thumbnail_dir" ) ) {
    if( $thumbnail_view == "circular" ) {
        my_circular($thumbnail_dir, $template, $pic_info, $currentPic, $thumb_row, $path_to_images); 
    }
    else {
        my_filmstrip($thumbnail_dir, $template, $pic_info, $currentPic, $thumb_row, $path_to_images); 
    }
}

$image_filename = "$item[0]";
$template = str_replace("<IMAGE_FILENAME>",$image_filename,$template);

// {{{  ----- create the thumbnails and set the permissions...
// the credit for this litte bit of genius goes to Jon from spiicytuna.org who suggested writing the directory
// and permissions via ftp and sent me some code to prove his point :).
if ($create_thumbnails == "true") {
  $full_www_path = dirname ($path);
  $full_ftp_path = $ftp_web_root."/".$full_www_path;
  $thumbnail = $path_to_images."/".$thumbnail_dir."/".$item[0];
  if (file_exists($thumbnail)) {
     // echo "The thumbnail $thumbnail exists";
  } else {
		// create the thumbnail directory for writing
		create_directory($full_ftp_path, $thumbnail_dir, $path_to_images, $ftp_hostname, $ftp_username, $ftp_password);
		// location of the original image
		$sourcefile = "$path_to_images/$item[0]";
		// output file
		$targetfile = $thumbnail;
		/* Create a new image object (not neccessarily true colour) */
        $source_id = null;
        // prep according to image type
        if (preg_match('/(jpg|jpeg)$/i',$sourcefile)) $source_id = imageCreatefromjpeg("$sourcefile");
        elseif (preg_match('/(png)$/i',$sourcefile)) $source_id = imageCreatefrompng("$sourcefile");
        elseif (preg_match('/(gif)$/i',$sourcefile)) $source_id = imageCreatefromgif("$sourcefile");
        else die("Unknown image file type");
		/* Get the dimensions of the source picture */
        $source_width = imagesx($source_id);
        $source_height = imagesy($source_id);
        // scale or crop during thumbnail resize
        $scale = max($thumbnail_max_width/$source_width, $thumbnail_max_height/$source_height);
        if($scale < 1) {
            $thumbnail_actual_width = floor($scale * $source_width);
            $thumbnail_actual_height = floor($scale * $source_height);
		    $target_id=imagecreatetruecolor($thumbnail_actual_width, $thumbnail_actual_height);
            if(function_exists('imagecopyresampled')) {
    		    $target_pic=imagecopyresampled($target_id,$source_id,0,0,0,0,$thumbnail_actual_width,$thumbnail_actual_height,$source_width,$source_height);
            } 
            else {
		        $target_pic=imagecopyresized($target_id,$source_id,0,0,0,0,$thumbnail_actual_width,$thumbnail_actual_height,$source_width,$source_height);
            }
            imagedestroy($source_id);
            $source_id = $target_id;
        }
        if($thumbnail_style == "crop") {
		    $target_id=imagecreatetruecolor($thumbnail_max_width, $thumbnail_max_height);
            if(function_exists('imagecopyresampled')) {
    		    $target_pic=imagecopyresampled($target_id,$source_id,0,0,0,0,$thumbnail_max_width,$thumbnail_max_height,$thumbnail_max_width,$thumbnail_max_height);
            } 
            else {
    		    $target_pic=imagecopyresized($target_id,$source_id,0,0,0,0,$thumbnail_max_width,$thumbnail_max_height,$thumbnail_max_width,$thumbnail_max_height);
            }
            imagedestroy($source_id);
            $source_id = $target_id;
        }
		imagejpeg ($target_id,"$targetfile",$thumbnail_quality); 
  }
}
// {{{ ----- create_directory($full_ftp_path, $thumbnail_dir, $path_to_images, $ftp_hostname, $ftp_username, $ftp_password)
function create_directory($full_ftp_path, $thumbnail_dir, $path_to_images, $ftp_hostname, $ftp_username, $ftp_password) {
 $new_thumbnail_dir = $path_to_images."/".$thumbnail_dir;
 //check to see if the directory is already there....
 if(file_exists($new_thumbnail_dir)){
 	// check permissions for writing
    if (!is_writable($new_thumbnail_dir)) {
        chmod_ftp_directory($full_ftp_path, $thumbnail_dir, $path_to_images, $ftp_hostname, $ftp_username, $ftp_password);
    }
 }
 else{
   $conn_id = ftp_connect($ftp_hostname);
   // login with username and password
   $login_result = ftp_login($conn_id, $ftp_username, $ftp_password);
   // try to chmod $path directory
   if (!ftp_chdir($conn_id, $full_ftp_path)) die("FTP ERROR: ftp_web_root directory not found.");
   if(ftp_mkdir($conn_id, $new_thumbnail_dir)) { 
		ftp_site($conn_id, "CHMOD 777 $new_thumbnail_dir") or die("FTP ERROR: unable to write to server.");
		return true;
   } else {
		return false;   
   }
   // close the FTP connection
   ftp_close($conn_id);
   return true;
 }
}
// }}}
// {{{ ----- function chmod_ftp_directory($full_ftp_path, $thumbnail_dir, $path_to_images, $ftp_hostname, $ftp_username, $ftp_password)
function chmod_ftp_directory($full_ftp_path, $thumbnail_dir, $path_to_images, $ftp_hostname, $ftp_username, $ftp_password) {
    $new_thumbnail_dir = $path_to_images."/".$thumbnail_dir;
    $conn_id = ftp_connect($ftp_hostname);
    // login with username and password
    $login_result = ftp_login($conn_id, $ftp_username, $ftp_password);
    // try to chmod $path directory
    if (!ftp_chdir($conn_id, $full_ftp_path)) die("FTP ERROR: ftp_web_root directory not found.");
    if (!ftp_site($conn_id, "CHMOD 777 $new_thumbnail_dir")) die("FTP ERROR: unable to write to server.");
    // close the FTP connection
    ftp_close($conn_id);
    return true;
}
// }}}
// }}}
echo $template;
?>
