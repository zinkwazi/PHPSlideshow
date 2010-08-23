PHPSlideShow by Greg Lawler
download the latest version from http://www.zinkwazi.com/scripts

PHPSlideshow is relesed under the GPL
See the license at http://www.gnu.org/licenses/gpl.txt
Feel free to use/modify this little script

IMPORTANT NOTE.... ;)
if you want to send me a token of appreciation, i like coffee so
anything from http://www.starbucks.com/card will be gladly accepted ;)
my address is:
attention: Greg Lawler
27 East Cota St., Santa Barbara, CA 93101 

DEFAULT INSTALL:
Download PHPSlideshow.zip from http://www.zinkwazi.com/scripts and
unzip the contents into a directory of images.
That's it, your slideshow is ready, simply navigate to the phpslideshow.php
script in your browser.

NOTE: Be sure that you are loading phpslideshow.php in your browser and 
NOT the template.html file (you'll see a strange broken page if you do this)

Rename phpslideshow.php to index.php if you wish to load the slideshow by
default in a directory e.g. http://example.com/dogs vs
http://example.com/dogs/phpslideshow.php

CUSTOM INSTALL:
If you wish to modify the default install, continue reading...

Q: How do I get thumbnails created automatically?
A: 1) You need to input your web site FTP username and password in the phpslidesho.php file.
   2) You ALSO need to tell PHPSlideshow where your web site is located once you log into FTP.
      This is the directory that your web site is uploaded to and is usually one of the following:
	public_html, wwwroot or httpdocs.
   3) Finally, change $create_thumbnails to be = "true";
   NOTE: Thumbnails are created the first time you view the slideshow.

Q: How do I make PHPSlideshow start in AUTO slideshow mode like in the demo?
A: To have the slideshow start automatically, simply append the following
    to your link:
        &auto=1
    so if your slideshow is at
    http://www.yoursite.com/phpslideshow.php?directory=photos
    the link URL with auto turned on would be:
    http://www.yoursite.com/scripts/phpslideshow.php?directory=photos&auto=1
    placing the <AUTO_SLIDESHOW_LINK> in your template will give the user
    the option to toggle the slideshow on and off.

Q: How do I customize the look and feel (template) of my PHPSlideshow installation?
A: There is a template.html which is the file you edit to change the layout, colors etc of your slide show.
    There are a number of "tags" that you can use in the template.html file to customize your PHPslideshow.
    See the list at the end of the README.txt

Q: How do I change the number of thumbnails displayed.
A: Edit the $thumb_row variable in the phpslideshow.php file.
    e.g. If you wanted 30 thumbnails, you would set $thumb_row as follows:
    $thumb_row = 30;

Q: How do I change how the order in which images are displayed (sorted?)
A: Edit the $sort_images variable in the phpslideshow.php file.
    Note: If you use a pics.txt file $sort_images has no effect.
    e.g. If you wanted to display the images sorted alphabetically:
    $sort_images = "name"; 

Q: How do I tell PHPSlideshow where to look for my thumbnail images?
A: Edit the $thumbnail_dir variable in the phpslideshow.php file.
    e.g. If your thumbnails are in a folder called "thumbnails":
    $thumbnail_dir = "thumbnails";

Q: How do I give each directory of images it's own page heading?
A: Place a text file called heading.txt in the images  directory with the page heading
    on the first line of this file.

Q: Are there any other config options?
A: Take a look in the CONFIG SECTION in the PHPSlideshow.php file :)

Q: How do I add image comments/descriptions to the slideshow?
A: There are two ways to do this, 
  1) add an EXIF comment to each image.
     A google search will return many free tools that enable you to edit the EXIF comments section of a JPEG. 
     Use the <EXIF_COMMENT> in the template.html file to display this data.
  2) Create a pics.txt file.
     Create a text file that lists each image name and description on a new line separated by a semi colon.
     for example each line would look like this: 
         my_house.JPG;This is my house.
     NO BLANK LINES!

     A quick way to generate a pics.txt file with image names is to use the command prompt.
     ls *.jpg > pics.txt in linux or OS X
     dir /b *.jpg > pics.txt at a dos prompt in windows
     You will need edit this in a text editor to add the semi colon and
     desription.

Q: How do I use my current template HTML file with PHPSlideshow?
A: Copy your template HTML into a file called template.html and add any of the available PHPSlideshow tags.

Following is the list of availabls tags:
   <SHOW_TITLE> : the slideshow title from the heading.txt file.
   <CURRENT_SHOW> : displays path to the current slideshow.
   <BACK> : navigation button to go back one image.
   <NEXT> : navigation button to go forward one image.
   <POSITION> : displays position in the slideshow e.g. "2 of 6" 
   <EXIF_COMMENT> : information from the EXIF Comment field if it exists.
   <IMAGE_TITLE> : the image title if you used a pics.txt file.
   <THUMBNAIL_ROW> : output the thumbnails if the "thumbnails" dir exists
   <META_REFRESH> : this needs to go on the <head> section of your template
   <AUTO_SLIDESHOW_LINK> : displays start and stop slideshow link (SEE <META_REFRESH>)
   <IMAGE> : displays the current image
   <NEXT_IMAGE> : Provides the next image name, used for preloading images.
   <IMAGE_FILENAME> : displays the image file name

Q: What are the CSS classes used to modify the thumbnail row images?
A: The following classes are defined around the thumbnails:
   class='thumbnail_center' : allows to you customize the active image's thumbnail.
   class='thumbnail' : affect all thumbnail images except the center one

Q: How do I use one slideshow for multiple different directories of images?
A: Load the phpslideshow.php script in your browser and pass it the directory path.
    e.g.:
    If you have a directory called pictures_directory that contains your 
    phpslideshow.php and two sub-directories containing pictures of your pets...
    pictures_directory -> phpslideshow.php
                       -> dog_pics
                       -> cat_pics
    in order to access the shows:
    http://yourserver.com/pictures_directory/phpslideshow.php?directory=dog_pics
    http://yourserver.com/pictures_directory/phpslideshow.php?directory=cat_pics

    if there were images in the pictures_directory, you'd see them like this:
    http://yourserver.com/pictures_directory/phpslideshow.php

Q: Can I use JavaScript to preload the next image to speed my customized template up a bit?
A: Yes, put the following script in the <head> section of your template.html file:
    <SCRIPT language="JavaScript">
    <!-- 
    // let's preload the next image...
    if (document.images)
    {
      pic1= new Image(800,600); 
      pic1.src="<NEXT_IMAGE>"; 
    }
    //-->
    </SCRIPT>

Q: Can you give an example pics.txt file?
A: Here is a 4 line example of a pics.txt file:
    greg.jpg;Me
    dog.png;My dog John
    cat;
    tux.jpg;My friend Tux

    (Not all pics need a description)

NOTE: for security, you can only access directories within the same directory as
      the phpslideshow.php script...
      Additionally, you cannot browse the thumbnail directory with phpslideshow.
