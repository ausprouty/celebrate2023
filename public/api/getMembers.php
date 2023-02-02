<?php

function getMembers($params){
	if (!isset($params['tid'])){
		 $out['debug'] = 'tid not set in getMembers';
		return $out;
	}
	$members = [];
	$out['debug'] = $params['tid'] . "\n";
    $sql = 'SELECT u.* FROM users as u
      INNER JOIN members as m
      ON u.uid = m.uid
      WHERE tid = :tid
      AND m.date_stopped IS NULL
      ORDER BY firstname';
	$data= array(
		'tid'=> $params['tid']
	);
	$data= array(
		'tid'=> $params['tid']
	);
	$out['debug'] .= $sql . "\n";
    $result = sqlReturnObjectMany($sql, $data);
	foreach ($result as $member){
		unset($member->email);
		unset($member->password);
		$members[] = $member;
	}
    $out['content'] = $members;
    return $out;
}