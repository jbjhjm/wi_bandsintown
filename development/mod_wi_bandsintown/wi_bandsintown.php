<?php
<%= phpbanner %>



class wi_bandsintown {

	var $params;
	var $error = false;
	var $errorMsg;
	var $cache;

	function initialize() {

		$this->user = JFactory::GetUser();
		$this->isAdmin = (in_array(7,$this->user->groups) || in_array(8,$this->user->groups)); # 7=admin, 8=SU

	}

	function executeTask() {

		$task = JRequest::GetVar('wi_bandsintown_task',false);
		if(!$task) return;
		if($task == 'toggle') $this->task_toggle();
		if($task == 'reload') $this->task_reload();

	}

	function task_toggle() {

		$id = (int)JRequest::GetVar('wi_bandsintown_id',false);
		if(!$this->isAdmin || !$id) return;


		$db = JFactory::GetDbo();
		$query = 'UPDATE `#__wi_bandsintown_events` SET visible=IF(visible=1, 0, 1) WHERE id='.$id.' LIMIT 1';
		$db->setQuery($query);
		$db->execute();

		JFactory::GetApplication()->redirect(JURI::Current(), 'Event toggled successfully!', 'success');

		return $storedEvents;

	}

	function task_reload() {

		$id = (int)JRequest::GetVar('wi_bandsintown_id',false);
		if(!$this->isAdmin || !$id) return;


		$db = JFactory::GetDbo();
		$query = 'DELETE FROM `#__wi_bandsintown_events` WHERE id='.$id.' LIMIT 1';
		$db->setQuery($query);
		$db->execute();

		JFactory::GetApplication()->redirect(JURI::Current(), 'Event reloaded successfully!', 'success');

		return $storedEvents;

	}

	// main routine
	function getData() {

		$appId = $this->params->get('appId', false);
		$artistName = $this->params->get('artistName', false);

		if(!$appId) {
			return $this->error('no app id');
		}
		if(!$artistName) {
			return $this->error('no artist name');
		}


		// check if it is time to refresh data...
		if ($data = $this->cache->get('bandsintown_eventdata_'.$this->cleanWord($artistName))) {
			// get events from local db table.
		} else {
			// reload and update events from Bandsintown Servers.
			$data = $this->loadEventsFromBandsintown();
			if($data && count($data)) $this->saveEventsToDatabase($data);
		}

		$data = $this->loadEventsFromDatabase();
		foreach($data as $i=>$row) {
			$data[$i]->offers = json_decode($row->offers);
			$data[$i]->lineup = json_decode($row->lineup);
			$data[$i]->venue = json_decode($row->venue);
		}

		return $data;

	}



	function loadEventsFromBandsintown() {

		#echo 'need to reload data now.';

		$appId = $this->params->get('appId', false);
		$artistName = $this->params->get('artistName', false);

		$reqData = array();
		$reqData['app_id'] = $appId;
		// $reqData['api_version'] = '2.0';
		$urlData = array();

		foreach($reqData as $k=>$v) $urlData[] = $k.'='.rawurlencode($v);


		# http://api.bandsintown.com/artists/Skrillex.json?api_version=2.0&app_id=YOUR_APP_ID -- artist information
		# http://api.bandsintown.com/artists/Skrillex/events.json?api_version=2.0&app_id=YOUR_APP_ID -- event data

		$this->url = 'http://rest.bandsintown.com/artists/'.rawurlencode($artistName).'/events?'.implode('&',$urlData);

		#  jimport('joomla.http');
		# response = {code,headers,body}
		$options = new JRegistry();
		$http = new JHttp($options);

		$response = $http->get($this->url);
		if($response->code!=200) {
			return $this->error('response code was '.$response->code.' <br/> URL: '.$this->url);
		}

		$data = $response->body;
		$this->cache->store($data, 'bandsintown_eventdata_'.$this->cleanWord($artistName));


		$data = json_decode($data);

		if($data===false) return false;

		// var_dump($data);die;

		// if(!$data) {
		// 	return $this->error('response is no json!');
		// }
		if(!is_array($data) && $data->errors) {
			return $this->error('request error: '.implode(', ',$data->errors));
		}

		foreach($data as $i => $v) {
			// legacy transforms
			// $data[$i]->facebook_rsvp_url = $data[$i]->url;
		}

		return $data;

	}

	# load already saved events from local database.
	function loadEventsFromDatabase() {

		$timeRange = $this->params->get('timeRange', 'upcoming');
		switch($timeRange) {
			case 'past': 		$where = ' WHERE datetime < "'.date("Y-m-d H:i:s", time()).'" '; $order = ' ORDER BY datetime DESC '; break;
			case 'upcoming' :	$where = ' WHERE datetime > "'.date("Y-m-d H:i:s", time()).'" '; $order = ' ORDER BY datetime ASC '; break;
			case 'all' :		$where = ''; $order = ' ORDER BY datetime DESC '; break;
		}

		if(!$this->isAdmin) $where .= ' AND visible=1';

		$db = JFactory::GetDbo();
		$query = 'SELECT * FROM `#__wi_bandsintown_events` '.$where.$order;
		$db->setQuery($query);
		$storedEvents = $db->loadObjectList();

		return $storedEvents;

	}

	# save/update events to/in database.
	function saveEventsToDatabase($data) {

		$db = JFactory::GetDbo();
		if(!$data || !count($data)) return;

		$loadedIDs = array();
		foreach($data as $event) {
			$loadedIDs[] = (int)$event->id;
		}

		$query = 'SELECT * FROM `#__wi_bandsintown_events` WHERE `bitid` IN('.implode(',',$loadedIDs).')';
		$db->setQuery($query);
		$storedEvents = $db->loadObjectList();

		$updateEvents = array();
		$insertEvents = array();

		foreach($data as $newEvent) {

			$dbRow = new StdClass();
			$dbRow->bitid 				= $newEvent->id;
			$dbRow->on_sale_datetime 	= $newEvent->on_sale_datetime;
			$dbRow->datetime 			= $newEvent->datetime;
			$dbRow->title 				= $newEvent->venue->name;
			$dbRow->offers 				= is_array($newEvent->offers) ? $newEvent->offers : array();
			$dbRow->lineup 				= is_array($newEvent->lineup) ? $newEvent->lineup : array();
			// $dbRow->ticket_url 			= $newEvent->ticket_url;
			// $dbRow->ticket_type 		= $newEvent->ticket_type;
			// $dbRow->ticket_status 		= $newEvent->ticket_status;
			$dbRow->url 				= $newEvent->url;
			$dbRow->description 		= $newEvent->description;
			$dbRow->venue 				= $newEvent->venue;

			if(!is_string($dbRow->offers)) $dbRow->offers = json_encode($dbRow->offers);
			if(!is_string($dbRow->lineup)) $dbRow->lineup = json_encode($dbRow->lineup);
			if(!is_string($dbRow->venue)) 	$dbRow->venue = json_encode($dbRow->venue);

			$foundIndex = false;
			foreach($storedEvents as $i=>$storedEvent) {
				if($newEvent->id == $storedEvent->bitid) {
					$foundIndex = true;
					break;
				}
			}
			if($foundIndex) {
				$updateEvents[] = $dbRow;
			} else {
				$insertEvents[] = $dbRow;
			}
		}

		#var_dump($updateEvents,$insertEvents);die;

		foreach($updateEvents as $e) {
			$db->updateObject('#__wi_bandsintown_events',$e,'bitid');
		}

		foreach($insertEvents as $e) {
			$db->insertObject('#__wi_bandsintown_events',$e);
		}


	}





	function formatEventsData($events) {

		$timeFormat = $this->params->get('timeFormat', 'H:i');
		$dateFormat = $this->params->get('dateFormat', 'd.m.Y');
		$lineupFormat = $this->params->get('lineupFormat', 'text');
		$lineupHideSelf = $this->params->get('lineupHideSelf', '1');
		$artistName = $this->params->get('artistName', false);

		foreach($events as $i=>$e) {

			$date = new DateTime($e->datetime);
			$e->formattedTime = $date->format($timeFormat);
			$e->formattedDate = $date->format($dateFormat);

			if($date->getTimestamp() < time()) {
				# past event!
				$e->facebook_rsvp_url = false;
				$e->url = false;
				$e->ticket_url = false;
				$e->offers = false;
			}

			$e->formattedLineup = array();
			foreach($e->lineup as $i2=>$a) {

				if(is_string($a)) {
					$e->formattedLineup[] = $a;
				} else {
					if($lineupHideSelf == '1' && $a->name == $artistName) continue;
					$artistInfo = '';

					if($lineupFormat=='image') $artistInfo = '<img src="'.$a->thumb_url.'" alt="'.$a->name.'" />';
					else $artistInfo = $a->name;

					if($lineupFormat!='text' && $a->facebook_tour_dates_url) $artistInfo =  '<a href="'.$a->facebook_tour_dates_url.'" target="_blank">'.$artistInfo.'</a>';

					$e->formattedLineup[] = $artistInfo;
				}

			}
			$e->formattedLineup = implode(', ',$e->formattedLineup);
			// if($lineupFormat!='image') $e->formattedLineup = implode(', ',$e->formattedLineup);
			// else $e->formattedLineup = implode(' ',$e->formattedLineup);

			if($this->isAdmin) {
				$e->admin = true;
				$e->toggleVisibilityUrl = JURI::Current().'?wi_bandsintown_task=toggle&wi_bandsintown_id='.$e->id;
				$e->toggleReloadUrl = JURI::Current().'?wi_bandsintown_task=reload&wi_bandsintown_id='.$e->id;
			}

			$events[$i] = $e;

		}

		return $events;

	}

	function cleanWord($word) {
		return preg_replace('/[^\w]/','_',$word);
	}

	function error($msg=false) {
		$this->error = true;
		if($msg) $this->errorMsg = $msg;
		require JModuleHelper::getLayoutPath('mod_wi_bandsintown', 'error');
		return false;
	}



}

?>
