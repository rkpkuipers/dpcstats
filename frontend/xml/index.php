<?php
include ('../classes.php');
?>
<html>
<head>
<style>
td
{
	vertical-align:top;
}
</style>
</head>
<body>
<table border="1">
<tr>
<td>Variabele</td>
<td>Omschrijving</td>
<td>Waarden</td>
</tr>
<tr>
<td valign="top">tabel</td>
<td valign="top">Tabel waar de data uit moet komen</td>
<td>memberoffset<br>teamoffset<br>subteamoffset</td>
</tr>
<tr>
<td>prefix</td>
<td>Project</td>
<td>
<?php
$query = 'SELECT project, description FROM project where active = 1 ORDER BY project';
$result = $db->selectQuery($query);

while ( $line = $db->fetchArray($result) ) echo $line['project'] . ' - ' . $line['description'] . '<br>';
?>
</td>
</tr>
<tr>
<td>datum</td>
<td>Datum in YYYY-MM-DD formaat</td>
<td>Alle data waarop data voor het project beschikbaar is</td>
</tr>
<tr>
<td>team</td>
<td>Bij data uit subteam tabel, welk subteams data opgehaald moet worden</td>
</tr>
<tr>
<td>offset</td>
<td>Offset van de lijst</td>
<td>0 tot lengte lijst</td>
</tr>
<tr>
<td>listsize</td>
<td>Lengte van de op te halen lijst</td>
<td>0 tot lengte lijst</td>
</tr>
</table>
<br>
<pre>
De xml scripts zijn te vinden onder http://tadah.mine.nu/xml/list.php en http://tadah.mine.nu/xml/detail.php 
Meerdere variabelen kunnen gecombineerd worden. 
De eerste wordt na een ? geplaatst. Alles wat daar na komt wordt door een & voorafgegaan:
list.php?tabel=teamoffset&prefix=sob of
list.php?prefix=rah&tabel=subteamoffset&team=Los%20Alcoholicos
detail.php?prefix=sob&tabel=memberoffset&naam=SpeedKikker
De volgorde van de variabelen maakt niet uit. 
Prefix is verplicht, team is alleen verplicht als voor mode subteamoffset wordt meegegeven.
</pre>
</body>
</html>
