<?php

class q2a_stepmail_question_db_client
{
  public static function getQuestionCount($userid)
	{
		$sql = "select count(postid) as round from qa_posts where userid=" . $userid . " and type='Q'";

		$result = qa_db_query_sub($sql);
		return qa_db_read_all_assoc($result);
	}

  public static function getUserInfo($userid)
  {
	  $sql = 'select email,handle from qa_users where userid=' . $userid;
    $result = qa_db_query_sub($sql);
    $arr = qa_db_read_all_assoc($result);
    return $arr[0];
  }
}
