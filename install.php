<head>
	<link rel="stylesheet" type="text/css" href="assets/css/install.css">
</head>
<body>
	<div class='content_view'>
		<h1>Sagara Logboek</h1>
		<h2>Install Script</h2>
		<p>
			Dit script is bedoeld om het logboek systeem te installeren. Dit script doet de volgende dingen:
			<ul>
				<li>Creëert de mysql database</li>
				<li>Maakt de admin user aan</li>
			</ul>
		</p>
		
		<?php
		$BaseDir = __DIR__;
		$BaseURL = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$BaseURL = str_replace("install.php", "", $BaseURL);
		echo $BaseDir."<br>";
		echo $BaseURL;
		?>
	
	</div>
</body>