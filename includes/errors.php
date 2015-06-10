<?php

if (count($errors) > 0) {
	echo "<ul class=\"errors\">";
	foreach ($errors as $error) {
		echo "<li>" . $error . "</li>";
	}
	echo "</ul>";
}

if(count($goods) > 0)
{
	echo "<ul class=\"goods\">";
	foreach ($goods as $good) {
		echo "<li>" . $good . "</li>";
	}
	echo "</ul>";
}

?>