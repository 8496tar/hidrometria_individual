 	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
Design by Free CSS Templates
http://www.freecsstemplates.org
Released for free under a Creative Commons Attribution 2.5 License

Name       : Hot Air Balloons
Description: A two-column, fixed-width design with dark color scheme.
Version    : 1.0
Released   : 20081210

-->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="keywords" content="" />
<meta name="description" content="" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Type" content="text/html; charset=<?= $this->config->item('charset'); ?>" />  
<link rel="stylesheet" type="text/css" media="all" href="<?= $this->config->item('base_url') ?>style.css" />
<link rel="stylesheet" type="text/css" href="<?= $this->config->item('base_url');?>ext/resources/css/ext-all.css" />
<link rel="stylesheet" type="text/css" href="<?= $this->config->item('base_url');?>ext/resources/css/xtheme-blue.css" />
<script type="text/javascript" src="<?= $this->config->item('base_url');?>ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="<?= $this->config->item('base_url');?>ext/ext-all.js"></script>
<title><?=$title?></title>
<link href="style.css" rel="stylesheet" type="text/css" media="screen" />
</head>
<body>
<div id="wrapper">
	<div id="header">
		<div id="logo">
			<h1><a href="#">Drop Counter</a></h1>
		</div>
	</div>
	<!-- end #header -->
	<div id="menu">
		<?php echo $this->load->view("menu") ?>
	</div>
	<!-- end #menu -->
	<div id="page">
		<div id="banner">&nbsp;</div>
		<div id="content">
			<?php echo $content; ?>
		</div>
		<!-- end #content -->
		<div id="sidebar">

		</div>
		<!-- end #sidebar -->
		<div style="clear: both;">&nbsp;</div>
	</div>
	<!-- end #page -->
	<div id="footer">
		<p>2009 Drop Counter. Teste de conclusão de curso. Template <a href="http://www.freecsstemplates.org/">Free CSS Templates</a>.</p>
	</div>
	<!-- end #footer -->
</div>
</body>
</html>
