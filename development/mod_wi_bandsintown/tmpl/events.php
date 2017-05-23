<?php
<%= phpbanner %>



$tableClasses = $params->get('tableClass','');
if($params->get('responsiveTable','')==1) $tableClasses.= ' wi-bandsintown-responsive-table';
if($params->get('noStyling',0)==0) $tableClasses.= ' wi-bandsintown-table-styled';



$lastCol = array();

$lastCol['RSVPLink'] 	= ($params->get('showRSVPLink',0)=='1');
$lastCol['TicketLink'] 	= (!$lastCol['RSVPLink'] && $params->get('showTicketLink',0)=='1');
$lastCol['Lineup'] 		= (!$lastCol['RSVPLink'] && !$lastCol['TicketLink'] && $params->get('showLineup',0)=='1' && $params->get('lineupPosition','below')=='col');
$lastCol['Location']	= (!$lastCol['RSVPLink'] && !$lastCol['TicketLink'] && !$lastCol['Lineup']);

if($wi_bit->isAdmin)
	foreach($lastCol as $k=>$v) {
		$lastCol[$k] = '';
	}
else
	foreach($lastCol as $k=>$v) {
		$lastCol[$k] = $v ? 'last' : '';
	}


?>


<div class="wi-bandsintown" >
	<table class="wi-bandsintown-table <?php echo $tableClasses; ?>" >


		<colgroup>
			<col width="20">
			<col width="60">

			<?php if($params->get('showEventTime',1)=='1'): ?>
			<col width="70">
			<?php endif; ?>

			<col width="400" class="<?php echo $lastCol['Location'] ?>">

			<?php if($params->get('showLineup',1)=='1' && $params->get('lineupPosition','below')=='col'): ?>
			<col width="400" class="<?php echo $lastCol['Lineup'] ?>">
			<?php endif; ?>

			<?php if($params->get('showTicketLink',0)=='1'): ?>
			<col width="50" class="<?php echo $lastCol['TicketLink'] ?>">
			<?php endif; ?>

			<?php if($params->get('showRSVPLink',0)=='1'): ?>
			<col width="50" class="<?php echo $lastCol['RSVPLink'] ?>">
			<?php endif; ?>

			<?php if($wi_bit->isAdmin): ?>
			<col width="30" class="last wi-bandsintown-toggle-event">
			<?php endif; ?>

			<col width="20">
		</colgroup>

		<tr>
			<th class="padding"></th>
			<th><?php echo JText::_('MOD_WI_BANDSINTOWN_HEADER_DATE') ?></th>

			<?php if($params->get('showEventTime',1)=='1'): ?>
			<th><?php echo JText::_('MOD_WI_BANDSINTOWN_HEADER_TIME') ?></th>
			<?php endif; ?>

			<th class="<?php echo $lastCol['Location'] ?>"><?php echo JText::_('MOD_WI_BANDSINTOWN_HEADER_LOCATION') ?></th>

			<?php if($params->get('showLineup',1)=='1' && $params->get('lineupPosition','below')=='col'): ?>
			<th class="<?php echo $lastCol['Lineup'] ?>"><?php echo JText::_('MOD_WI_BANDSINTOWN_HEADER_LINEUP') ?></th>
			<?php endif; ?>

			<?php if($params->get('showTicketLink',0)=='1'): ?>
			<th class="<?php echo $lastCol['TicketLink'] ?>"><?php echo JText::_('MOD_WI_BANDSINTOWN_HEADER_TICKETS') ?></th>
			<?php endif; ?>

			<?php if($params->get('showRSVPLink',0)=='1'): ?>
			<th class="<?php echo $lastCol['RSVPLink'] ?>"><?php echo JText::_('MOD_WI_BANDSINTOWN_HEADER_RSVP') ?></th>
			<?php endif; ?>

			<?php if($wi_bit->isAdmin): ?>
			<th class="last wi-bandsintown-toggle-event">Toggle</th>
			<?php endif; ?>

			<th class="padding"></th>
		</tr>

		<?php foreach($events as $i=>$e): ?>
		<tr class="<?php echo ($wi_bit->isAdmin && $e->visible==0) ? 'hidden-event' : ''; ?>" itemscope itemtype="http://schema.org/<?php echo $params->get('eventType','MusicEvent') ?>">

			<td class="padding"><div class="uk-hidden" itemprop="name" ><?php echo $e->title; ?></div></td>

			<td data-title="<?php echo JText::_('MOD_WI_BANDSINTOWN_HEADER_DATE') ?>">
				<meta itemprop="startDate" content="2016-04-01T12:00">
				<?php echo $e->formattedDate; ?>
			</td>

			<?php if($params->get('showEventTime',1)=='1'): ?>
			<td data-title="<?php echo JText::_('MOD_WI_BANDSINTOWN_HEADER_TIME') ?>">
				<?php echo $e->formattedTime; ?>
			</td>
			<?php endif; ?>

			<td data-title="<?php echo JText::_('MOD_WI_BANDSINTOWN_HEADER_LOCATION') ?>" class="<?php echo $lastCol['Location'] ?>"
				 	itemprop="location" itemscope itemtype="http://schema.org/Place">
				<span itemprop="name"><?php echo $e->venue->name; ?> </span>
				<meta itemprop="address" content="<?php echo $e->venue->name; ?>, <?php echo $e->venue->city; ?>, <?php echo $e->venue->country; ?>">
				(<?php echo $e->venue->city; ?>, <?php echo $e->venue->country; ?>)

				<?php if($params->get('showLineup',1)=='1' && $params->get('lineupPosition','below')=='below' && strlen($e->formattedLineup)): ?>
				<div>
					<span class="wi-lineup-below"><?php echo JText::_('MOD_WI_BANDSINTOWN_LINEUP') ?>: <?php echo $e->formattedLineup; ?></span>
				</div>
				<?php endif; ?>
			</td>

			<?php if($params->get('showLineup',1)=='1' && $params->get('lineupPosition','below')=='col'): ?>
			<td data-title="<?php echo JText::_('MOD_WI_BANDSINTOWN_HEADER_LINEUP') ?>" class="<?php echo $lastCol['Lineup'] ?>">
				<?php if(strlen($e->formattedLineup)): ?>
				<span class="wi-lineup-col"><?php echo $e->formattedLineup; ?></span>
				<?php endif; ?>
			</td>
			<?php endif; ?>

			<?php if($params->get('showTicketLink',0)=='1'): ?>
			<td data-title="<?php echo JText::_('MOD_WI_BANDSINTOWN_HEADER_TICKETS') ?>" class="<?php echo $lastCol['TicketLink'] ?>">
				<?php if($e->ticket_url): ?>
				<a class="wi-bandsintown-button wi-bandsintown-button-tickets" target="_blank" href="<?php echo $e->ticket_url; ?>">Tickets</a>
				<?php endif; ?>
			</td>
			<?php endif; ?>

			<?php if($params->get('showRSVPLink',0)=='1'): ?>
			<td data-title="<?php echo JText::_('MOD_WI_BANDSINTOWN_HEADER_RSVP') ?>" class="<?php echo $lastCol['RSVPLink'] ?>">
				<?php if($e->facebook_rsvp_url): ?>
				<a class="wi-bandsintown-button wi-bandsintown-button-rsvp" target="_blank" href="<?php echo $e->facebook_rsvp_url; ?>">RSVP</a>
				<?php endif; ?>
			</td>
			<?php endif; ?>

			<?php if($wi_bit->isAdmin): ?>
			<td data-title="">
				<a class="wi-bandsintown-button wi-bandsintown-button-rsvp" href="<?php echo $e->toggleVisibilityUrl; ?>" itemprop="url">
					<?php echo ($e->visible==1) ? 'hide' : 'show'; ?>
				</a>
			</td>
			<?php endif; ?>

			<td class="padding"></td>
		</tr>
		<?php endforeach; ?>

	</table>
</div>
