#!/usr/bin/php
<?

include (dirname(realpath($argv[0])) . '/../include.php');

$datum = getCurrentDate('wcg');

dailyOffset('memberoffset', 'wcg');
dailyOffset('subteamoffset', 'wcg');
dailyOffset('individualoffset', 'wcg');
$tempdir = '/home/rkuipers/stats/statsrun/files/';

$pages = 2;

#/*
for($i=1;$i<=$pages;$i++)
{
	if ( is_file($tempdir . '/wcgm-' . str_pad($i, 2, 0, STR_PAD_LEFT)) )
		unlink($tempdir . '/wcgm-' . str_pad($i, 2, 0, STR_PAD_LEFT));

	system('wget --quiet --tries 5 -O ' . $tempdir . '/wcgm-' . str_pad($i, 2, 0, STR_PAD_LEFT) . ' "http://www.worldcommunitygrid.org/team/viewTeamMemberDetail.do?sort=points&&numRecordsPerPage=250&pageNum=' . $i . '&teamId=025RSMBR9N1"');
}

for($ctrl=0;$ctrl<5;$ctrl++)
{
        for($i=1;$i<=$pages;$i++)
	{
		if ( file_exists($tempdir . '/wcgm-' . str_pad($i, 2, 0, STR_PAD_LEFT) ) )
		{
			if ( filesize($tempdir . '/wcgm-' . str_pad($i, 2, 0, STR_PAD_LEFT)) < 70000 )
				system('wget --quiet --tries 5 -O ' . $tempdir . '/wcgm-' . str_pad($i, 2, 0, STR_PAD_LEFT) . ' "http://www.worldcommunitygrid.org/team/viewTeamMemberDetail.do?sort=points&teamId=025RSMBR9N1&numRecordsPerPage=250&pageNum=' . $i . '"');
		}
		else
		{
			system('wget --quiet --tries 5 -O ' . $tempdir . '/wcgm-' . str_pad($i, 2, 0, STR_PAD_LEFT) . ' "http://www.worldcommunitygrid.org/team/viewTeamMemberDetail.do?sort=points&teamId=025RSMBR9N1&numRecordsPerPage=250&pageNum=' . $i . '"');
		}
	}
}
#*/

system('cat ' . $tempdir . '/wcgm-* | grep viewMemberInfo -A 14 | grep -v -e white\.gif -e class=\"contentText -e \<\/span\>\<\/td\> > ' . $tempdir . '/wcg-members2');
system('dos2unix -q ' . $tempdir . '/wcg-members2');
system('sed \'/^[[:blank:]]*$/d\' ' . $tempdir . '/wcg-members2 > ' . $tempdir . '/wcg-members');
unlink($tempdir . '/wcg-members2');

$member = array();
$subteam = array();
	
$lines = file($tempdir . '/wcg-members');

for($i=0;$i<count($lines);$i+=6)
{
	$data = preg_replace(array('@<b>@si', '@</b>@si', '@<a[^>]*?>@si', '@</a>@si', '@<td[^>]*?>@si', '@</td>@si', '@</span>@si'), "", $lines[$i+1]);
		
	$score = str_replace(',', '', trim($lines[$i+4]));

	addMember($member, $subteam, trim($data), str_replace(',', '', trim($lines[$i+4])), '.');
}

fixLists($member, $subteam, '.');

updateStats($member, 'wcg_memberoffset');

foreach($subteam as $stname => $stmemberlist)
{
	foreach($stmemberlist as $stmembername => $stmemberscore)
	{
		$subteammembers[] = new TeamMember($stmembername, $stmemberscore, $stname);
	}
}

addSubteamStatsrun($subteammembers, 'wcg_subteamoffset');
	
for($i=1;$i<=$pages;$i++)
{
	unlink($tempdir . '/wcgm-' . str_pad($i, 2, 0, STR_PAD_LEFT));
}
unlink($tempdir . '/wcg-members');

individualStatsrun('wcg');
?>
