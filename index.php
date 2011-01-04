<html>

<head>

<title>pprocsy</title>

<style type="text/css">
html body
{
  margin-top: 50px;
}
.inputform
{
  z-index: 100000;
  display: inline;
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 28px;
  margin: 0 0 2px 0;
  border: 1px solid #c40505;
  font-size: small;
  color: #000000;
  background: #000000;
}
.inputform input
{
  padding: 1px 4px;
  border: 1px solid #757575;
}
.debugmessage
{
  margin: 1px 0;
  padding: 1px 4px;
  font-size: x-small;
  color: #c40505;
  background: white;
  display: block;
  border: 1px solid #c40505;
}
.debugmessage .item
{
  color: #000;
}
.iframearea
{
  width: 100%;
  border: 0;
}
</style>

</head>

<body>

<form  class="inputform" action="?" method="post">
<input type="submit" value="url" /><input type="text" name="url" size=50 />
</form>

<?php

function debugPrint($item, $value)
{
  $debug = false;

  if ($debug)
  {
    echo "<div class=\"debugmessage\">\n";
    echo "<span class=\"item\">".$item."</span>\n";
    echo "<span class=\"value\">".$value."</span>\n";
    echo "</div>\n";
  }
}

function filterLine($line)
{
  $output = $line;
  return $output;
}

function printPage($url, $cache_dir)
{
  $html = "";

  debugPrint("getPage::cache_dir", $cache_dir);
  debugPrint("getPage::url", $url);

  $filename = md5($url);

  debugPrint("getPage:filename", $filename);

  // if file was not cached then get with curl
  if (!file_exists($filename))
  {
    $res = curl_init($url);
    curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
    $ret = curl_exec($res);
    curl_close($res);

    debugPrint("getPage::res: ", $res);
    debugPrint("getPage::ret: ", $ret);

    file_put_contents($cache_dir."/".$filename, $ret);
  }

  if ($fp = fopen($cache_dir."/".$filename, 'r'))
  {
    while ($line = fgets($fp))
    {
      debugPrint("getPage::line: ", $line);

      $html .= filterLine($line);
    }
    fclose($fp);
  }

  //echo "<iframe class=\"iframearea\" scrolling=\"no\" src=\"".$cache_dir."/".$filename."\" />";
  echo $html;
}

/* ************************************************************************** */
/* main                                                                       */
/* ************************************************************************** */

$cache = "./cache";
$url = "";

if (isset($_POST['url'])) $url = $_POST['url'];
if (isset($_GET['url'])) $url = $_GET['url'];

printPage($url, $cache);

?>

</body>
</html>
