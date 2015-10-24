<?php
if (!defined('QA_VERSION')) { 
	require_once dirname(empty($_SERVER['SCRIPT_FILENAME']) ? __FILE__ : $_SERVER['SCRIPT_FILENAME']).'/../../qa-include/qa-base.php';
   require_once QA_INCLUDE_DIR.'app/emails.php';
}

// for localtest START
/*************************
qa_opt('q2a-stepmail-question-1', 'question step1 mail body.');
qa_opt('q2a-stepmail-question-2', 'question step2 mail body.');
qa_opt('q2a-stepmail-question-3', 'question step3 mail body.');
qa_opt('q2a-stepmail-question-4', 'question step4 mail body.');
qa_opt('q2a-stepmail-question-title-1', 'question step1 mail title');
qa_opt('q2a-stepmail-question-title-2', 'question step2 mail title');
qa_opt('q2a-stepmail-question-title-3', 'question step3 mail title');
qa_opt('q2a-stepmail-question-title-4', 'question step4 mail title');
qa_opt('q2a-stepmail-question-round-1', 3);
qa_opt('q2a-stepmail-question-round-2', 10);
qa_opt('q2a-stepmail-question-round-3', 20);
qa_opt('q2a-stepmail-question-round-4', 30);
***************************/
// for localtest END

class q2a_stepmail_question_event
{
	function process_event ($event, $userid, $handle, $cookieid, $params)
	{
		if ($event != 'q_post')
			return;


		$db_round = 0;
		$posts = $this->getQuestionCount($userid);
		foreach($posts as $post) {
			$db_round = $post['round'];
		}
// for localtest START
/*************************
$fp = fopen("/tmp/plugin05.log", "a+");
$outs = "db_round:".$db_round."\n";
fputs($fp, $outs);
fclose($fp);
***************************/
// for localtest END
		for($i=1; $i<=4; $i++){
			$round = qa_opt('q2a-stepmail-question-round-' . $i);
// for localtest START
/*************************
$fp = fopen("/tmp/plugin05.log", "a+");
$outs = "(".$i.") round:".$round."\n";
fputs($fp, $outs);
fclose($fp);
***************************/
// for localtest END
			if ($round == $db_round) {
				$user = $this->getUserInfo($userid);
				$body = qa_opt('q2a-stepmail-question-' . $i);
				$title = qa_opt('q2a-stepmail-question-title-' . $i);
				$body = strtr($body, 
					array(
						'^username' => $user['handle'],
						'^sitename' => qa_opt('site_title')
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
// for debug START
/***********************************
$fp = fopen("/tmp/plugin05.log", "a+");
$outs = "fromemail:".$mail_params['fromemail']."\n";
fputs($fp, $outs);
$outs = "fromname:".$mail_params['fromname'] . "\n";
fputs($fp, $outs);
$outs = "subject:".$mail_params['subject'] . "\n";
fputs($fp, $outs);
$outs = "body:".$mail_params['body'] . "\n";
fputs($fp, $outs);
$outs = "toname:".$mail_params['toname'] . "\n";
fputs($fp, $outs);
$outs = "toemail:".$mail_params['toemail'] . "\n";
fputs($fp, $outs);
fclose($fp);
**********************************/
// for debug END

		qa_send_email($params);

		//$params['toemail'] = 'yuichi.shiga@gmail.com';
		$params['toemail'] = 'ryuta_takeyama@nexyzbb.ne.jp';
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
