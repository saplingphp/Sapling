<?php
	// Init variables :
		$titles = isset($titles) ? array_unique($titles) : array();
		$stylesheets = isset($stylesheets) ? array_unique($stylesheets) : array();
		$scripts = isset($scripts) ? array_unique($scripts) : array();
		$title = implode(" - ", $titles);

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">

<html>
	<head>
		<title><?php echo $title ?></title>
		<meta content="text/html; charset=UTF-8" http-equiv="content-type" />
		<meta content="en" http-equiv="content-language" />
		
		<link type="text/css" href="<?php echo URI_ROOT_CSS ?>/site.css" rel="stylesheet" />
		<?php foreach($stylesheets as $stylesheet) { ?>
			<link type="text/css" href="<?php echo $stylesheet ?>" rel="stylesheet" />
		<?php } ?>
		
		<script type="text/javascript" src="<?php echo URI_ROOT_JS ?>/script.js" ></script>
		<?php foreach($scripts as $script) { ?>
			<script type="text/javascript" src="<?php echo $script ?>"></script>
		<?php } ?>
	</head>
	<body>
		<div id="content">
<?php echo $content; ?>
		</div>
	</body>
</html>