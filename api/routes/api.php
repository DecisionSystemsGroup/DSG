<?php
	require 'functions.php';

	/**
	*	GET ALL SCORES
	**/
	$app->get('/api/v1/scores/', function () use ($app, $db){
		$stmt = $db->prepare("SELECT `id`,`username`,`year`,`month`,`gen_score`,`placeholder1`,`placeholder2`,`placeholder3` FROM `raw_scores` WHERE 1");
		$stmt->execute();

		$stmt->bind_result($score_id, $score_username, $score_year, $score_month, $score_gen_score, $score_placeholder1, $score_placeholder2, $score_placeholder3);

		$response = array();
		$response['scores'] = array();
		while($stmt->fetch()){
			$score = array();	//reinit the array just in case

			$score['id'] = $score_id;
			$score['username'] = $score_username;
			$score['year'] = $score_year;
			$score['month'] = $score_month;
			$score['gen_score'] = $score_gen_score;
			$score['placeholder1'] = $score_placeholder1;
			$score['placeholder2'] = $score_placeholder2;
			$score['placeholder3'] = $score_placeholder3;

			$response['scores'][$score_month][] = $score;
		}
		$stmt->close();
		for($i=0; $i<count($response['scores']); $i++){
			usort($response['scores'][$i+1], 'sortByScore');
		}

		$app->response->setStatus(200);
		$response['success'] = true;
		$response = json_encode($response, JSON_UNESCAPED_UNICODE);
		$app->response->setBody($response);
	});

	$app->get('/api/v1/rewards/', function () use ($app, $db){
		$stmt = $db->prepare("SELECT `id`, `name`, `url`, `thumb`, `rank`, `claimed_by` FROM `rewards` WHERE 1");
		$stmt->execute();

		$stmt->bind_result($reward_id, $name, $url, $thumb, $rank, $claimed_by);

		$response = array();
		$response['rewards'] = array();
		while($stmt->fetch()){
			$reward = array();	//reinit the array just in case

			$reward['id'] = $reward_id;
			$reward['name'] = $name;
			$reward['url'] = $url;
			$reward['thumb'] = $thumb;
			$reward['rank'] = $rank;
			$reward['claimed_by'] = $claimed_by;

			$response['rewards'][] = $reward;
		}
		$stmt->close();

		$app->response->setStatus(200);
		$response['success'] = true;
		$response = json_encode($response, JSON_UNESCAPED_UNICODE);
		$app->response->setBody($response);
	});

	/**
	*	GET USER RANK INFO FOR A USER FOR A GIVEN MONTH
	**/
	$app->get('/api/v1/users/:username/month/:month/rank/', function($req_username, $req_month) use ($app, $db){
		$app->response->setStatus(200);
		$response['success'] = true;
		$response['rank'] = getUserRankObject($db, $req_username, $req_month);

		$previousMonthRankObj = getUserRankObject($db, $req_username, $req_month-1);

		$response['rank']['scoreUp'] = ($response['rank']['gen_score']-$previousMonthRankObj['gen_score'])>0;
		$response['rank']['scoreChange'] = abs($response['rank']['gen_score']-$previousMonthRankObj['gen_score']);

		$response = json_encode($response, JSON_UNESCAPED_UNICODE);
		$app->response->setBody($response);
	});
?>
