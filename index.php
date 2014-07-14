<?php
class PrettyTemplateIndex {

	const HTML_FILE_EXT = '*.html';
	const PAGE_TITLE_EXTRACT_REGEXP = '/<title>([^<]+)<\/title>/';
	const PAGE_META_SESSION_KEY = 'prettyTemplateIndexMetaCache';


	public function execute() {

		session_start();

		echo(
			$this->getPageHeader() .
			$this->getFileList() .
			'</table></body></html>'
		);
	}

	private function getPageHeader() {

		return <<<'EOT'
<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=Edge" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />

	<title>Pretty Template Index</title>

	<style>
		body { background: #353c42;font: 62.5%/1 Georgia,Times,'Times New Roman',serif;margin: 60px 20px;padding: 0; }
		table { border-collapse: collapse;font-size: 1.6em;margin: 0 auto; }
		table th,table td { color: #000;padding: 8px;vertical-align: top; }
		table th { background: #8899a8;font-weight: bold;text-align: left; }
		table tr { background: #4a535b;-moz-transition: background 200ms linear;-o-transition: background 200ms linear;-webkit-transition: background 200ms linear;transition: background 200ms linear; }
		table tr:nth-child(odd) { background: #545f68; }
		table tr:hover { background: #bcd3e7; }
		table a { color: #000;text-decoration: none; }
		table a:hover { text-decoration: underline; }
		.notfound { text-align: center; }
	</style>
</head>

<body>

<table>
<tr>
	<th>Filename</th>
	<th>Title</th>
	<th>Size</th>
	<th>Last modified</th>
</tr>
EOT;
	}

	private function getFileList() {

		// check for files in same directory as script itself, otherwise try DOCUMENT_ROOT/DOCUMENT_URI
		$fileList = glob(__DIR__ . '/' . self::HTML_FILE_EXT);
		$fileList = ($fileList)
			? $fileList
			: glob($_SERVER['DOCUMENT_ROOT'] . $_SERVER['DOCUMENT_URI'] . self::HTML_FILE_EXT);

		if (!$fileList) {
			// no page templates found
			return '<tr><td class="notfound" colspan="4">No templates found</td></tr>';
		}

		// order alphabetically
		sort($fileList);

		$html = '';
		foreach ($fileList as $fileItem) {
			// get file item name as HTML and file meta data
			$fileItemHtml = htmlspecialchars(basename($fileItem));
			list($pageTitle,$pageSize) = $this->getPageMeta($fileItem);

			$html .=
				'<tr>' .
					'<td><a href="' . $fileItemHtml . '">' . $fileItemHtml . '</a></td>' .
					'<td>' . htmlspecialchars($pageTitle) . '</td>' .
					'<td>' . $pageSize . '</td>' .
					'<td>' . date('Y-m-d H:i:s',filemtime($fileItem)) . '</td>' .
				'</tr>';
		}

		return $html;
	}

	private function getPageMeta($filePath) {

		$HTTPHost = $_SERVER['HTTP_HOST'];
		$documentURI = $_SERVER['DOCUMENT_URI'];
		$filePathBaseName = basename($filePath);
		$fileMetaStoreKey = sprintf(
			'%s:%s:%s',
			$HTTPHost,$documentURI,$filePathBaseName
		);

		// page meta in session cache?
		if (isset($_SESSION[self::PAGE_META_SESSION_KEY][$fileMetaStoreKey])) {
			$metaData = $_SESSION[self::PAGE_META_SESSION_KEY][$fileMetaStoreKey];

			// ensure file modified time matches meta data
			if (array_shift($metaData) == filemtime($filePath)) {
				return $metaData;
			}
		}

		// page not in session cache/file modified

		// attempt page title fetch directly from file
		if (preg_match(
			self::PAGE_TITLE_EXTRACT_REGEXP,
			file_get_contents($filePath),$matches
		)) {
			// success, save filesize direct from file in addition
			$pageTitle = trim($matches[1]);
			$pageFileSize = filesize($filePath);

		} else {
			// grab file meta over http(s) and try again
			$pageFetchURL = sprintf(
				'%s://%s%s%s',
				($_SERVER['SERVER_PORT'] == 80) ? 'http' : 'https',
				$_SERVER['HTTP_HOST'],$_SERVER['DOCUMENT_URI'],
				$filePathBaseName
			);

			// fetch page source - save content size (filesize) and extract title
			$pageContent = file_get_contents($pageFetchURL);
			$pageFileSize = strlen($pageContent);
			$pageTitle = (preg_match(self::PAGE_TITLE_EXTRACT_REGEXP,$pageContent,$matches))
				? trim($matches[1])
				: 'N/A';
		}

		$pageTitle = htmlspecialchars_decode($pageTitle);

		// cache page meta data in session and return
		if (!isset($_SESSION[self::PAGE_META_SESSION_KEY])) {
			$_SESSION[self::PAGE_META_SESSION_KEY] = [];
		}

		$_SESSION[self::PAGE_META_SESSION_KEY][$fileMetaStoreKey] = [
			filemtime($filePath),
			$pageTitle,$pageFileSize
		];

		return [$pageTitle,$pageFileSize];
	}
}


(new PrettyTemplateIndex())->execute();
