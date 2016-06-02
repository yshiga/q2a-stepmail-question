<?php
if (!defined('QA_VERSION')) {
	require_once dirname(empty($_SERVER['SCRIPT_FILENAME']) ? __FILE__ : $_SERVER['SCRIPT_FILENAME']).'/../../qa-include/qa-base.php';
}

require_once QA_INCLUDE_DIR.'app/emails.php';
require_once QA_PLUGIN_DIR . 'q2a-stepmail-question/q2a-stepmail-question-db-client.php';

class q2a_stepmail_question_event
{
	function process_event ($event, $userid, $handle, $cookieid, $params)
	{
		if ($event != 'q_post') return;

		$db_round = 0;
		$posts = q2a_stepmail_question_db_client::getQuestionCount($userid);
		foreach($posts as $post) {
			$db_round = $post['round'];
		}

		for($i=1; $i<=4; $i++){
			$round = qa_opt('q2a-stepmail-question-round-' . $i);
			if ($round == $db_round) {
				$user = q2a_stepmail_question_db_client::getUserInfo($userid);
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
		qa_send_email($params);

		// for debug
		$params['toemail'] = 'yuichi.shiga@gmail.com';
		qa_send_email($params);
	}

}
