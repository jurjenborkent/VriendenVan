<?php
$conn = mysqlI_connect("localhost", "root", "usbw", "mydb") or die("cannot connect");

	

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

function getContent() {
	$conn = $GLOBALS['conn'];
	$sql= "SELECT * FROM content";
	$query = mysqli_query($conn, $sql) or die(mysqli_error($conn));
	$result = mysqli_fetch_row($query);
	print_r($result);

}

?>