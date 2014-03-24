<?php
/*
yourTwapperKeeper - Twitter Archiving Application - http://your.twapperkeeper.com
Copyright (c) 2010 John O'Brien III - http://www.linkedin.com/in/jobrieniii

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Set Important / Load important
session_start();
require_once('config.php');
require_once('function.php');
require_once('twitteroauth.php');


$id = $_GET['id'];
$archiveInfo = $tk->listArchive($id);
if ($archiveInfo['count'] <> 1 || (!(isset($_GET['id'])))) {
	$_SESSION['notice'] = "Archive does not exist.";
	header('Location: index.php');
	}
	
// setup perm urls
$permurl= $tk_your_url."archive.php?".htmlentities($_SERVER['QUERY_STRING']); 
$permrss = $tk_your_url."rss.php?".htmlentities($_SERVER['QUERY_STRING']);
$permexcel = $tk_your_url."excel.php?".htmlentities($_SERVER['QUERY_STRING']);
$permtable = $tk_your_url."table.php?".htmlentities($_SERVER['QUERY_STRING']);
$permjson = $tk_your_url."apiGetTweets.php?".htmlentities($_SERVER['QUERY_STRING']);
$permcsvpipe = $tk_your_url."csv_pipe_delimiter.php?".htmlentities($_SERVER['QUERY_STRING']);

// set default limit
if ($_GET['l'] == '') {$limit = 10;} else {$limit = $_GET['l'];}
if ($_GET['o'] == '') {$orderby = 'd';} else {$orderby = $_GET['o'];} 

// set time limit(s)
if ($_GET['sm'] <> '' && $_GET['sd'] <> '' && $_GET['sy'] <> '') {
    $start_time = strtotime($_GET['sm']."/".$_GET['sd']."/".$_GET['sy']);}
if ($_GET['em'] <> '' && $_GET['ed'] <> '' && $_GET['ey'] <> '') {
    $end_time = strtotime($_GET['em']."/".$_GET['ed']."/".$_GET['ey']);}
    
// Get tweets
if ($start_time <> '' || $end_time <> '') {
$archiveTweets = $tk->getTweets($_GET['id'],$start_time,$end_time,$limit,$orderby,$_GET['nort'],$_GET['from_user'],$_GET['text'],$_GET['lang'],$_GET['max_id'],$_GET['since_id'],$_GET['offset'],$_GET['lat'],$_GET['long'],$_GET['rad'],$_GET['debug']);
} else {
$archiveTweets = $tk->getTweets($_GET['id'],null,null,$limit,$orderby,$_GET['nort'],$_GET['from_user'],$_GET['text'],$_GET['lang'],$_GET['max_id'],$_GET['since_id'],$_GET['offset'],$_GET['lat'],$_GET['long'],$_GET['rad'],$_GET['debug']);
}


// OAuth login check
if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {
$login_status = "<a href='./oauthlogin.php' ><img src='./resources/lighter.png'/></a>";  
$logged_in = FALSE;
} else {
$access_token = $_SESSION['access_token'];
$connection = new TwitterOAuth($tk_oauth_consumer_key, $tk_oauth_consumer_secret, $access_token['oauth_token'], $access_token['oauth_token_secret']);
$login_info = $connection->get('account/verify_credentials');
$login_status = "Hi ".$_SESSION['access_token']['screen_name'].", are you ready to archive?<br><a href='./clearsessions.php'>logout</a>";
$logged_in = TRUE;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<link rel="alternate" type="application/rss+xml" href="<?php echo $permrss; ?>">
<title>Your Twapper Keeper - Archive your own tweets</title>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<link href="resources/css/custom-theme/jquery-ui-1.8.4.custom.css" rel="stylesheet" type="text/css">
<link href="resources/css/yourtwapperkeeper.css" rel="stylesheet" type="text/css">
<script src="resources/js/jquery-1.4.2.min.js"></script>
<script src="resources/js/jquery-ui-1.8.4.custom.min.js"></script>
</head>


<body>

<div id='login'>
<?php echo $login_status; ?> 

<p><a href='index.php'><img src='resources/yourTwapperKeeperLogo.png'/></a></p>
</div> <!-- end login div -->

<div id='header'>

</div> <!-- end header div -->

<div id='main'>
	<?php if (isset($_SESSION['notice'])) { ?>
	<div class='ui-widget'><div class='ui-state-highlight ui-corner-all' style='padding:5px; margin: 5px; width:750px; margin-left:auto; margin-right:auto; text-align:center'><span class='ui-icon ui-icon-info' style='float: left'></span><b><?php echo $_SESSION['notice']; ?></b></div></div>

	<?php
	 unset($_SESSION['notice']);
	 }?> 

<h1><?php echo $archiveInfo['results'][0]['keyword']; ?> - <?php echo $archiveInfo['results'][0]['description']; ?></h1>
<h2>Created on <?php echo date(DATE_RFC2822,$archiveInfo['results'][0]['create_time']); ?> and total number of tweets = <?php echo $archiveInfo['results'][0]['count']; ?></h2>
<h4>tags: <?php echo $archiveInfo['results'][0]['tags']; ?></h4>

<?php
// filter form
$month_num = array(1,2,3,4,5,6,7,8,9,10,11,12);
$month_verbose = array('January','February','March','April','May','June','July','August','September','October','November','December');  
$day = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
$year = array('2009','2010','2011','2012','2013','2014');
$order_values = array('ascending'=>'a','descending'=>'d');
$limit_values = array(10,25,50,250,500,1000,10000,100000,1000000,10000000);
$languageCodes = array(
 "aa" => "Afar",
 "ab" => "Abkhazian",
 "ae" => "Avestan",
 "af" => "Afrikaans",
 "ak" => "Akan",
 "am" => "Amharic",
 "an" => "Aragonese",
 "ar" => "Arabic",
 "as" => "Assamese",
 "av" => "Avaric",
 "ay" => "Aymara",
 "az" => "Azerbaijani",
 "ba" => "Bashkir",
 "be" => "Belarusian",
 "bg" => "Bulgarian",
 "bh" => "Bihari",
 "bi" => "Bislama",
 "bm" => "Bambara",
 "bn" => "Bengali",
 "bo" => "Tibetan",
 "br" => "Breton",
 "bs" => "Bosnian",
 "ca" => "Catalan",
 "ce" => "Chechen",
 "ch" => "Chamorro",
 "co" => "Corsican",
 "cr" => "Cree",
 "cs" => "Czech",
 "cu" => "Church Slavic",
 "cv" => "Chuvash",
 "cy" => "Welsh",
 "da" => "Danish",
 "de" => "German",
 "dv" => "Divehi",
 "dz" => "Dzongkha",
 "ee" => "Ewe",
 "el" => "Greek",
 "en" => "English",
 "eo" => "Esperanto",
 "es" => "Spanish",
 "et" => "Estonian",
 "eu" => "Basque",
 "fa" => "Persian",
 "ff" => "Fulah",
 "fi" => "Finnish",
 "fj" => "Fijian",
 "fo" => "Faroese",
 "fr" => "French",
 "fy" => "Western Frisian",
 "ga" => "Irish",
 "gd" => "Scottish Gaelic",
 "gl" => "Galician",
 "gn" => "Guarani",
 "gu" => "Gujarati",
 "gv" => "Manx",
 "ha" => "Hausa",
 "he" => "Hebrew",
 "hi" => "Hindi",
 "ho" => "Hiri Motu",
 "hr" => "Croatian",
 "ht" => "Haitian",
 "hu" => "Hungarian",
 "hy" => "Armenian",
 "hz" => "Herero",
 "ia" => "Interlingua ",
 "id" => "Indonesian",
 "ie" => "Interlingue",
 "ig" => "Igbo",
 "ii" => "Sichuan Yi",
 "ik" => "Inupiaq",
 "io" => "Ido",
 "is" => "Icelandic",
 "it" => "Italian",
 "iu" => "Inuktitut",
 "ja" => "Japanese",
 "jv" => "Javanese",
 "ka" => "Georgian",
 "kg" => "Kongo",
 "ki" => "Kikuyu",
 "kj" => "Kwanyama",
 "kk" => "Kazakh",
 "kl" => "Kalaallisut",
 "km" => "Khmer",
 "kn" => "Kannada",
 "ko" => "Korean",
 "kr" => "Kanuri",
 "ks" => "Kashmiri",
 "ku" => "Kurdish",
 "kv" => "Komi",
 "kw" => "Cornish",
 "ky" => "Kirghiz",
 "la" => "Latin",
 "lb" => "Luxembourgish",
 "lg" => "Ganda",
 "li" => "Limburgish",
 "ln" => "Lingala",
 "lo" => "Lao",
 "lt" => "Lithuanian",
 "lu" => "Luba-Katanga",
 "lv" => "Latvian",
 "mg" => "Malagasy",
 "mh" => "Marshallese",
 "mi" => "Maori",
 "mk" => "Macedonian",
 "ml" => "Malayalam",
 "mn" => "Mongolian",
 "mr" => "Marathi",
 "ms" => "Malay",
 "mt" => "Maltese",
 "my" => "Burmese",
 "na" => "Nauru",
 "nb" => "Norwegian Bokmal",
 "nd" => "North Ndebele",
 "ne" => "Nepali",
 "ng" => "Ndonga",
 "nl" => "Dutch",
 "nn" => "Norwegian Nynorsk",
 "no" => "Norwegian",
 "nr" => "South Ndebele",
 "nv" => "Navajo",
 "ny" => "Chichewa",
 "oc" => "Occitan",
 "oj" => "Ojibwa",
 "om" => "Oromo",
 "or" => "Oriya",
 "os" => "Ossetian",
 "pa" => "Panjabi",
 "pi" => "Pali",
 "pl" => "Polish",
 "ps" => "Pashto",
 "pt" => "Portuguese",
 "qu" => "Quechua",
 "rm" => "Raeto-Romance",
 "rn" => "Kirundi",
 "ro" => "Romanian",
 "ru" => "Russian",
 "rw" => "Kinyarwanda",
 "sa" => "Sanskrit",
 "sc" => "Sardinian",
 "sd" => "Sindhi",
 "se" => "Northern Sami",
 "sg" => "Sango",
 "si" => "Sinhala",
 "sk" => "Slovak",
 "sl" => "Slovenian",
 "sm" => "Samoan",
 "sn" => "Shona",
 "so" => "Somali",
 "sq" => "Albanian",
 "sr" => "Serbian",
 "ss" => "Swati",
 "st" => "Southern Sotho",
 "su" => "Sundanese",
 "sv" => "Swedish",
 "sw" => "Swahili",
 "ta" => "Tamil",
 "te" => "Telugu",
 "tg" => "Tajik",
 "th" => "Thai",
 "ti" => "Tigrinya",
 "tk" => "Turkmen",
 "tl" => "Tagalog",
 "tn" => "Tswana",
 "to" => "Tonga",
 "tr" => "Turkish",
 "ts" => "Tsonga",
 "tt" => "Tatar",
 "tw" => "Twi",
 "ty" => "Tahitian",
 "ug" => "Uighur",
 "uk" => "Ukrainian",
 "ur" => "Urdu",
 "uz" => "Uzbek",
 "ve" => "Venda",
 "vi" => "Vietnamese",
 "vo" => "Volapuk",
 "wa" => "Walloon",
 "wo" => "Wolof",
 "xh" => "Xhosa",
 "yi" => "Yiddish",
 "yo" => "Yoruba",
 "za" => "Zhuang",
 "zh" => "Chinese",
 "zu" => "Zulu"
);


?>

<style>
    .form_query {
        padding: 30px 0;
        border-bottom: 1px solid #333;
    }
</style>

<div class="form_query download">
    <h3>Download CSV with custom query</h3>
    <form method='get' action='csv_pipe_delimiter.php'>
        <input type='hidden' name='id' value='<?php echo $id; ?>'>
        <table>
        <tr>
        <td><b>START DATE</b></td><td></td><td></td><td></td><td><b>END DATE</b></td><td></td><td></td><td><b>ORDER</b></td><td><b>VIEW LIMIT</b></td><td><b>FROM USER</b></td><td><b>TWEET TEXT</b></td><td><b>LANGUAGE</b></td>

        <td></td>
        </tr>

        <tr>
        <td>
        <SELECT NAME="sm">
        <OPTION value=''>
        <?php
        foreach ($month_num as $value) {
            echo "<OPTION value='$value'";
            if ($value == $_GET['sm']) {echo " SELECTED";}
            echo ">".$month_verbose[$value-1];
        }
        ?>
        </SELECT>                                                  
        </td>

        <td>
        <SELECT NAME="sd">
        <OPTION value=''>
        <?php
        foreach ($day as $value) {
            echo "<OPTION";
            if ($value == $_GET['sd']) {echo " SELECTED";}
            echo ">$value";
        }
        ?>

        </SELECT>
        </td>

        <td>                                                                                                                
        <SELECT NAME="sy">
        <OPTION value=''>
        <?php
        foreach ($year as $value) {
            echo "<OPTION";
            if ($value == $_GET['sy']) {echo " SELECTED";}
            echo ">$value";
        }
        ?>
        </SELECT>
        </td>

        <td></td>

        <td>
        <SELECT NAME="em">
        <OPTION value=''>
        <?php
        foreach ($month_num as $value) {
            echo "<OPTION value='$value'";
            if ($value == $_GET['em']) {echo " SELECTED";}
            echo ">".$month_verbose[$value-1];
        }
        ?>
        </SELECT>
        </td>

        <td>
        <SELECT NAME="ed">
        <OPTION value=''>
        <?php
        foreach ($day as $value) {
            echo "<OPTION";
            if ($value == $_GET['ed']) {echo " SELECTED";}
            echo ">$value";
        }
        ?>

        </SELECT>
        </td>

        <td>
        <SELECT NAME="ey">
        <OPTION value=''>
        <?php
        foreach ($year as $value) {
            echo "<OPTION";
            if ($value == $_GET['ey']) {echo " SELECTED";}
            echo ">$value";
        }
        ?>

        </SELECT>
        </td>

        <td>
        <SELECT NAME="o">
        <OPTION value=''>
        <?php
        foreach ($order_values as $key=>$value) {
            echo "<OPTION value='$value'";
            if ($value == $_GET['o']) {echo " SELECTED";}
            echo ">$key";
        }
        ?>
        </SELECT>
        </td>

        <td>
        <SELECT NAME="l">
        <OPTION value=''>
        <?php
        foreach ($limit_values as $value) {
            echo "<OPTION value='$value'";
            if ($value == $limit) {echo " SELECTED";}
            echo ">$value";
        }
        ?>
        </SELECT>
        </td>

        <?php
        echo "<td>";
        echo "<input name='from_user' value ='".$_GET['from_user']."'/>";
        echo "</td>";
        ?>

        <td>
        <input name='text' value='<?php echo $_GET['text']; ?>'/>
        </td>

        <td>
        <SELECT NAME='lang'>
        <OPTION value=''>
        <?php
        foreach ($languageCodes as $key=>$value) {
            echo "<OPTION value='$key'";
            if ($key == $_GET['lang']) {echo " SELECTED";}
            echo ">$value ($key)";
        }
        ?>
        </SELECT>
        </td>

        <td>
        <input type="checkbox" name="nort" value="1"
        <?php if ($_GET['nort'] == 1) {echo " checked";}?>
        />remove RTs
        </td>

        </tr>
        </table>

        <br><input type='submit' value='download'/>  

    </form>
</div>



<div class="form_query view">
    <h3>Filter display</h3>
    <form method='get' action='archive.php'>
    <input type='hidden' name='id' value='<?php echo $id; ?>'>
    <table>
    <tr>
    <td><b>START DATE</b></td><td></td><td></td><td></td><td><b>END DATE</b></td><td></td><td></td><td><b>ORDER</b></td><td><b>VIEW LIMIT</b></td><td><b>FROM USER</b></td><td><b>TWEET TEXT</b></td><td><b>LANGUAGE</b></td>

    <td></td>
    </tr>

    <tr>
    <td>
    <SELECT NAME="sm">
    <OPTION value=''>
    <?php
    foreach ($month_num as $value) {
        echo "<OPTION value='$value'";
        if ($value == $_GET['sm']) {echo " SELECTED";}
        echo ">".$month_verbose[$value-1];
    }
    ?>
    </SELECT>                                                  
    </td>

    <td>
    <SELECT NAME="sd">
    <OPTION value=''>
    <?php
    foreach ($day as $value) {
        echo "<OPTION";
        if ($value == $_GET['sd']) {echo " SELECTED";}
        echo ">$value";
    }
    ?>

    </SELECT>
    </td>

    <td>                                                                                                                
    <SELECT NAME="sy">
    <OPTION value=''>
    <?php
    foreach ($year as $value) {
        echo "<OPTION";
        if ($value == $_GET['sy']) {echo " SELECTED";}
        echo ">$value";
    }
    ?>
    </SELECT>
    </td>

    <td></td>

    <td>
    <SELECT NAME="em">
    <OPTION value=''>
    <?php
    foreach ($month_num as $value) {
        echo "<OPTION value='$value'";
        if ($value == $_GET['em']) {echo " SELECTED";}
        echo ">".$month_verbose[$value-1];
    }
    ?>
    </SELECT>
    </td>

    <td>
    <SELECT NAME="ed">
    <OPTION value=''>
    <?php
    foreach ($day as $value) {
        echo "<OPTION";
        if ($value == $_GET['ed']) {echo " SELECTED";}
        echo ">$value";
    }
    ?>

    </SELECT>
    </td>

    <td>
    <SELECT NAME="ey">
    <OPTION value=''>
    <?php
    foreach ($year as $value) {
        echo "<OPTION";
        if ($value == $_GET['ey']) {echo " SELECTED";}
        echo ">$value";
    }
    ?>

    </SELECT>
    </td>

    <td>
    <SELECT NAME="o">
    <OPTION value=''>
    <?php
    foreach ($order_values as $key=>$value) {
        echo "<OPTION value='$value'";
        if ($value == $_GET['o']) {echo " SELECTED";}
        echo ">$key";
    }
    ?>
    </SELECT>
    </td>

    <td>
    <SELECT NAME="l">
    <OPTION value=''>
    <?php
    foreach ($limit_values as $value) {
        echo "<OPTION value='$value'";
        if ($value == $limit) {echo " SELECTED";}
        echo ">$value";
    }
    ?>
    </SELECT>
    </td>

    <?php
    echo "<td>";
    echo "<input name='from_user' value ='".$_GET['from_user']."'/>";
    echo "</td>";
    ?>

    <td>
    <input name='text' value='<?php echo $_GET['text']; ?>'/>
    </td>

    <td>
    <SELECT NAME='lang'>
    <OPTION value=''>
    <?php
    foreach ($languageCodes as $key=>$value) {
        echo "<OPTION value='$key'";
        if ($key == $_GET['lang']) {echo " SELECTED";}
        echo ">$value ($key)";
    }
    ?>
    </SELECT>
    </td>

    <td>
    <input type="checkbox" name="nort" value="1"
    <?php if ($_GET['nort'] == 1) {echo " checked";}?>
    />remove RTs
    </td>

    </tr>
    </table>

    <br><input type='submit' value='query'/>  

    </form>


    <br><br>
    <?php 
              
    echo "HTML Permalink = <a href='$permurl'>$permurl</a><br>";
    echo "RSS Permalink = <a href='$permrss'>$permrss</a><br>";
    echo "Excel Permalink = <a href='$permexcel'>$permexcel</a><br>";
    echo "Simple Table Permalink = <a href='$permtable'>$permtable</a><br>";
    echo "JSON API = <a href='$permjson'>$permjson</a><br>";
    echo "CSV PIPE DELIMITER = <a href='$permcsvpipe'>$permcsvpipe</a>";
    echo "</h5>";
    ?>

</div>

<div style='text-align:left; margin-left:auto; margin-right:auto; width:1024px; padding-top:15px; padding-bottom:15px'>

<?php        
		$tw_count = 0;
        
        foreach ($archiveTweets as $row) {
            $tw_count = $tw_count + 1;
            echo "<div style='margin-bottom:5px'>";
            echo "<div style='float:left; margin-right:5px'><img src='".$row['profile_image_url']."' height='40px'/></div>";
            echo "<div style='float:left; width:950px'>";
            $text = preg_replace('@(http://([\w-.]+)+(:\d+)?(/([\w/_.]*(\?\S+)?)?)?)@', '<a href="$1" target="_blank">$1</a>', $row['text']);
            $matches = array();
            preg_match('@(http://([\w-.]+)+(:\d+)?(/([\w/_.]*(\?\S+)?)?)?)@',$row['text'],$matches);
            $text = preg_replace("/#(\w+)/", "<a href=\"http://search.twitter.com/search?q=\\1\" target=\"_blank\">#\\1</a>", $text);

            echo "<b>@".$row['from_user']."</b> ".$text."<br><br>";
            echo "<font style='font-weight:lighter; font-size:8px'><i>".$row['created_at']." - tweet id <a name='tweetid-".$row['id']."'>".$row['id']."</a> - #$tw_count</i></font>";
            echo "<br>";  
            if ($row['geo_type'] <> '') {
                echo "<font style='font-weight:lighter; font-size:8px'><i>geo info: ".$row['geo_type']." - lat = ".$row['geo_coordinates_0']." - long = ".$row['geo_coordinates_1']."</i></font><br>";}
             
            
            echo "</div>";                                        
            echo "</div>";
            echo "<div style='clear:both; margin-bottom:10px; margin-top:10px; border-bottom:1px dotted #333333'><br></div>";
        }
?>
</div>

<div id='footer'>
<p>Your TwapperKeeper - <?php echo $yourtwapperkeeper_version; ?></p>
</div>





</body>
</html>
