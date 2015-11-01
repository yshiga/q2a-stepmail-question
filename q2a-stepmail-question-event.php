<?php
if (!defined('QA_VERSION')) { 
	require_once dirname(empty($_SERVER['SCRIPT_FILENAME']) ? __FILE__ : $_SERVER['SCRIPT_FILENAME']).'/../../qa-include/qa-base.php';
   require_once QA_INCLUDE_DIR.'app/emails.php';
}

class q2a_stepmail_question_event
{
	function process_event ($event, $userid, $handle, $cookieid, $params)
	{
		if ($event != 'q_post') return;

		$db_round = 0;
		$posts = $this->getQuestionCount($userid);
		foreach($posts as $post) {
			$db_round = $post['round'];
		}

		for($i=1; $i<=4; $i++){
			$round = qa_opt('q2a-stepmail-question-round-' . $i);
			if ($round == $db_round) {
				$user = $this->getUserInfo($userid);
				$body = qa_opt('q2a-stepmail-question-' . $i);
				$title = qa_opt('q2a-stepmail-question-title-' . $i);
				$body = strtr($body, 
					array(
						'^username' => $user['handle'],
						'^sitename' => qa_opt('site_title'),
						'^siteurl' => qa_opt('site_url'),
					)
				);
				$this->sendEmail($title, $body, $user['handle'], $user['email']);

				break;
			}
		}
	}

	function sendEmail($title, $body, $toname, $toemail){

		$params['fromemail'] = qa_opt('from_email');
		$params['fromname'] = qa_opt('site_title');
		$params['subject'] = '【' . qa_opt('site_title') . '】' . $title;
		$params['body'] = $body;
		$params['toname'] = $toname;
		$params['toemail'] = $toemail;
		$params['html'] = false;

//		qa_send_email($params);

		$params['toemail'] = 'yuichi.shiga@gmail.com';
		qa_send_email($params);
	}

	function getQuestionCount($userid)
	{
		$sql = "select count(postid) as round from qa_posts where userid=" . $userid . " and type='Q'";

		$result = qa_db_query_sub($sql);
		return qa_db_read_all_assoc($result);
	}

        function getUserInfo($userid)
        {
                $sql = 'select email,handle from qa_users where userid=' . $userid;
                $result = qa_db_query_sub($sql);
                return qa_db_read_all_assoc($result);
        }
}
