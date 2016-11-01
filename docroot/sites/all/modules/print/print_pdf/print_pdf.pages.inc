<?php

/**
 * @file
 * Generates the PDF versions of the pages
 *
 * This file is included by the print_pdf module and includes the
 * functions that interface with the PDF generation packages.
 *
 * @ingroup print
 */

module_load_include('inc', 'print', 'print.pages');

/**
 * Generate a PDF version of the printer-friendly page
 *
 * @see print_controller()
 * @see _print_pdf_dompdf()
 * @see _print_pdf_tcpdf()
 */
function print_pdf_controller() {
  // Disable caching for generated PDFs, as Drupal doesn't ouput the proper headers from the cache
  $GLOBALS['conf']['cache'] = FALSE;

  $args = func_get_args();
  $path = filter_xss(implode('/', $args));
  $cid = isset($_GET['comment']) ? (int)$_GET['comment'] : NULL;

  if (!empty($path)) {
    if ($alias = drupal_lookup_path('source', $path)) {
      // Alias
      $path_arr = explode('/', $alias);
      $node = node_load($path_arr[1]);
    }
    elseif (ctype_digit($args[0])) {
      // normal nid
      $node = node_load($args[0]);
    }

    $pdf_filename = variable_get('print_pdf_filename', PRINT_PDF_FILENAME_DEFAULT);
    if (module_exists('token')) {
      if (!empty($pdf_filename) && !empty($node)) {
        $pdf_filename = token_replace($pdf_filename, 'node', $node, TOKEN_PREFIX, TOKEN_SUFFIX, array('clear' => TRUE));
      }
      else {
        $pdf_filename = token_replace($pdf_filename, 'global', NULL, TOKEN_PREFIX, TOKEN_SUFFIX, array('clear' => TRUE));
        if (empty($pdf_filename)) {
          // If empty, use a fallback solution
          $pdf_filename = str_replace('/', '_', $path);
        }
      }
    }
  }
  else {
    $pdf_filename = 'page';
  }

  if (function_exists('transliteration_clean_filename')) {
    $pdf_filename = transliteration_clean_filename($pdf_filename, language_default('language'));
  }

  drupal_alter('print_pdf_filename', $pdf_filename, $path);

  $pdf = print_pdf_generate_path($path, $cid, $pdf_filename . '.pdf');
  if ($pdf == NULL) {
    drupal_goto($path);
    exit;
  }

  $nodepath = (isset($node->path) && is_string($node->path)) ? drupal_get_normal_path($node->path) : 'node/'. $path;
  db_query("UPDATE {print_pdf_page_counter} SET totalcount = totalcount + 1, timestamp = %d WHERE path = '%s'", time(), $nodepath);
  // If we affected 0 rows, this is the first time viewing the node.
  if (!db_affected_rows()) {
    // We must create a new row to store counters for the new node.
    db_query("INSERT INTO {print_pdf_page_counter} (path, totalcount, timestamp) VALUES ('%s', 1, %d)", $nodepath, time());
  }
}

function print_pdf_generate_path($path, $cid = NULL, $pdf_filename = NULL) {
  global $base_url;

  $print = print_controller($path, $cid, PRINT_PDF_FORMAT);
  if ($print === FALSE) {
    return;
  }

  // Img elements must be set to absolute
  $pattern = '!<(img\s[^>]*?)>!is';
  $print['content'] = preg_replace_callback($pattern, '_print_rewrite_urls', $print['content']);
  $print['logo'] = preg_replace_callback($pattern, '_print_rewrite_urls', $print['logo']);
  $print['footer_message'] = preg_replace_callback($pattern, '_print_rewrite_urls', $print['footer_message']);

  // Send to printer option causes problems with PDF
  $print['sendtoprinter'] = '';

  $node = $print['node'];
  $html = theme('print_page', $print, PRINT_PDF_FORMAT, $node);
  $html = drupal_final_markup($html);

  // Convert the a href elements, to make sure no relative links remain
  $pattern = '!<(a\s[^>]*?)>!is';
  $html = preg_replace_callback($pattern, '_print_rewrite_urls', $html);
  // And make anchor links relative again, to permit in-PDF navigation
  $html = preg_replace("!${base_url}/". PRINTPDF_PATH .'/.*?#!', '#', $html);

  return print_pdf_generate_html($print, $html, $pdf_filename);
}

function print_pdf_generate_html($print, $html, $filename = NULL) {
  $print_pdf_pdf_tool = variable_get('print_pdf_pdf_tool', PRINT_PDF_PDF_TOOL_DEFAULT);

  if (basename($print_pdf_pdf_tool) == 'dompdf_config.inc.php') {
    return _print_pdf_dompdf($print, $html, $filename);
  }
  elseif (basename($print_pdf_pdf_tool) == 'tcpdf.php') {
    return _print_pdf_tcpdf($print, $html, $filename);
  }
  elseif (drupal_substr(basename($print_pdf_pdf_tool, '.exe'), 0, 11) == 'wkhtmltopdf') {
    return _print_pdf_wkhtmltopdf($print, $html, $filename);
  }
  elseif ($filename) {
    return drupal_not_found();
  }
  return NULL;
}

/**
 * Convert image paths to the file:// protocol
 *
 * In some Drupal setups, the use of the 'private' filesystem or Apache's
 * configuration prevent access to the images of the page. This function
 * tries to circumnvent those problems by accessing files in the local
 * filesystem.
 *
 * @param $html
 *   contents of the post-processed template already with the node data
 * @see print_pdf_controller()
 */
function _print_pdf_file_access_images($html) {
  global $base_url, $language;
  $print_pdf_images_via_file = variable_get('print_pdf_images_via_file', PRINT_PDF_IMAGES_VIA_FILE_DEFAULT);

  switch (variable_get('language_negotiation', LANGUAGE_NEGOTIATION_NONE)) {
    case LANGUAGE_NEGOTIATION_PATH_DEFAULT:
    case LANGUAGE_NEGOTIATION_PATH:
      $lang = $language->language;
      break;
    default:
      $lang = '';
      break;
  }
  // Always convert private to local paths
  $file_downloads = variable_get('file_downloads', FILE_DOWNLOADS_PUBLIC);
  if ($file_downloads == FILE_DOWNLOADS_PRIVATE) {
    $pattern = "!(<img\s[^>]*?src\s*?=\s*?['\"]?)${base_url}/(?:(?:index.php)?\?q=)?(?:${lang}/)?system/files/([^>]*?>)!is";
    $replacement = '$1file://'. realpath(file_directory_path()) .'/$2';
    $html = preg_replace($pattern, $replacement, $html);
  }
  elseif ($print_pdf_images_via_file) {
    $pattern = "!(<img\s[^>]*?src\s*?=\s*?['\"]?)${base_url}/(?:(?:index.php)?\?q=)?(?:${lang}/)?([^>]*?>)!is";
    $replacement = '$1file://'. dirname($_SERVER['SCRIPT_FILENAME']) .'/$2';
    $html = preg_replace($pattern, $replacement, $html);
  }

  return $html;
}

/**
 * Generate the PDF file using the dompdf library
 *
 * @param $print
 *   array containing the configured data
 * @param $html
 *   contents of the post-processed template already with the node data
 * @param $filename
 *   name of the PDF file to be generated
 * @see print_pdf_controller()
 */
function _print_pdf_dompdf($print, $html, $filename = NULL) {
  $print_pdf_pdf_tool = variable_get('print_pdf_pdf_tool', PRINT_PDF_PDF_TOOL_DEFAULT);
  $print_pdf_paper_size = variable_get('print_pdf_paper_size', PRINT_PDF_PAPER_SIZE_DEFAULT);
  $print_pdf_page_orientation = variable_get('print_pdf_page_orientation', PRINT_PDF_PAGE_ORIENTATION_DEFAULT);
  $print_pdf_content_disposition = variable_get('print_pdf_content_disposition', PRINT_PDF_CONTENT_DISPOSITION_DEFAULT);

  if (variable_get('print_pdf_autoconfig', PRINT_PDF_AUTOCONFIG_DEFAULT)) {
    if (!defined('DOMPDF_ENABLE_PHP')) define("DOMPDF_ENABLE_PHP", FALSE);
    if (!defined('DOMPDF_ENABLE_REMOTE')) define("DOMPDF_ENABLE_REMOTE", TRUE);
    if (!defined('DOMPDF_TEMP_DIR')) define("DOMPDF_TEMP_DIR", file_directory_temp());
    if (!defined('DOMPDF_UNICODE_ENABLED')) define("DOMPDF_UNICODE_ENABLED", variable_get('print_pdf_dompdf_unicode', PRINT_PDF_DOMPDF_UNICODE_DEFAULT));
    if (!defined('DOMPDF_FONT_CACHE')) define("DOMPDF_FONT_CACHE", file_directory_path() . '/' . PRINT_PDF_DOMPDF_CACHE_DIR_DEFAULT . '/fonts/');
  }

  require_once($print_pdf_pdf_tool);
  if (function_exists('spl_autoload_register')) {
    spl_autoload_register('DOMPDF_autoload');
  }

  // Try to use local file access for image files
  $html = _print_pdf_file_access_images($html);

  // Spaces in img URLs must be replaced with %20
  $pattern = '!<(img\s[^>]*?)>!is';
  $html = preg_replace_callback($pattern, '_print_replace_spaces', $html);

  // dompdf seems to have problems with something in system.css so let's not use it
  $html = preg_replace('!<link.*?modules/system/system.css.*?/>!', '', $html);

  $url_array  = parse_url($print['url']);

  $protocol = $url_array['scheme'] .'://';
  $host = $url_array['host'];
  $path = dirname($url_array['path']) .'/';

  $dompdf = new DOMPDF();
  $dompdf->set_base_path($path);
  $dompdf->set_host($host);
  $dompdf->set_paper(drupal_strtolower($print_pdf_paper_size), $print_pdf_page_orientation);
  $dompdf->set_protocol($protocol);

// dompdf can't handle footers cleanly, so disable the following
//  $html = theme('print_pdf_dompdf_footer', $html);

  // If dompdf Unicode support is disabled, try to convert to ISO-8859-1 and then to HTML entities
  if (!variable_get('print_pdf_dompdf_unicode', PRINT_PDF_DOMPDF_UNICODE_DEFAULT)) {
  // Convert the euro sign to an HTML entity
  $html = str_replace('€', '&#0128;', $html);

  // Convert from UTF-8 to ISO 8859-1 and then to HTML entities
  if (function_exists('utf8_decode')) {
    $html = utf8_decode($html);
  }
// iconv fails silently when it encounters something that it doesn't know, so don't use it
//  else if (function_exists('iconv')) {
//    $html = iconv('UTF-8', 'ISO-8859-1', $html);
//  }
  elseif (function_exists('mb_convert_encoding')) {
    $html = mb_convert_encoding($html, 'ISO-8859-1', 'UTF-8');
  }
  elseif (function_exists('recode_string')) {
    $html = recode_string('UTF-8..ISO_8859-1', $html);
  }
  $html = htmlspecialchars_decode(htmlentities($html, ENT_NOQUOTES, 'ISO-8859-1'), ENT_NOQUOTES);
  }
  else {
    // Otherwise, ensure the content is properly formatted Unicode.
    $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
  }

  // Must get rid of tbody (dompdf goes into recursion)
  $html = preg_replace('!<tbody[^>]*?>|</tbody>!i', '', $html);

  $dompdf->load_html($html);

  $dompdf->render();
  if ($filename) {
    $dompdf->stream($filename, array('Attachment' => ($print_pdf_content_disposition == 2)));
    return TRUE;
  }
  else {
    return $dompdf->output();
  }
}

/**
 * Generate the PDF file using the TCPDF library
 *
 * @param $print
 *   array containing the configured data
 * @param $html
 *   contents of the post-processed template already with the node data
 * @param $filename
 *   name of the PDF file to be generated
 * @see print_pdf_controller()
 */
function _print_pdf_tcpdf($print, $html, $filename = NULL) {
  global $base_url, $language;

  $print_pdf_pdf_tool = variable_get('print_pdf_pdf_tool', PRINT_PDF_PDF_TOOL_DEFAULT);
  $print_pdf_paper_size = variable_get('print_pdf_paper_size', PRINT_PDF_PAPER_SIZE_DEFAULT);
  $print_pdf_page_orientation = variable_get('print_pdf_page_orientation', PRINT_PDF_PAGE_ORIENTATION_DEFAULT);
  $print_pdf_content_disposition = variable_get('print_pdf_content_disposition', PRINT_PDF_CONTENT_DISPOSITION_DEFAULT);

  $pdf_tool_path = realpath(dirname($print_pdf_pdf_tool));

  if (variable_get('print_pdf_autoconfig', PRINT_PDF_AUTOCONFIG_DEFAULT)) {
    if (!defined('K_TCPDF_EXTERNAL_CONFIG')) define('K_TCPDF_EXTERNAL_CONFIG', TRUE);
    if (!defined('K_PATH_MAIN')) define('K_PATH_MAIN', dirname($_SERVER['SCRIPT_FILENAME']));
    if (!defined('K_PATH_URL')) define('K_PATH_URL', $base_url);
    if (!defined('K_PATH_FONTS')) define('K_PATH_FONTS', $pdf_tool_path .'/fonts/');
    if (!defined('K_PATH_CACHE')) define('K_PATH_CACHE', file_directory_path() . '/' . PRINT_PDF_TCPDF_CACHE_DIR_DEFAULT . '/cache/');
    if (!defined('K_PATH_IMAGES')) define('K_PATH_IMAGES', '');
    if (!defined('K_BLANK_IMAGE')) define('K_BLANK_IMAGE', $pdf_tool_path .'/images/_blank.png');
    if (!defined('K_CELL_HEIGHT_RATIO')) define('K_CELL_HEIGHT_RATIO', 1.25);
    if (!defined('K_SMALL_RATIO')) define('K_SMALL_RATIO', 2/3);
  }

  // Try to use local file access for image files
  $html = _print_pdf_file_access_images($html);

  // Decode HTML entities in image filenames
  $pattern = "!<img\s[^>]*?src\s*?=\s*?['\"]?{$base_url}[^>]*?>!is";
  $html = preg_replace_callback($pattern, create_function('$matches', 'return html_entity_decode($matches[0], ENT_QUOTES);'), $html);
  // Remove queries from the image URL
  $pattern = "!(<img\s[^>]*?src\s*?=\s*?['\"]?{$base_url}[^>]*?)(?:%3F|\?)[^\s'\"]+([^>]*?>)!is";
  $html = preg_replace($pattern, '$1$2', $html);

  require_once($print_pdf_pdf_tool);
  if (defined('PDF_PRODUCER') && (strpos(PDF_PRODUCER, 'PHP4') !== FALSE)) {
    module_load_include('inc', 'print_pdf', 'print_pdf.class_php4');
  }
  else {
    module_load_include('inc', 'print_pdf', 'print_pdf.class');
  }

  $font = Array(
    check_plain(variable_get('print_pdf_font_family', PRINT_PDF_FONT_FAMILY_DEFAULT)),
    '',
    check_plain(variable_get('print_pdf_font_size', PRINT_PDF_FONT_SIZE_DEFAULT)),
  );
  $orientation = drupal_strtoupper($print_pdf_page_orientation[0]);

  // create new PDF document
  $pdf = new PrintTCPDF($orientation , 'mm', $print_pdf_paper_size, TRUE);

  // set document information
  if (isset($print['submitted'])) {
    $pdf->SetAuthor(strip_tags($print['submitted']));
  }
  $pdf->SetCreator(variable_get('site_name', 'Drupal'));
  $pdf->SetTitle(html_entity_decode($print['title'], ENT_QUOTES, 'UTF-8'));
  if (isset($print['taxonomy'])) {
    $keys = implode(' ', explode("\n", trim(strip_tags($print['taxonomy']))));
    $pdf->SetKeywords($keys);
  }
  $pdf->setPDFVersion('1.6');
  $pdf->setFontSubsetting(variable_get('print_pdf_font_subsetting', PRINT_PDF_FONT_SUBSETTING_DEFAULT));
  $pdf->setTcpdfLink(false);

  if ($language->direction == LANGUAGE_RTL) {
    $pdf->setRTL(TRUE);
  }

  $pdf = theme('print_pdf_tcpdf_header', $pdf, $html, $font);
  $pdf = theme('print_pdf_tcpdf_footer', $pdf, $html, $font);
  $pdf = theme('print_pdf_tcpdf_page', $pdf);

  // add a page
  $pdf->AddPage();

  $pdf = theme('print_pdf_tcpdf_content', $pdf, $html, $font);

  // reset pointer to the last page
  $pdf->lastPage();

  // try to recover from any warning/error
  ob_clean();

  if ($filename) {
    // Close and output PDF document
    $output_dest = ($print_pdf_content_disposition == 2) ? 'D' : 'I';
    $pdf->Output($filename, $output_dest);
    return TRUE;
  }
  else {
    return $pdf = $pdf->Output('', 'S');
  }
}

/**
 * Generate the PDF file using wkhtmltopdf
 *
 * @param $print
 *   array containing the configured data
 * @param $html
 *   contents of the post-processed template already with the node data
 * @param $filename
 *   name of the PDF file to be generated
 * @see print_pdf_controller()
 */
function _print_pdf_wkhtmltopdf($print, $html, $filename = NULL) {
  $print_pdf_pdf_tool = variable_get('print_pdf_pdf_tool', PRINT_PDF_PDF_TOOL_DEFAULT);
  $print_pdf_paper_size = variable_get('print_pdf_paper_size', PRINT_PDF_PAPER_SIZE_DEFAULT);
  $print_pdf_page_orientation = variable_get('print_pdf_page_orientation', PRINT_PDF_PAGE_ORIENTATION_DEFAULT);
  $print_pdf_content_disposition = variable_get('print_pdf_content_disposition', PRINT_PDF_CONTENT_DISPOSITION_DEFAULT);
  $print_pdf_wkhtmltopdf_options = variable_get('print_pdf_wkhtmltopdf_options', PRINT_PDF_WKHTMLTOPDF_OPTIONS);

  $dpi = 96;

  if (function_exists('token_replace') && !empty($print_pdf_wkhtmltopdf_options)) {
    $print_pdf_wkhtmltopdf_options = token_replace($print_pdf_wkhtmltopdf_options, 'node', $print['node'], TOKEN_PREFIX, TOKEN_SUFFIX, array('clear' => TRUE));
  }

  $version = _print_pdf_wkhtmltopdf_version();

  // 0.10.0 beta2 identifies itself as 0.9.9
  if (version_compare($version, '0.9.9', '>=')) {
    $print_pdf_wkhtmltopdf_options = '--disable-local-file-access '. $print_pdf_wkhtmltopdf_options;
  }
  elseif (version_compare($version, '0.9.6', '>=')) {
    $print_pdf_wkhtmltopdf_options = '--disallow-local-file-access '. $print_pdf_wkhtmltopdf_options;
  }
  else {
    drupal_goto($print['url']);
    exit;
  }

  $descriptor = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'a'));
  $cmd = '"'. realpath($print_pdf_pdf_tool) ."\" --page-size $print_pdf_paper_size --orientation $print_pdf_page_orientation --dpi $dpi $print_pdf_wkhtmltopdf_options - -";

  $process = proc_open($cmd, $descriptor, $pipes, NULL, NULL);

  if (is_resource($process)) {
    fwrite($pipes[0], $html);
    fclose($pipes[0]);

    $pdf = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    stream_set_blocking($pipes[2], 0);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    $retval = proc_close($process);
    if (!empty($error) || ($retval != 0)) {
      if (empty($error)) {
        $error = 'No stderr output available.';
      }
      watchdog('print_pdf', 'wkhtmltopdf [%cmd] (returned %ret): %error', array('%cmd' => $cmd, '%ret' => $retval, '%error' => $error));
    }
  }

  if (!empty($pdf)) {
    if ($filename) {
      if (headers_sent()) {
        exit("Unable to stream pdf: headers already sent");
      }
      header("Cache-Control: private");
      header("Content-Type: application/pdf");

      $attachment =  ($print_pdf_content_disposition == 2) ?  "attachment" :  "inline";

      header("Content-Disposition: $attachment; filename=\"$filename\"");

      echo $pdf;
      flush();
      return TRUE;
    }
    else {
      return $pdf;
    }
  }
  else {
    drupal_set_message('Unable to generate PDF file.', 'error');
    drupal_goto($meta['url']);
    return NULL;
  }
}

/**
 * Format the dompdf footer contents
 *
 * @param $html
 *   contents of the body of the HTML from the original node
 * @see theme_print_pdf_tcpdf_footer()
 */
function theme_print_pdf_dompdf_footer($html) {
  preg_match('!<div class="print-footer">(.*?)</div>!si', $html, $tpl_footer);

  if (isset($tpl_footer[1])) {
    $html = str_replace($tpl_footer[0], '', $html);

    $text = '<script type="text/php">
      if (isset($pdf)) {
        $font = Font_Metrics::get_font("verdana");;
        $size = 10;
        $color = array(0,0,0);
        $text_height = Font_Metrics::get_font_height($font, $size);

        $w = $pdf->get_width();
        $h = $pdf->get_height();

        $footer = $pdf->open_object();

        // Draw a line along the bottom
        $y = $h - 25;
        $pdf->line(15, $y, $w - 15, $y, $color, 1);

        $y += $text_height / 2;
        $pdf->page_text(15, $y, \''. addslashes(strip_tags($tpl_footer[1])) .'\', $font, $size, $color);

        $pdf->close_object();
        $pdf->add_object($footer, "all");

        // Center the text
        $width = Font_Metrics::get_text_width("Page 1 of 2", $font, $size);
        $pagenumtxt = t("Page !n of !total", array("!n" => "{PAGE_NUM}", "!total" => "{PAGE_COUNT}"));
        $pdf->page_text($w - 15 - $width, $y, $pagenumtxt, $font, $size, $color);
      }
    </script>';

    return str_replace("<body>", "<body>" . $text, $html);
  } else {
    return $html;
  }
}

/**
 * Format the TCPDF header
 *
 * @param $pdf
 *   current TCPDF object
 * @param $html
 *   contents of the body of the HTML from the original node
 * @param $font
 *   array with the font definition (font name, styles and size)
 * @see theme_print_pdf_tcpdf_header()
 */
function theme_print_pdf_tcpdf_header($pdf, $html, $font) {
  preg_match('!<div class="print-logo">(.*?)</div>!si', $html, $tpl_logo);
  preg_match('!<h1 class="print-title">(.*?)</h1>!si', $html, $tpl_title);
  preg_match('!<div class="print-site_name">(.*?)</div>!si', $html, $tpl_site_name);

  $ratio = 0;
  $logo = '';
  if (isset($tpl_logo[1]) && preg_match('!src\s*=\s*(?:"(.*?)"|\'(.*?)\'|([^\s]*))!i', $tpl_logo[1], $logo_url)) {
    $logo = $logo_url[1];
    if (!empty($logo)) {
      $size = getimagesize($logo);
      $ratio = $size ? ($size[0] / $size[1]) : 0;
    }
  }

  // set header font
  $pdf->setHeaderFont($font);
  // set header margin
  $pdf->setHeaderMargin(5);
  // set header data
  $pdf->setHeaderData($logo, 10 * $ratio, html_entity_decode($tpl_title[1], ENT_QUOTES, 'UTF-8'), html_entity_decode(strip_tags($tpl_site_name[1]), ENT_QUOTES, 'UTF-8'));

  return $pdf;
}

/**
 * Format the TCPDF page settings (margins, etc)
 *
 * @param $pdf
 *   current TCPDF object
 * @see theme_print_pdf_tcpdf_page()
 */
function theme_print_pdf_tcpdf_page($pdf) {
  // set margins
  $pdf->SetMargins(15, 20, 15);
  // set auto page breaks
  $pdf->SetAutoPageBreak(TRUE, 15);
  // set image scale factor
  $pdf->setImageScale(1);
  // set image compression quality
  $pdf->setJPEGQuality(100);

  return $pdf;
}

/**
 * Format the TCPDF page content
 *
 * @param $pdf
 *   current TCPDF object
 * @param $html
 *   contents of the body of the HTML from the original node
 * @param $font
 *   array with the font definition (font name, styles and size)
 * @see theme_print_pdf_tcpdf_content()
 */
function theme_print_pdf_tcpdf_content($pdf, $html, $font) {
  // set content font
  $pdf->setFont($font[0], $font[1], $font[2]);

  preg_match('!<body.*?>(.*)</body>!sim', $html, $matches);
  $pattern = '!(?:<div class="print-(?:logo|site_name|breadcrumb|footer)">.*?</div>|<hr class="print-hr" />)!si';
  $matches[1] = preg_replace($pattern, '', $matches[1]);

  // Make CCK fields look better
  $matches[1] = preg_replace('!(<div class="field.*?>)\s*!sm', '$1', $matches[1]);
  $matches[1] = preg_replace('!(<div class="field.*?>.*?</div>)\s*!sm', '$1', $matches[1]);
  $matches[1] = preg_replace('!<div( class="field-label.*?>.*?)</div>!sm', '<strong$1</strong>', $matches[1]);

  // Since TCPDF's writeHTML is so bad with <p>, do everything possible to make it look nice
  $matches[1] = preg_replace('!<(?:p(|\s+.*?)/?|/p)>!i', '<br$1 />', $matches[1]);
  $matches[1] = str_replace(array('<div', 'div>'), array('<span', 'span><br />'), $matches[1]);
  do {
    $prev = $matches[1];
    $matches[1] = preg_replace('!(</span>)<br />(\s*?</span><br />)!s', '$1$2', $matches[1]);
  } while ($prev != $matches[1]);

  @$pdf->writeHTML($matches[1]);

  return $pdf;
}

/**
 * Format the TCPDF footer contents
 *
 * @param $pdf
 *   current TCPDF object
 * @param $html
 *   contents of the body of the HTML from the original node
 * @param $font
 *   array with the font definition (font name, styles and size)
 * @see theme_print_pdf_tcpdf_footer()
 */
function theme_print_pdf_tcpdf_footer($pdf, $html, $font) {
  preg_match('!<div class="print-footer">(.*?)</div>!si', $html, $tpl_footer);
  if (isset($tpl_footer[1])) {
    $footer = trim(preg_replace('!</?div[^>]*?>!i', '', $tpl_footer[1]));

    // set footer font
    $font[2] *= 0.8;
    $pdf->setFooterFont($font);
    // set footer margin
    $pdf->SetFooterMargin(10);
    // set footer data
    $pdf->setFooterContent($footer);
  }

  return $pdf;
}

/**
 * Format the TCPDF footer layout
 *
 * @param $pdf
 *   current TCPDF object
 * @see theme_print_pdf_tcpdf_footer2()
 */
function theme_print_pdf_tcpdf_footer2($pdf) {
  // Position at 1.5 cm from bottom
  $pdf->writeHTMLCell(0, 15, 15, $pdf->getPageHeight()-15, $pdf->footer);

  $ormargins = $pdf->getOriginalMargins();
  $pagenumtxt = t('Page !n of !total', array('!n' => $pdf->PageNo(), '!total' => $pdf->getAliasNbPages()));
  // Print page number
  if ($pdf->getRTL()) {
    $pdf->SetX($ormargins['right']);
    $pdf->Cell(0, 10, $pagenumtxt, 'T', 0, 'L');
  }
  else {
    $pdf->SetX($ormargins['left']);
    $pdf->Cell(0, 10, $pagenumtxt, 'T', 0, 'R');
  }

  return $pdf;
}