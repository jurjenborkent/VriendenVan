<?php
$conn = mysqli_connect("localhost", "root", "usbw", "mydb") or die(" echo 'connectie mislukt'");
error_reporting(0);

function redirect($url) {
	if (!headers_sent()) {
		header('Location: ' . $url);
		exit ;
	} else {
		echo '<script type="text/javascript">';
		echo 'window.location.href="' . $url . '";';
		echo '</script>';
		echo '<noscript>';
		echo '<meta http-equiv="refresh" content="0;url=' . $url . '" />';
		echo '</noscript>';
		exit ;
	}
}

function singleSelect($select, $from, $where) {
	$conn = $GLOBALS['conn'];

	$sql = "SELECT $select 
			FROM $from
			WHERE $where";

	$query = mysqli_query($conn, $sql) or die(mysqli_error($conn));
	$result = mysqli_fetch_row($query);
	return $result[0];
}

function singleSelectnoWhere($select, $from) {
	$conn = $GLOBALS['conn'];

	$sql = "SELECT $select 
			FROM $from
			";

	$query = mysqli_query($conn, $sql) or die(mysqli_error($conn));
	$result = mysqli_fetch_row($query);
	return $result[0];
}

// ---------CMS---------------------------------------------------------------------------------


function menu_pages() {
	$conn = $GLOBALS['conn'];
	$query = mysqli_query($conn, "SELECT * FROM pages order by id") or die(mysqli_error($conn));

	while ($post = mysqli_fetch_assoc($query)) {
		echo '<a href="pages.php?id=' . $post['id'] . '" ><span class="menu-item">' . $post['titel'] . '</span></a>';

	}
}

function pages() {
	$conn = $GLOBALS['conn'];
	$id = $_GET['id'];

	if (isset($_GET['id'])) {

		echo '<A HREF="pages.php?" class= "goback">Ga terug naar overzicht</A><form method="post" action="">';
		echo '<table><tr><td>Menu-naam voor pagina:</td><td><input type="varchar" name="titel" value="' . singleSelect('titel', 'pages', "id='$id'");
		echo '"></td></tr></table><br>';
		echo '<noscript>Zet Javascript aan in uw browser om dit te gebruiken.<br></noscript><textarea name="tekst" type="text" id="wysiwyg" rows="5" cols="103"><p>' . singleSelect('tekst', 'pages', "id='$id'");
		echo '</p></textarea><table><tr><td class="antispam">Leave this empty:<input name="url" /></td></tr><tr><td><input type="submit" value="Opslaan" name="opslaan"></td></tr></table></form><br><hr><br>';

		if (isset($_POST['opslaan'])) {
			$tekst = $_POST['tekst'];
			$titel = $_POST['titel'];
			$tekst = addslashes($tekst);
			mysqli_query($conn, "UPDATE `pages` SET titel='$titel', tekst='$tekst' WHERE id ='$id'") or die(mysqli_error($conn));
			echo '<META HTTP-EQUIV="Refresh" CONTENT="0">';

		}
	} 
}

function imageUpload() {

	$conn = $GLOBALS['conn'];

	if (!isset($_GET['id'])) {
		echo '<h2>Galerij</h2><br>';
		$query = mysqli_query($conn, "SELECT * FROM  gallery_id ORDER BY id") or die(mysqli_error($conn));
		echo '<a href="gallery.php?id=0">Nieuwe Galerij</a><br><br><table>';
		while ($post = mysqli_fetch_assoc($query)) {
			echo '<tr><td width="400"><a href="gallery.php?id=' . $post['id'] . '" >' . $post['gallery'] . '</a></td><td width="100"><a href="gallery.php?id=0&gal=' . $post['id'] . '" >Titel wijzigen</a></td><td><a href="gallery.php?del=' . $post['id'] . '" >Verwijderen</a></td></tr>';

		}
		

		if (isset($_GET['del'])) {
			mysqli_query($conn, "DELETE FROM gallery WHERE gallery = " . $_GET['del']) or die(mysqli_error($conn));
			mysqli_query($conn, "DELETE FROM gallery_id WHERE id = " . $_GET['del']) or die(mysqli_error($conn));
			foreach (glob('images/' . $_GET['del'].'/*') as $file) {

				unlink($file);
			}
			rmdir("images/" . $_GET['del']);
			redirect("gallery.php");
		}

	} else {
		if ($_GET['id'] == 0) {

			addGallery();

		} else {
			$gallery = $_GET['id'];

			echo '<A HREF="gallery.php" class= "goback">Ga terug naar overzicht</A>';
			if (isset($_POST['submit'])) {
				if ($_POST['submit'] === "Upload") {

					$toegestaan = array("JPG", "jpg", "jpeg", "gif", "png");
					$extensie = end(explode(".", $_FILES["file"]["name"]));
					if ((($_FILES["file"]["type"] == "image/gif") || ($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/JPG") || ($_FILES["file"]["type"] == "image/png") && ($_FILES["file"]["type"] == "image/pjpeg")) && in_array($extensie, $toegestaan)) {

						echo "Afbeelding: " . $_FILES["file"]["name"] . "<br>";
						echo "Type: " . $_FILES["file"]["type"] . "<br>";
						$rand = rand(0, 99999);
						$rand2 = time();
						$random = $rand . $rand2;
						mkdir("images/" . $gallery . "/", 0777);
						$image = "images/" . $gallery . '/' . $random . "." . $extensie;
						echo "Opgeslagen in: " . $image;
						if (file_exists($image)) {
							echo "<br/> Er is al een afbeelding met dezelfde bestandsnaam! <br/><br/>";
						} else {
							mysqli_query($conn, "INSERT INTO gallery (image_id, image, gallery) VALUES ('$random' , '$image' , '$gallery')");
							move_uploaded_file($_FILES["file"]["tmp_name"], $image);
							echo "<br/> Afbeelding geupload! <br/><br/>";

						}
					} else {
						echo "<strong>Ongeldig bestand </strong><br>";
					}
				}
			}

			echo "<h2>" . singleSelect('gallery', 'gallery_id', "id='$gallery'") . "</h2><br><br>";
			echo '<form action="" method="post" enctype="multipart/form-data"><p>
							<label for="file">Selecteer afbeelding:</label><br>
							<input type="file" name="file" id="file"><br />
							<input type="submit" name="submit" value="Upload"><p>
							</form><br><br><hr>';

			imageView();

		}
	}
}

function imageView() {
	$conn = $GLOBALS['conn'];
	$id = $_GET['id'];

	$query = mysqli_query($conn, "SELECT * FROM gallery WHERE gallery ='$id'") or die(mysqli_error($conn));
	echo "<strong>Aantal afbeeldingen in deze galerij: " . mysqli_num_rows($query) . "</strong><br /><br />";
	echo "<table>";
	while ($post = mysqli_fetch_assoc($query)) {

		echo "<tr><td><img src=\"" . $post['image'] . "\" width=\"500\" height=\"250\" alt=\"Image \"></td>";
		echo '<td><a href="gallery.php?id=' . $id . '&gal=' . $post['gallery'] . '&del=1&img=' . $post['image_id'] . '">Verwijderen</a></td>';

	}
	echo "</table>";

	if (isset($_GET['img'])) {
		if (isset($_GET['del'])) {
			if (isset($_GET['gal'])) {

				mysqli_query($conn, "DELETE FROM gallery WHERE image_id = " . $_GET['img']) or die(mysqli_error($conn));
				$unlink = glob("images/*/" . $_GET['img'] . ".*");
				unlink(implode($unlink));
				redirect("gallery.php?id=" . $_GET['gal']);
			}
		}
	}

}

function addGallery() {
	$conn = $GLOBALS['conn'];
	echo "<h2>Galerij titel</h2><br><br>";
	echo '<form action="" method="post" <p>	<input type="varchar" name="name" value="';
	$titel = $_POST['name'];
	if (isset($_GET['gal'])) {
		$gal = $_GET['gal'];
		echo singleSelect('gallery', 'gallery_id', "id='$gal'");
		$stmt = "UPDATE gallery_id SET gallery =' $titel' WHERE id = '$gal'";
		$url = "gallery.php?id=" . $gal;

	} else {
		echo "vul hier uw titel in";
		$stmt = "INSERT INTO gallery_id (gallery) VALUES ('$titel')";


		$tablestatus = mysqli_query($conn, "SHOW TABLE STATUS LIKE 'gallery_id' ");
		$fetch = mysqli_fetch_array($tablestatus);
		$auto_increment = $fetch['Auto_increment'];

		$url = "gallery.php?id=" . $auto_increment;
	}
	echo '"><input type="submit" name="submit" value="Opslaan"></form>';

	if (isset($_POST['submit'])) {
		mysqli_query($conn, $stmt) or die(mysqli_error($conn));
		redirect($url);

	}

}

function fileUpload() {
	$conn = $GLOBALS['conn'];

	$directory = '../nieuwsbrieven/';
	$titel = 'Nieuwsbrieven' ;
		
	if (isset($_POST['submit'])) {
		if ($_POST['submit'] === "Upload") {

			$extensie = end(explode(".", $_FILES["file"]["name"]));

			echo "Bestand: " . $_FILES["file"]["name"] . "<br>";
			echo "Type: " . $_FILES["file"]["type"] . "<br>";

			$image = $directory . $_FILES["file"]["name"];

			echo "Opgeslagen in: " . $image;
			if (file_exists($image)) {
				echo "<br/> Er is al een bestand met dezelfde bestandsnaam! <br/><br/>";
			} else {
				move_uploaded_file($_FILES["file"]["tmp_name"], $image);
				echo "<br/> Bestand geupload! <br/><br/>";
			}
		}
	}
	echo "<h2>" . $titel . "</h2><br><br>";
	echo '<form action="" method="post" enctype="multipart/form-data"><p>
						<label for="file">Selecteer bestand:</label><br>
						<input type="file" name="file" id="file"><br />
						<input type="submit" name="submit" value="Upload"><p>
						</form><br><br><hr>';

	/* FILE DELETE */

	echo '<form action="" method="post">Selecteer bestanden, klik daarna op verwijderen <input type="submit" name="submit" value="Verwijderen"><table>';

	foreach (glob($directory.'*.*') as $file) {
		$string = $file;
		$check = "<INPUT TYPE=\"checkbox\" NAME=\"checkbox[]\" VALUE=\"" . $file . "\">";

		echo " <tr><td>" . str_replace($directory, "", $string) . "</td><td>" . $check . "</td></tr>";
	}
	echo "</table>";
	echo "</form>";

	if (isset($_POST['submit'])) {
		if ($_POST['submit'] === "Verwijderen") {
			foreach ($_POST['checkbox'] as $value) {

				if (file_exists($value)) {
					unlink($value);
					redirect(basename($_SERVER['PHP_SELF']) . "?id=" . $_GET['id'] . $redirect);
				}
			}
		}
	}

	
}

function contact() {
	$errors = array();
	$goods = array();

	if (isset($_POST['verstuur'])) {

		$naam = $_POST['naam'];
		$email = $_POST['email'];
		$tel = $_POST['telefoon'];
		$bericht = $_POST['bericht'];
		$contact = $_POST['contactvorm'];

		$to = "jurjenborkent@gmail.com";
		$subject = "[" . $contact . "] " . $naam . " heeft het contactformulier van MarPo ingevuld";
		$message = "$naam heeft het contactformulier van MarPo ingevuld:\nBetreft: ".$contact."\n\n" . "Naam: $naam\n" . "Email: $email \n" . "Telefoon: $tel \n" . "Bericht: \n " . "$bericht\n\n\n" . "Contact formulier MarPo.\n";

		$headers = "From: $email \n";
		$headers .= "Reply-To: $email \n";

		if ($naam == "") {
			$errors[] = "Er is geen naam ingevuld";
		}
		if ($email == "") {
			$errors[] = "Er is geen e-mailadres ingevuld";
		}

		if (!filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)) {
			$errors[] = "E-mailadres is onjuist";
		}

		if (!is_numeric($tel)) {
			$errors[] = "Telefoonnummer mag alleen cijfers bevatten";
		}

		if (strlen($tel) < 10 || strlen($tel) > 10) {
			$errors[] = "Een telefoonnummer bevat 10 cijfers";
		}

		if ($bericht == "") {
			$errors[] = "Er is geen bericht ingevuld";
		}

		if (count($errors) == 0 && isset($_POST['url']) && $_POST['url'] == '') {
			mail($to, $subject, $message, $headers);
			$goods[] = "<strong>Bericht succesvol verstuurd</strong>";
			foreach ($_REQUEST as $i => $value) {
				unset($_REQUEST[$i]);
			}
		} else {
			$errors[] = "<strong>Bericht niet verzonden. Probeer het nogmaals</strong>";
		}
	}
	include ("includes/errors.php");
	echo '<p>
					Velden met * zijn verplicht.
					<form method="post" action="">
				
				Soort bericht* <br>
						<select name="contactvorm">
						<option value="Offerte">Offerte</option>
						<option value="Contact">Vraag</option>
						<option value="Bericht/Opmerking">Bericht/Opmerking</option>	
						</select>
					
					<br><br>
					Naam (volledig)*
					<br><input type="text" name="naam" value="' . $_REQUEST["naam"] . '">		
					<br><br>
					E-mailadres*<br>
					<input type="text" name="email" value="' . $_REQUEST["email"] . '">		
					<br><br>
					Telefoonnummer (inclusief netnummer)
					<br>
					<input type="text" name="telefoon" value="' . $_REQUEST["telefoon"] . '">		
					<br><br>
					Bericht*
					<br>
					<textarea name="bericht" rows=8 cols=30 >' . $_REQUEST["bericht"] . '</textarea>
					<div class="antispam">niet invullen.
					
					<br />
					<input name="url" /></div>
					
					<br><br><input type="submit" value="Verstuur" name="verstuur" class="btn btn-default">
					
			</form>
				</p>';

}
?>