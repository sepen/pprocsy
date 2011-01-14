<html>

<head>

<title>pprocsy</title>

<style type="text/css">

html body
{
  margin-top: 50px;
}

.msgdebug
{
  margin: 1px 0;
  padding: 1px 4px;
  font-size: small;
  color: blue;
  background: white;
  display: block;
  border: 1px solid blue;
}
.msgdebug .item
{
  color: #000;
}

.msgerror
{
  margin: 1px 0;
  padding: 1px 4px;
  font-size: small;
  color: #c40505;
  background: white;
  display: block;
  border: 1px solid #c40505;
}
.msgerror .item
{
  color: #000;
}

.panel
{
  z-index: 100000;
  display: block;
  position: absolute;
  top: 0;
  left: 0;
  height: 28px;
  color: black;
  background: #edeeef;
  padding: 4px 1px 1px 1px;
  border: 1px solid #aaaaaa;
}
.panel input
{
  height: 24px;
  padding: 0 4px;
}
.panel span
{
  margin: 0 0 0 2px;
}
.panel span a
{
  color: black;
  text-decoration: none;
}
.panel .url
{
  color: black;
  border: 1px solid black;
  background: white;
}

.iframearea
{
  width: 100%;
  border: 0;
}

</style>

</head>

<body>

<div class="panel">
<form action="?" method="post">
<span><a href="index.php">procsy</a></span>
<span><input class="url" type="text" name="url" size=40 /></span>
</form>
</div>

<?php

function msgDebug($item, $value)
{
  $debug = false;

  if ($debug)
  {
    $value = htmlspecialchars($value);
    echo "<div class=\"msgdebug\">\n";
    echo "<span>DEBUG</span>\n";
    echo "<span class=\"item\">".$item."</span>\n";
    echo "<span class=\"value\">".$value."</span>\n";
    echo "</div>\n";
  }
}

function msgError($item, $value)
{
  $value = htmlspecialchars($value);
  echo "<div class=\"msgerror\">\n";
  echo "<span>ERROR</span>\n";
  echo "<span class=\"item\">".$item."</span>\n";
  echo "<span class=\"value\">".$value."</span>\n";
  echo "</div>\n";
}

function checkUrl($url)
{
  // discard empty urls
  if (empty($url)) return false;
  // discard urls which doesn't start with http://
  if (substr($url, 0, 7) != "http://") return false;
  // discard urls which could be a potential injection risk
  preg_match("/http:\/\//", substr($url, 7, strlen($url)), $matches, PREG_OFFSET_CAPTURE);
  if (count($matches) > 0) return false;
  // if nothing is wrong then return true
  return true;
}

function filterLine($line)
{
  // TODO: replace relative links for every href="foo/bar/..."
  // replace absolute links for every href="http://...."
  $line = preg_replace("/<a (.*)? href=\"http:\/\/(.*)/i", "<a \\1 href=\"?url=http://\\2", $line);
  $line = preg_replace("/<a (.*)? href='http:\/\/(.*)/i", "<a \\1 href='?url=http://\\2", $line);
  msgDebug("filterLine::line", $line);
  return $line;
}

function printPage($url, $cache_dir)
{
  $html = "";

  msgDebug("printPage::cache_dir", $cache_dir);
  msgDebug("printPage::url", $url);

  $filename = md5($url);

  msgDebug("printPage::filename:", $filename);

  // if file was not cached then get with curl
  if (!file_exists($filename))
  {
    $res = curl_init($url);
    curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
    $ret = curl_exec($res);
    curl_close($res);

    msgDebug("getPage::res", $res);
    msgDebug("getPage::ret", $ret);

    file_put_contents($cache_dir."/".$filename, $ret);
  }

  if ($fp = fopen($cache_dir."/".$filename, 'r'))
  {
    while ($line = fgets($fp))
    {
      msgDebug("getPage::line", $line);

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

if (!is_dir($cache)) msgError("cache dir not found", $cache);
if (!is_writable($cache)) msgError("cache dir not writable", $cache);

$url = "";

if (isset($_POST['url'])) $url = $_POST['url'];
if (isset($_GET['url'])) $url = $_GET['url'];

if (checkUrl($url)) printPage($url, $cache);

?>

</body>
</html>
