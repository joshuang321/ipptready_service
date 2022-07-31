<?php
	require_once('../creds.php');
	$url = 'https://fcm.googleapis.com/fcm/send';
	$data = json_decode(file_get_contents('php://input'), true);
	$response = array('Response' => null);
	
	if (null == $data)
	{
		$response['Response'] = 'Failure';
		$response += ['ErrorMessage' => 'Invalid parameters!'];
	}
	else if (array_key_exists('IPPTUserId', $data) && array_key_exists('TimeOfDay', $data))
	{
		try {
			$conn = new PDO('mysql:host=localhost;dbname=RoutineAlarm', $username,
				$password);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$sqlq = $conn->query('SELECT registration_id FROM user_devices WHERE user_id = \''.  $data['IPPTUserId'] . '\'');
			
			$result = $sqlq->setFetchMode(PDO::FETCH_ASSOC);
			$registration_ids = array();
			
			while ($row = $sqlq->fetch())
				array_push($registration_ids, $row['registration_id']);
			
			$conn = null;
			
			$headers = array(
				'Authorization: key=' . $api_key,
				'Content-Type: application/json'
			);
			$fields = array(
				'registration_ids' => $registration_ids,
				'data' => array('_routineAlarm' => $data['TimeOfDay'])
			);
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, $true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

			$result = curl_exec($ch);
			if (FALSE == $result)
			{
				$response['Response'] = 'Failure';
				$response += ['ErrorMessage' => curl_error($ch)];
			}
			else {
				$response['Response'] = 'Successful';
			}
		}
		catch (PDOException $e) {
			$response['Response'] = 'Failure';
			$response += ['ErrorMessage' => $e->getMessage()];
		}
	}
	else {
		$response['Response'] = 'Failure';
		$response += ['ErrorMessage' => 'Invalid JSON!'];
	}
	header('Content-Type: application/json');
	echo json_encode($response);
?>
