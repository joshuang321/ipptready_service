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
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$sqlq = $conn->query('SELECT user_id, registration_id FROM user_devices WHERE user_id = \''.
				$data['IPPTUserId'] .'\' AND registration_id = \''. $data['RegisterId'] . '\'');
			$result = $sqlq->setFetchMode(PDO::FETCH_ASSOC);
			$isAdded = false;
			
			while ($row = $sqlq->fetch())
			{
				if ($row['user_id'] == $data['IPPTUserId'] && $row['registration_id'] == $data['RegisterId'])
					$isAdded = true;
			}
					
			if (!$isAdded)
			{
				$conn->exec('INSERT INTO user_devices(user_id, registration_id) VALUES(\''.
					$data['IPPTUserId'] .'\', \''. $data['RegisterId']. '\')');
				$conn = null;
			}
			$response['Response'] = 'Successful';
			$response += ['isFirstTime' => !$isAdded];
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
