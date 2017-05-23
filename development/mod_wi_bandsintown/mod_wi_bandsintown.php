<?php
<%= phpbanner %>

require_once 'wi_bandsintown.php';

$errorMsg = false;
$doc = JFactory::GetDocument();

$wi_bit = new wi_bandsintown();
$wi_bit->params = $params;
$wi_bit->cache = JFactory::getCache('mod_wi_bandsintown', '', 'file');
$wi_bit->cache->setLifeTime($params->get('cachingDuration', 30));
$wi_bit->cache->setCaching(($params->get('cachingEnable', 0)==1));

$wi_bit->initialize();
$wi_bit->executeTask();
$events = $wi_bit->getData();

if(!$wi_bit->error) {
	if(count($events)) {

		$events = $wi_bit->formatEventsData($events);

		#$responsiveTable = $params->get('responsiveTable', 1);
		#$noStyling = $params->get('noStyling', 1);
		#if($responsiveTable || !$noStyling) { }
		$doc->addStylesheet('/modules/mod_wi_bandsintown/assets/wi_bandsintown.css');

		require JModuleHelper::getLayoutPath('mod_wi_bandsintown', 'events');

	} else {

		# empty?
		require JModuleHelper::getLayoutPath('mod_wi_bandsintown', 'nothingtoshow');

	}
}
