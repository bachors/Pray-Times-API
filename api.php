<?php

	// Example API by iBacor.
	
	include_once('PrayTime.php');
	
	$result = array();
	
	// Calculation Methods:
	$calculation = array(
		array(
			'name' => 'Shia Ithna-Ashari',
			'value' => 0
		),array(
			'name' => 'University of Islamic Sciences, Karachi',
			'value' => 1
		),array(
			'name' => 'Islamic Society of North America (ISNA)',
			'value' => 2
		),array(
			'name' => 'Muslim World League (MWL)',
			'value' => 3
		),array(
			'name' => 'Umm al-Qura, Makkah',
			'value' => 4
		),array(
			'name' => 'Egyptian General Authority of Survey',
			'value' => 5
		),array(
			'name' => 'Custom Setting',
			'value' => 6
		),array(
			'name' => 'Institute of Geophysics, University of Tehran',
			'value' => 7
		)
	);
	
	function latlng($address){
		$address = preg_replace('/\s+/', '+', $address);
		$json = file_get_contents("https://maps.google.com/maps/api/geocode/json?address=".$address."&sensor=false");
		$array = json_decode($json);
		return array($array->results[0]->geometry->location->lat, $array->results[0]->geometry->location->lng);
	}
	
	if(!empty($_GET['address']) && isset($_GET['timezone']) && isset($_GET['method'])){
		if(is_numeric($_GET['method']) && in_array($_GET['method'], array(0,1,2,3,4,5,6,7))){
			$year = (!empty($_GET['year']) ? $_GET['year'] : date("Y"));
			$method = $_GET['method'];	
			$address = $_GET['address'];
			$timeZone = $_GET['timezone']; // Example: 7 = Waktu Indonesia Barat; 8 = Waktu Indonesia Tengah; 9 = Waktu Indonesia Timur;
			
			list($latitude, $longitude) = latlng($address);

			// prayTime Object
			$prayTime = new PrayTime($method);

			$date = strtotime($year. '-1-1');
			$endDate = strtotime(($year+ 1). '-1-1');
			
			$result['status'] = 'success';
			
			$result['data'] = array(
				'method' => $calculation[$method]['name'],
				'year' => $year,
				'address' => $address,
				'latitude' => $latitude,
				'longitude' => $longitude,
				'timezone' => $timeZone
			);

			while ($date < $endDate)
			{
				$times = $prayTime->getPrayerTimes($date, $latitude, $longitude, $timeZone);
				$day = date('M d', $date);
				
				$result['result'][] = array(
					'day' => $day,
					'time' => array(
						'fajr' => $times[0],
						'sunrise' => $times[1],
						'dhuhr' => $times[2],
						'asr' => $times[3],
						'sunset' => $times[4],
						'maghrib' => $times[5],
						'isha' => $times[6]
					)			
				);
				
				$date += 24* 60* 60;  // next day
			}
		}else{
			$result['status'] = 'error';
		}
	}else{
		$result['status'] = 'success';
		$result['method'] = $calculation;
	}
		
	header('Content-Type: application/json');
	header('Access-Control-Allow-Origin: *');
	echo json_encode($result, JSON_PRETTY_PRINT);

?>


