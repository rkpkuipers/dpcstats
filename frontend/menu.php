<ul id="nav">
	<li><a href="#">Projects</a>
		<ul>
		<?php
			$query = 'SELECT project, description, active FROM project ORDER BY active DESC, project';
			$result = $db->selectQuery($query);

			while ( $line = $db->fetchArray($result) )
       			{
				echo '<li><a href="index.php?prefix=' . $line['project'] . 
					'&amp;datum=' . $datum . 
					'&amp;mode=' . (in_array($line['project'], array('sp5', 'sp6'))?'Stampede':'Members') .
					'" title="Stats for ' . $line['description'] . '">' . $line['description'] . '</a></li>'."\n";
			}
		?>
		</ul>
	</li>
	
	<li><a href="#">General Links</a>
		<ul>
		<?php
			echo getNavBarEntry('GOT - /5', 'http://gathering.tweakers.net/forum/list_topics/5');
			echo getNavBarEntry('Source Code (Beta)', $baseUrl . '/?mode=stats');
			echo getNavBarEntry('Website Dutch Power Cows','http://www.dutchpowercows.org');
			echo getNavBarEntry('Forum Dutch Power Cows','http://forum.dutchpowercows.org');
		?>
		</ul>
	</li>

	<li><a href="#">Project Links</a>
		<ul>
		<?php
			echo getNavBarEntry('DPC FAQ', 'http://www.dutchpowercows.org/faqs/' . $project->getWDOPrefix());
			echo getNavBarEntry('DPCH', 'http://www.dutchpowercows.org/dpch/' . $project->getWDOPrefix());
			echo getNavBarEntry('Member Graphs', $baseUrl . '/index.php?mode=memberGraphs&amp;prefix=' . $project->getPrefix(), $link++);
			echo getNavBarEntry('Monthly Stats', $baseUrl . '/index.php?mode=monthlyStats&amp;prefix=' . $project->getPrefix(), $link++);
			echo getNavBarEntry('Average Production', $baseUrl . '/index.php?mode=avgProd&amp;tabel=memberoffset&amp;prefix=' . $project->getPrefix());
		
			echo getNavBarEntry('Official Website', $project->getWebsite() );
			echo getNavBarEntry('Official Forum'  , $project->getForum()   );
			?>
		</ul>
	</li>

	<li><a href="#">Visual Options</a>
		<ul>
			<?php	
			echo getNavBarEntry('Toggle Calendar ', 'index.php?prefix=' . $project->getPrefix() . '&amp;tabel=' . $tabel . '&amp;naam=' . rawurlencode($naam) . 
				'&amp;datum=' . $datum . '&amp;mode=' . $mode  . '&amp;setcActive=' . ($cActive=='on'?'off':'on') . '&amp;team=' . rawurlencode($team) );
			echo getNavBarEntry('Toggle Daily Top\'s','index.php?prefix=' . $project->getPrefix() . '&amp;tabel=' . $tabel . '&amp;naam=' . rawurlencode($naam) .
				'&amp;datum=' . $datum . '&amp;mode=' . $mode . '&amp;setdtActive=' . ($dtActive=='on'?'off':'on') . '&amp;teams=' . $teams );
			echo getNavBarEntry('Toggle Shoutbox','index.php?prefix=' . $project->getPrefix() . '&amp;tabel=' . $tabel . '&amp;naam=' . rawurlencode($naam) . 
				'&amp;datum=' . $datum . '&amp;mode=' . $mode . '&amp;setsbActive=' . ($sbActive=='on'?'off':'on') . '&amp;teams=' . $teams );

			?>
			
		</ul>
	</li>
</ul>
