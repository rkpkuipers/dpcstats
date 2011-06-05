<?php

include('classes/admin.php');

?>

<script language="javascript">
<!--
function checkPass()
{
	if ( password.value != pass2.value )
	{
		alert("Ingevoered passwords zijn ongelijk");
		return false;
	}
	
	return true;
}
-->
</script>
<div style="text-align:center; margin-left:auto; margin-right:auto"><h2>Register</h2></div>
<form name="newUser" action="admin/verify.php" method="post" onsubmit="checkPass();">
<table style="width:200px; margin-left:auto; margin-right:auto">
<tr><td>Username</td><td><input class="TextField" type="text" name="username" value=""></td></tr>
<tr><td>Password</td><td><input class="TextField" type="password" name="password" value=""></td></tr>
<tr><td>Password</td><td><input class="TextField" type="password" name="pass2" value=""></td></tr>
<tr><td>E-Mail</td><td><input class="TextField" type="text" name="email" value=""></td><td style="font-size:10px">optional</td></tr>
<tr><td colspan="2" align="center"><input class="TextField" type="submit" value="Register"></td></tr>
</table>
<div><hr></div>
<div>
<p>De kleine lettertjes:
tadah.mine.nu is een website gemaakt door SpeedKikker voor zichzelf die statistieken verzamelt van lopende Distributed Computing (DC) projecten. Het feit dat andere mensen
er enig nut in zien is een dwaling hunner zeids waar ik niks aan kan doen. Om het leed toch enigzins te verzachten is het mogelijk om een nieuw account toe te voegen aan
het ongetwijfeld enorme lijstje met website accounts dat je al hebt. Momenteel is de functionaliteit geboden via een gebruikersaccount gelimiteerd tot het wederom kunnen
posten in de shoutbox. Op de planning staat een user pagina waarop de accounts in verschillende projecten kunnen worden aangegeven waar dan overzichten van gemaakt kunnen
worden. Gezien het feit dat deze feature al zeker 2 jaar op de wish list staat: Don't hold your breath...<br><br>
tadah.mine.nu en/of SpeedKikker zijn op geen enkele wijze verbonden met of gelieerd aan:
<ol>
<li>Willekeurig welk DC project</li>
<li>Tweakers.net</li>
<li>Dutch Power Cows</li>
</ol>
Vragen over projecten/tweakers.net/DPC/GoT moeten daar gesteld worden niet hier. Geen enkele informatie van gebruikers van de site zal door de admin gebruikt worden voor 
doeleinden anders dan de gebruikers statistieken zoals te zien via de link "Site Traffic" onderaan. Geen van de ingevoerde gegevens zal aan derden verstrekt worden. Een 
e-mail adres opgeven is optioneel en zal alleen gebruikt worden om in bijzondere gevallen contact met de gebruiker op te nemen. Bij een verloren passwoord zal er enkel en 
alleen gecommuniceerd worden via het opgegeven e-mail adres om de privacy te waarborgen. (Als je mij ervan kan overtuigen dat jij jij bent dan zijn uitzonderingen mogelijk) 
</p>