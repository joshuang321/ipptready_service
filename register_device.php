<?php
	require_once('../creds.php');
	$data = json_decode(file_get_contents('php://input'), true);
	$response = array('Response' => null);

	if (null == $data)
	{
		$response['Response'] = 'Failure';
		$response += ['ErrorMessage' => 'Invalid parameters!'];
	}
	else if (array_key_exists('RegisterId', $data) && array_key_exists('IPPTUserId', $data))
	{
		try {
			$conn = new PDO('mysql:host=localhost;dbname=RoutineAlarm', $username,
				$password);
			$sqlq = 'INSERT INTO user_devices(user_id, registration_id)
				VALUES(\''. $data['IPPTUserId'] .'\', \''. $data['RegisterId']. '\')';
			$conn->exec($sqlq);
			$conn = null;
			$response['Response'] = 'Successful';
		}
		catch (PDOException $e) {
			$response['Response'] = 'Failure';
			$response += ['ErrorMessage' => $e->getMessage()];
		}
	}
	else {
		$response['Response'] = 'Failure';
	}
	header('Content-Type: application/json');
	echo json_encode($response);
?>
