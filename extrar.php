#!/usr/bin/php
<?php
# questo file, se possibile, va riscritto in perl.
function extractArticle($html){
		// http://stackoverflow.com/a/4911037
		if (preg_match('/(?:<article[^>]*>)(.*)<\/article>/isU', $html, $matches))
			return $matches[1];
		else
			return false;
}
echo extractArticle(file_get_contents($argv[1]));
?>