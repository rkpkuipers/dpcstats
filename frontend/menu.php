<ul id="nav">
	<li><a href="#">Projects</a>
		<ul>
		<?
			$query = 'SELECT project, description, active FROM project ORDER BY active DESC, project';
			$result = $db->selectQuery($query);

		      
		      	$currAct = 1;
			while ( $line = $db->fetchArray($result) )
       			{
				if ( ( $currAct == 1 ) && ( $line['active'] == 0 ) )
				{	
					echo '<li><a href="#">Additional Projects&nbsp;&nbsp;&nbsp;&gt;</a><ul>';
					$currAct = 0;
				}

				echo '<li><a href="index.php?prefix=' . $line['project'] . 
					'&amp;datum=' . $datum . 
					'&amp;mode=' . (in_array($line['project'], array('sp5', 'sp6'))?'Stampede':'Members') .
					'" title="Stats for ' . $line['description'] . '">' . $line['description'] . '</a></li>'."\n";
			}

		?>
				</ul>
			</li>
		</ul>
	</li>

	<li><a href="#">General Links</a>
		<ul>
		<?
			echo getNavBarEntry('Bug Tracker', $baseUrl . '/mantis/view_all_bug_page.php');
			echo getNavBarEntry('GOT - /5', 'http://gathering.tweakers.net/forum/list_topics/5');
			echo getNavBarEntry('Source Code (Beta)', $baseUrl . '/?mode=stats');
			echo getNavBarEntry('Website Dutch Power Cows','http://www.dutchpowercows.org');
			echo getNavBarEntry('Forum Dutch Power Cows','http://forum.dutchpowercows.org');
		?>
		</ul>
	</li>

	<li><a href="#">Project Links</a>
		<ul>
		<?
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
			<?
	
			echo getNavBarEntry('Toggle Calendar ', 'index.php?prefix=' . $project->getPrefix() . '&amp;tabel=' . $tabel .'&amp;datum=' . $datum. '&amp;mode=' . $mode  . '&amp;naam=' . rawurlencode($naam) . '&amp;cActive=' . ($cActive=='on'?'off':'on') . '&amp;setCActive=&amp;team=' . rawurlencode($team) );

			echo getNavBarEntry('Toggle Daily Top\'s','index.php?prefix=' . $project->getPrefix() . '&amp;tabel=' . $tabel . '&amp;datum=' . $datum . '&amp;mode=' . $mode . '&amp;naam=' . rawurlencode($naam) . '&amp;dtActive=' . ($dtActive=='on'?'off':'on') . '&amp;setDtActive=&amp;teams=' . $teams );
			
			echo getNavBarEntry('Toggle Shoutbox','index.php?prefix=' . $project->getPrefix() . '&amp;tabel=' . $tabel . '&amp;datum=' . $datum . '&amp;mode=' . $mode . '&amp;naam=' . rawurlencode($naam) . '&amp;sbActive=' . ($sbActive=='on'?'off':'on') . '&amp;setSbActive=&amp;teams=' . $teams );

			?>
			
		</ul>
	</li>
</ul>
