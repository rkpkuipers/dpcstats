<P STYLE="margin-bottom: 0in"><FONT SIZE=4><B>Stats Systeem</B></FONT></P>
<P STYLE="margin-bottom: 0in"><I>Algemeen</I></P>
<P STYLE="margin-bottom: 0in">Begin 2004 na het joinen van DPC ben ik
begonnen met het opzetten van een statistieken site. Waarom weet ik
niet meer, waarschijnlijk omdat ik het niet al te druk had
toendertijd. De site was origineel enkele en alleen bedoeld voor TSC,
dit is helaas hier en daar in de opzet en in sommige benamingen nog
duidelijk te zien. Aangezien ik niet altijd alleen maar TSC heb
gedraait leek het me ergens eind 2004 een goed idee om het systeem
geschikt te maken voor meerdere projecten. In eerste instantie is het
systeem uitgebreid met D2OL wat als voordeel heeft dat het praktisch
identiek is aan TSC. Na de toevoeging van D2OL met alle wijzigingen
in de backend van dien is het systeem uitgebreid met FaD om de
conversie af te maken. Vanaf july 2005 zijn de stats officieel in
gebruik door DPC als bron voor TSC en D2OL. Vanaf begin dit jaar zijn
daar door de HTML -&gt; RML conversie SoB, Seti, Folding@Home
en enkele weken later uFluids aan toegevoegd.</P>
<P STYLE="margin-bottom: 0in"><I>Opzet</I></P>
<P STYLE="margin-bottom: 0in">Het stats syteem bestaat uit 2 delen
met een database voor opslag van gegevens. Een frontend die de data
uit de database haalt en aan de user toont en een backend die de data
van de verschillende projectsites haalt en in de database plaatst.
Het systeem is gebouwd op een linux machine, de gebruikte webserver
is Apache 2 met PHP 5. Voor de database wordt momenteel MySQL 5
gebruikt.</P>
<P STYLE="margin-bottom: 0in"><B>Database</B></P>
<P STYLE="margin-bottom: 0in">Voor elk project zijn er losse tabellen
waar de data in wordt opgeslagen. Om te beginnen zijn die
{project}_memberOffset en {project}_teamOffset. Eventueel is er ook
een {project}_subteamOffset tabel aanwezig maar dit ligt aan het
project. In deze tabellen staan alle gegevens van de
members/teams/subteam van dat project. De structuur is: naam,
cands(offset), id(rank), dag, daily, dailypos(dailyRank) en currRank.
De namen tussen haakjes zijn ter verduidelijking, dit zijn tevens de
namen die de velden eigenlijk moeten hebben maar de database is qua
structuur sinds begin 2004 nooit meer aangepast.. 
</P>
<P STYLE="margin-bottom: 0in">De indeling in verschillende tabellen
is gedaan om te voorkomen dat er een grote tabel zou onstaan met een
paar miljoen regels. Dat lijkt onwaarschijnlijk maar de hele database
bevat op het moment dat ik dit schrijf (07-03-2006) 8,7 miljoen
records versprijd over 83 tabellen. Het overgrote deel hiervan zijn
member/team/subteam records van de verschillende projecten.</P>
Naast de project tabellen zijn er nog een serie tabellen met
metadata:</P>
<TABLE WIDTH=100% BORDER=1 BORDERCOLOR="#000000">
	<COL WIDTH=51*>
	<COL WIDTH=205*>
	<THEAD>
		<TR VALIGN=TOP>
			<TD WIDTH=20% align="center">
				<I><B>Tabel</B></I>
			</TD>
			<TD WIDTH=80% ALIGN=CENTER><I><B>Inhoud</B></I>
			</TD>
		</TR>
	</THEAD>
	<TBODY>
		<TR VALIGN=TOP>
			<TD WIDTH=20%>
				<FONT SIZE=2>additional</FONT>
			</TD>
			<TD WIDTH=80%>
				<FONT SIZE=2>Additionele informatie over een member, zoals bij
				SoB tests. Dit moet grotendeels nog geimplementeerd worden.</FONT>
			</TD>
		</TR>
		<TR VALIGN=TOP>
			<TD WIDTH=20%>
				<FONT SIZE=2>averageProduction</FONT>
			</TD>
			<TD WIDTH=80%>
				<FONT SIZE=2>De gemiddelde week en maand output van
				members/teams/subteam. </FONT>
			</TD>
		</TR>
		<TR VALIGN=TOP>
			<TD WIDTH=20%>
				<FONT SIZE=2>changelog</FONT>
			</TD>
			<TD WIDTH=80%>
				<FONT SIZE=2>Veranderingen die in het systeem zijn
				aangebracht. Gebruik moet nog geherevalueerd worden na het succes
				van de bugtracker.</FONT>
			</TD>
		</TR>
		<TR VALIGN=TOP>
			<TD WIDTH=20%>
				<FONT SIZE=2>links</FONT>
			</TD>
			<TD WIDTH=80%>
				<FONT SIZE=2>Bevat links die op de site en onder de dpch's
				geplaatst moeten worden.</FONT>
			</TD>
		</TR>
		<TR VALIGN=TOP>
			<TD WIDTH=20%>
				<FONT SIZE=2>milestones</FONT>
			</TD>
			<TD WIDTH=80%>
				<FONT SIZE=2>De mijlpalen die bij een project te halen zijn.
				Dit wordt los opgeslagen om project specifieke mijlpalen mogelijk
				te maken (BUG:000005)</FONT>
			</TD>
		</TR>
		<TR VALIGN=TOP>
			<TD WIDTH=20%>
				<FONT SIZE=2>movement</FONT>
			</TD>
			<TD WIDTH=80%>
				<FONT SIZE=2>Bevat per dag de nieuwe en vertrekkende leden.</FONT>
			</TD>
		</TR>
		<TR VALIGN=TOP>
			<TD WIDTH=20%>
				<FONT SIZE=2>project</FONT>
			</TD>
			<TD WIDTH=80%>
				<FONT SIZE=2>De algemene informatie over elk project dat
				beschikbaar is in het systeem.</FONT>
			</TD>
		</TR>
		<TR VALIGN=TOP>
			<TD WIDTH=20%>
				<FONT SIZE=2>shoutbox</FONT>
			</TD>
			<TD WIDTH=80%>
				<FONT SIZE=2>Alle berichten die in de shoutbox gepost zijn.</FONT>
			</TD>
		</TR>
		<TR VALIGN=TOP>
			<TD WIDTH=20%>
				<FONT SIZE=2>updates</FONT>
			</TD>
			<TD WIDTH=80%>
				<FONT SIZE=2>Datum/tijd dat de tabellen voor het laatst
				bijgewerkt zijn vanaf de verschillende bronnen.</FONT>
			</TD>
		</TR>
	</TBODY>
</TABLE>
<P STYLE="margin-bottom: 0in">De totale database bevat bijna 9
miljoen records over 83 tabellen en is 1 GB groot.</P>
<P STYLE="margin-bottom: 0in"><B>Backend</B></P>
<P STYLE="margin-bottom: 0in; font-weight: medium">De backend
verzorgt de statsrun. Elk project heeft 1 of 2 scripts die de
member/team informatie van de projectsites af halen en die parsen.
Daarna worden de verkregen lijsten doorgegeven aan de updateStats()
functie die ervoor zorgt dat de database wordt bijgewerkt en alle
getallen, lijsten en metadata goed word gezet. Voor elke statsrun uit
draait het offset script. Die kijkt of er voor de huidige dag data in
de database zit en zo niet wordt daarvoor gezorgd. Deze manier van
werken bied erg veel flexibiliteit: het toevoegen van een nieuw
project vergt niet meer dan 2 scripts schrijven om de data te
verzamelen van het project, de algemene project informatie in de
project tabel te zetten en de member/team/subteamOffset tabellen aan
te maken. De scripts in de backend worden aangestuurd door een serie
cronjobs.</P>
<p style="margin-bottom: 0in"><b>Subversion Repository</b></p>
<P STYLE="margin-bottom: 0in; font-weight: medium">Om de code beter beschikbaar en hanteerbaar te maken zit alles in een <a href="http://www.subversion.org">subversion</a> repository. Anonymous checkouts zijn mogelijk vanaf de url http://rkuipers.mine.nu/svn/frontend en http://rkuipers.mine.nu/svn/backend. Code commiten kan alleen met password maar patches op het geheel (via bv. svn diff) zijn altijd welkom.
<P STYLE="margin-bottom: 0in"><B>Links</B></P>
<br>
<a href="http://tadah.mine.nu/export/database.sql">Database dump zonder data</a><br>
<a href="http://tadah.mine.nu/export/frontend.tar.bz2">Frontend</a><br>
<a href="http://tadah.mine.nu/export/backend.tar.bz2">Backend</a><br>
<a href="crontab">Cronjobs</a>
