<?php 
include("functions.php"); 

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<?php
		include ("includes/head.php");
		?>
	</head>
	<body>
		<div class="wrapper">
			<div id="page">
				<?php
				include ("includes/header.php");
				include ("includes/menu.php");
				?>

				<div id="content">
						 <?php 
	 imageUpload();
	 
	 ?>
					
					

				</div>

			</div>
		</div>
	</body>
</html>