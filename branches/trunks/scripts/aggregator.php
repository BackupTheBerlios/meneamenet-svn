<?php
/* $Id: aggregator.php,v 1.0 2006/03/28 01:22:31 trunks
        Based on aggregator.module from drupal (http://drupal.org/) */

/**
 * @file
 * Used to aggregate syndicated content (RSS and RDF).
 */

include('/var/www/meneame/www/config.php');

aggregator_main();

function aggregator_main() {
  global $db, $dblang;

  $result = $db->get_results('SELECT * FROM categories c JOIN feed_data f ON f.id = c.category_id WHERE c.category_lang="' . $dblang . '"');

  foreach ($result as $category) {
    aggregator_refresh($category);
  }
}

/**
 * Checks a news feed for new items.
 */
function aggregator_refresh($feed) {
  global $channel, $image, $db;

  // Generate conditional GET headers.
  $headers = array();
  if ($feed->etag) {
    $headers['If-None-Match'] = $feed->etag;
  }
  if ($feed->modified) {
    $headers['If-Modified-Since'] = gmdate('D, d M Y H:i:s', $feed->modified) .' GMT';
  }

  // Request feed.
  $result = http_request($feed->category_feed, $headers);

  // Process HTTP response code.
  switch ($result->code) {
    case 304:
      // No new syndicated content from site
      $db->query('UPDATE feed_data SET checked = ' . time() . ' WHERE id = '. $feed->id);
      break;
    case 301:
      // Updated URL for feed
      $feed->category_feed = $result->redirect_url;
      $db->query("UPDATE feed_data SET category_feed='" . $feed->category_feed . "' WHERE category_id=" . $feed->id);
      break;

    case 200:
    case 302:
    case 307:
      // Filter the input data:
     if (aggregator_parse_feed($result->data, $feed)) {

        if ($result->headers['Last-Modified']) {
          $modified = strtotime($result->headers['Last-Modified']);
        }

        /*
        ** Prepare the channel data:
        */

        foreach ($channel as $key => $value) {
          $channel[$key] = trim(strip_tags($value));
        }

        /*
        ** Prepare the image data (if any):
        */

        foreach ($image as $key => $value) {
          $image[$key] = trim($value);
        }

        if ($image['LINK'] && $image['URL'] && $image['TITLE']) {
          $image = '<a href="'. $image['LINK'] .'"><img src="'. $image['URL'] .'" alt="'. $image['TITLE'] .'" /></a>';
        }
        else {
          $image = NULL;
        }

        /*
        ** Update the feed data:
        */

        $db->query("UPDATE feed_data SET checked = ". time() .", link = '". $channel['LINK'] ."', description = '". $channel['DESCRIPTION'] ."', image = '". $image ."', etag = '". $result->headers['ETag'] ."', modified = '". $modified ."' WHERE id = ". $feed->id);

      }
      break;
    default:
      echo 'Failed to parse RSS feed ' . $feed->category_name . ' : ' . $result->code .' '. $result->error . "\n";
  }
}

/**
 * Perform an HTTP request.
 *
 * This is a flexible and powerful HTTP client implementation. Correctly
 * handles
 * GET, POST, PUT or any other HTTP requests. Handles redirects.
 *
 * @param $url
 *   A string containing a fully qualified URI.
 * @param $headers
 *   An array containing an HTTP header => value pair.
 * @param $method
 *   A string defining the HTTP request to use.
 * @param $data
 *   A string containing data to include in the request.
 * @param $retry
 *   An integer representing how many times to retry the request in case of a
 *   redirect.
 * @return
 *   An object containing the HTTP request headers, response code, headers,
 *   data, and redirect status.
 */
function http_request($url, $headers = array(), $method = 'GET', $data = NULL, $retry = 3) {
  $result = new StdClass();

  // Parse the URL, and make sure we can handle the schema.
  $uri = parse_url($url);

  switch ($uri['scheme']) {
    case 'http':
      $fp = @fsockopen($uri['host'], ($uri['port'] ? $uri['port'] : 80), $errno, $errstr, 15);
      break;
    case 'https':
      // Note: Only works for PHP 4.3 compiled with OpenSSL.
      $fp = @fsockopen('ssl://'. $uri['host'], ($uri['port'] ? $uri['port'] : 443), $errno, $errstr, 20);
      break;
    default:
      $result->error = 'invalid schema '. $uri['scheme'];
      return $result;
  }

  // Make sure the socket opened properly.
  if (!$fp) {
    $result->error = trim($errno .' '. $errstr);
    return $result;
  }

  // Construct the path to act on.
  $path = $uri['path'] ? $uri['path'] : '/';
  if ($uri['query']) {
    $path .= '?'. $uri['query'];
  }

  // Create HTTP request.
  $defaults = array(
    'Host' => 'Host: '. $uri['host'],
    'User-Agent' => 'User-Agent: Drupal (+http://www.drupal.org/)',
    'Content-Length' => 'Content-Length: '. strlen($data)
  );

  foreach ($headers as $header => $value) {
    $defaults[$header] = $header .': '. $value;
  }

  $request = $method .' '. $path ." HTTP/1.0\r\n";
  $request .= implode("\r\n", $defaults);
  $request .= "\r\n\r\n";
  if ($data) {
    $request .= $data ."\r\n";
  }
  $result->request = $request;

  fwrite($fp, $request);

  // Fetch response.
  $response = '';
  while (!feof($fp) && $data = fread($fp, 1024)) {
    $response .= $data;
  }
  fclose($fp);

  // Parse response.
  list($headers, $result->data) = explode("\r\n\r\n", $response, 2);
  $headers = preg_split("/\r\n|\n|\r/", $headers);

  list($protocol, $code, $text) = explode(' ', trim(array_shift($headers)), 3);
  $result->headers = array();

  // Parse headers.
  while ($line = trim(array_shift($headers))) {
    list($header, $value) = explode(':', $line, 2);
    $result->headers[$header] = trim($value);
  }

  $responses = array(
    100 => 'Continue', 101 => 'Switching Protocols',
    200 => 'OK', 201 => 'Created', 202 => 'Accepted', 203 => 'Non-Authoritative Information', 204 => 'No Content', 205 => 'Reset Content', 206 => 'Partial Content',
    300 => 'Multiple Choices', 301 => 'Moved Permanently', 302 => 'Found', 303 => 'See Other', 304 => 'Not Modified', 305 => 'Use Proxy', 307 => 'Temporary Redirect',
    400 => 'Bad Request', 401 => 'Unauthorized', 402 => 'Payment Required', 403 => 'Forbidden', 404 => 'Not Found', 405 => 'Method Not Allowed', 406 => 'Not Acceptable', 407 => 'Proxy Authentication Required', 408 => 'Request Time-out', 409 => 'Conflict', 410 => 'Gone', 411 => 'Length Required', 412 => 'Precondition Failed', 413 => 'Request Entity Too Large', 414 => 'Request-URI Too Large', 415 => 'Unsupported Media Type', 416 => 'Requested range not satisfiable', 417 => 'Expectation Failed',
    500 => 'Internal Server Error', 501 => 'Not Implemented', 502 => 'Bad Gateway', 503 => 'Service Unavailable', 504 => 'Gateway Time-out', 505 => 'HTTP Version not supported'
  );
  // RFC 2616 states that all unknown HTTP codes must be treated the same as
  // the base code in their class.
  if (!isset($responses[$code])) {
    $code = floor($code / 100) * 100;
  }

  switch ($code) {
    case 200: // OK
    case 304: // Not modified
      break;
    case 301: // Moved permanently
    case 302: // Moved temporarily
    case 307: // Moved temporarily
      $location = $result->headers['Location'];

      if ($retry) {
        $result = http_request($result->headers['Location'], $headers, $method, $data, --$retry);
        $result->redirect_code = $result->code;
      }
      $result->redirect_url = $location;

      break;
    default:
      $result->error = $text;
  }

  $result->code = $code;
  return $result;
}

/**
 * Decode all HTML entities (including numerical ones) to regular UTF-8 bytes.
 * Double-escaped entities will only be decoded once ("&amp;lt;" becomes
 * "&lt;", not "<").
 *
 * @param $text
 *   The text to decode entities in.
 * @param $exclude
 *   An array of characters which should not be decoded. For example,
 *   array('<', '&', '"'). This affects both named and numerical entities.
 */
function decode_entities($text, $exclude = array()) {
  static $table;
  // We store named entities in a table for quick processing.
  if (!isset($table)) {
    // Get all named HTML entities.
    $table = array_flip(get_html_translation_table(HTML_ENTITIES));
    // PHP gives us ISO-8859-1 data, we need UTF-8.
    $table = array_map('utf8_encode', $table);
    // Add apostrophe (XML)
    $table['&apos;'] = "'";
  }
  $newtable = array_diff($table, $exclude);

  // Use a regexp to select all entities in one pass, to avoid decoding
  // double-escaped entities twice.
  return preg_replace('/&(#x?)?([A-Za-z0-9]+);/e', '_decode_entities("$1", "$2", "$0", $newtable, $exclude)', $text);
}

/**
 * Helper function for decode_entities
 */
function _decode_entities($prefix, $codepoint, $original, &$table, &$exclude)
{
  // Named entity
  if (!$prefix) {
    if (isset($table[$original])) {
      return $table[$original];
    }
    else {
      return $original;
    }
  }
  // Hexadecimal numerical entity
  if ($prefix == '#x') {
    $codepoint = base_convert($codepoint, 16, 10);
  }
  else {
    // Decimal numerical entity (strip leading zeros to avoid PHP octal
    // notation)
    $codepoint = preg_replace('/^0+/', '', $codepoint);
  }
  // Encode codepoint as UTF-8 bytes
  if ($codepoint < 0x80) {
    $str = chr($codepoint);
  }
  else if ($codepoint < 0x800) {
    $str = chr(0xC0 | ($codepoint >> 6))
         . chr(0x80 | ($codepoint & 0x3F));
  }
  else if ($codepoint < 0x10000) {
    $str = chr(0xE0 | ( $codepoint >> 12))
         . chr(0x80 | (($codepoint >> 6) & 0x3F))
         . chr(0x80 | ( $codepoint       & 0x3F));
  }
  else if ($codepoint < 0x200000) {
    $str = chr(0xF0 | ( $codepoint >> 18))
         . chr(0x80 | (($codepoint >> 12) & 0x3F))
         . chr(0x80 | (($codepoint >> 6)  & 0x3F))
         . chr(0x80 | ( $codepoint        & 0x3F));
  }
  // Check for excluded characters
  if (in_array($str, $exclude)) {
    return $original;
  }
  else {
    return $str;
  }
}

function aggregator_parse_feed(&$data, $feed) {
  global $items, $image, $channel, $db;

  // Unset the global variables before we use them:
  unset($GLOBALS['element'], $GLOBALS['item'], $GLOBALS['tag']);
  $items = array();
  $image = array();
  $channel = array();

  // parse the data:
  $xml_parser = meneame_xml_parser_create($data);
  xml_set_element_handler($xml_parser, 'aggregator_element_start', 'aggregator_element_end');
  xml_set_character_data_handler($xml_parser, 'aggregator_element_data');

  if (!xml_parse($xml_parser, $data, 1)) {
    echo 'Failed to parse RSS feed ' . $feed->category_name . ' : ' . xml_error_string(xml_get_error_code($xml_parser)) .' at line ' . xml_get_current_line_number($xml_parser) . "\n";
    return 0;
  }
  xml_parser_free($xml_parser);

  /*
  ** We reverse the array such that we store the first item last,
  ** and the last item first.  In the database, the newest item
  ** should be at the top.
  */

  $items = array_reverse($items);

  foreach ($items as $item) {
    unset($title, $link, $author, $description);

    // Prepare the item:
    foreach ($item as $key => $value) {
      // TODO: Make handling of aggregated HTML more flexible/configurable.
      $value = decode_entities(trim($value));
      if ($key != 'LINK' && $key != 'GUID') {
        //$value = filter_xss($value);
        ereg_replace("'","\'", $value);
      }
      $item[$key] = $value;
    }

    /*
    ** Resolve the item's title.  If no title is found, we use
    ** up to 40 characters of the description ending at a word
    ** boundary but not splitting potential entities.
    */

    if ($item['TITLE']) {
      $title = $item['TITLE'];
    }
    else {
      $title = preg_replace('/^(.*)[^\w;&].*?$/', "\\1", truncate_utf8($item['DESCRIPTION'], 40));
    }

    /*
    ** Resolve the items link.
    */

    if ($item['LINK']) {
      $link = $item['LINK'];
    }
    elseif ($item['GUID'] && (strncmp($item['GUID'], 'http://', 7) == 0)) {
      $link = $item['GUID'];
    }
    else {
      $link = $feed->link;
    }

    /*
    ** Try to resolve and parse the item's publication date.  If no
    ** date is found, we use the current date instead.
    */

    if ($item['PUBDATE']) $date = $item['PUBDATE'];                        // RSS 2.0
    else if ($item['DC:DATE']) $date = $item['DC:DATE'];                   // Dublin core
    else if ($item['DCTERMS:ISSUED']) $date = $item['DCTERMS:ISSUED'];     // Dublin core
    else if ($item['DCTERMS:CREATED']) $date = $item['DCTERMS:CREATED'];   // Dublin core
    else if ($item['DCTERMS:MODIFIED']) $date = $item['DCTERMS:MODIFIED']; // Dublin core
    else $date = 'now';

    $timestamp = strtotime($date); // strtotime() returns -1 on failure
    if ($timestamp < 0) {
      $timestamp = aggregator_parse_w3cdtf($date); // also returns -1 on failure
      if ($timestamp < 0) {
        $timestamp = time(); // better than nothing
      }
    }

    /*
    ** Save this item.  Try to avoid duplicate entries as much as
    ** possible.  If we find a duplicate entry, we resolve it and
    ** pass along it's ID such that we can update it if needed.
    */

    if ($link && $link != $feed->link && $link != $feed->category_feed) {
      $entry = $db->get_results("SELECT link_id FROM links WHERE link_category = ". $feed->category_id ." AND link_url = '". $link ."'");
    }
    else {
      $entry = $db->get_results("SELECT link_id FROM links WHERE link_category = ". $feed->category_id ." AND link_title = '". $title ."'");
    }

    aggregator_save_item(array('link_id' => $entry[0]->link_id, 'link_category' => $feed->category_id, 'link_modified' => $timestamp, 'link_title' => $title, 'link_url' => $link, 'link_author' => 1, 'link_content' => $item['DESCRIPTION'], 'category_name' => $feed->category_name, 'tags' => $feed->tags));
  }

  return 1;
}

function aggregator_save_item($edit) {
  global $db;
  if ($edit['link_id'] && $edit['link_title']) {
    $db->query("UPDATE links SET link_title = '". $edit['link_title'] ."', link_url = '". $edit['link_url'] ."', link_author = '". $edit['link_author'] ."', link_content = '". $edit['link_content'] ."' WHERE link_id = " . $edit['link_id']);
  }
  else if ($edit['id']) {
    $db->query('DELETE FROM links WHERE link_id = ' . $edit['link_id']);
  }
  else if ($edit['link_title'] && $edit['link_url']) { 
    $db->query("INSERT INTO links (link_category, link_title, link_url_title, link_url, link_status, link_author, link_blog, link_content, link_date, link_tags) VALUES (". $edit['link_category'] .", '". $edit['link_title'] ."', '". $edit['link_title'] ."', '". $edit['link_url'] ."', 'queued', '". $edit['link_author'] ."', '1', '". $edit['link_content'] . " ', from_unixtime('". $edit['link_modified'] ."'), '". $edit['tags']."')");
  }
}

/**
 * Parse the W3C date/time format, a subset of ISO 8601. PHP date parsing
 * functions do not handle this format.
 * See http://www.w3.org/TR/NOTE-datetime for more information.
 * Originally from MagpieRSS (http://magpierss.sourceforge.net/).
 *
 * @param $date_str A string with a potentially W3C DTF date.
 * @return A timestamp if parsed successfully or -1 if not.
 */
function aggregator_parse_w3cdtf($date_str) {
  if (preg_match('/(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(:(\d{2}))?(?:([-+])(\d{2}):?(\d{2})|(Z))?/', $date_str, $match)) {
    list($year, $month, $day, $hours, $minutes, $seconds) = array($match[1], $match[2], $match[3], $match[4], $match[5], $match[6]);
    // calc epoch for current date assuming GMT
    $epoch = gmmktime($hours, $minutes, $seconds, $month, $day, $year);
    if ($match[10] != 'Z') { // Z is zulu time, aka GMT
      list($tz_mod, $tz_hour, $tz_min) = array($match[8], $match[9], $match[10]);
      // zero out the variables
      if (!$tz_hour) {
        $tz_hour = 0;
      }
      if (!$tz_min) {
        $tz_min = 0;
      }
      $offset_secs = (($tz_hour * 60) + $tz_min) * 60;
      // is timezone ahead of GMT?  then subtract offset
      if ($tz_mod == '+') {
        $offset_secs *= -1;
      }
      $epoch += $offset_secs;
    }
    return $epoch;
  }
  else {
    return -1;
  }
}

/**
 * Call-back function used by the XML parser.
 */
function aggregator_element_start($parser, $name, $attributes) {
  global $item, $element, $tag;

  switch ($name) {
    case 'IMAGE':
    case 'TEXTINPUT':
      $element = $name;
      break;
    case 'ITEM':
      $element = $name;
      $item += 1;
  }

  $tag = $name;
}

/**
 * Call-back function used by the XML parser.
 */
function aggregator_element_end($parser, $name) {
  global $element;

  switch ($name) {
    case 'IMAGE':
    case 'TEXTINPUT':
    case 'ITEM':
      $element = '';
  }
}

/**
 * Call-back function used by the XML parser.
 */
function aggregator_element_data($parser, $data) {
  global $channel, $element, $items, $item, $image, $tag;

  switch ($element) {
    case 'ITEM':
      $items[$item][$tag] .= $data;
      break;
    case 'IMAGE':
      $image[$tag] .= $data;
      break;
    case 'TEXTINPUT':
      // The sub-element is not supported. However, we must recognize
      // it or its contents will end up in the item array.
      break;
    default:
      $channel[$tag] .= $data;
  }
}

/**
 * Prepare a new XML parser.
 *
 * This is a wrapper around xml_parser_create() which extracts the encoding
 * from
 * the XML data first and sets the output encoding to UTF-8. This function
 * should
 * be used instead of xml_parser_create(), because PHP's XML parser doesn't
 * check
 * the input encoding itself.
 *
 * This is also where unsupported encodings will be converted.
 * Callers should take this into account: $data might have been changed after
 * the call.
 *
 * @param &$data
 *   The XML data which will be parsed later.
 * @return
 *   An XML parser object.
 */
function meneame_xml_parser_create(&$data) {
  // Default XML encoding is UTF-8
  $encoding = 'utf-8';
  $bom = false;

  // Check for UTF-8 byte order mark (PHP5's XML parser doesn't handle it).
  if (!strncmp($data, "\xEF\xBB\xBF", 3)) {
    $bom = true;
    $data = substr($data, 3);
  }

  // Check for an encoding declaration in the XML prolog if no BOM was found.
  if (!$bom && ereg('^<\?xml[^>]+encoding="([^"]+)"', $data, $match)) {
    $encoding = $match[1];
  }

  // Unsupported encodings are converted here into UTF-8.
  $php_supported = array('utf-8', 'iso-8859-1', 'us-ascii');
  if (!in_array(strtolower($encoding), $php_supported)) {
    $out = convert_to_utf8($data, $encoding);
    if ($out !== false) {
      $data = $out;
      $encoding = 'utf-8';
    }
    else {
       /* watchdog('php', t("Could not convert XML encoding '%s' to
UTF-8.", array('%s' => $encoding)), WATCHDOG_WARNING); */
      return 0;
    }
  }

  $xml_parser = xml_parser_create($encoding);
  xml_parser_set_option($xml_parser, XML_OPTION_TARGET_ENCODING, 'utf-8');
  return $xml_parser;
}

/**
 * Convert data to UTF-8
 *
 * Requires the iconv, GNU recode or mbstring PHP extension.
 *
 * @param $data
 *   The data to be converted.
 * @param $encoding
 *   The encoding that the data is in
 * @return
 *   Converted data or FALSE.
 */
function convert_to_utf8($data, $encoding) {
  if (function_exists('iconv')) {
    $out = @iconv($encoding, 'utf-8', $data);
  }
  else if (function_exists('mb_convert_encoding')) {
    $out = @mb_convert_encoding($data, 'utf-8', $encoding);
  }
  else if (function_exists('recode_string')) {
    $out = @recode_string($encoding .'..utf-8', $data);
  }
  else {
    /* watchdog('php', t("Unsupported encoding '%s'. Please install iconv, GNU
recode or mbstring for PHP.", array('%s' => $encoding)), WATCHDOG_ERROR); */
    return FALSE;
  }
  return $out;
}

?>
