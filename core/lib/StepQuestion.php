<?php

// this class handles the step commands "video", "image" and "question"

class StepQuestion extends Step
{
	protected function init() 
	{
		
	}

	public function validate(&$data) 
	{
		$msg = array();

		if (!$this->properties['skipvalidation']) {
			if ($this->command == 'video' && 
				(!isset($data['watched']) || $data['watched'] <> true))
			{
				$msg[] = 'You have to watch the whole video.';
			}
			if (!isset($data['answered']) || $data['answered'] <> true) 
			{
				$msg[] = 'You have to answer the question.';
			}
		}

		if (count($msg) == 0)
		{
			unset($data['watched']);
			unset($data['answered']);
			$data['media'] = (isset($this->arguments[0]) ? $this->arguments[0] : null);
			return true;
		} else 
		{
			return $msg;
		}
	}

	protected function prepareRender()
	{
		switch($this->command)
		{
			case 'video': $this->prepareVideo(); break;
			case 'image': $this->prepareImage(); break;
		}

		$this->prepareAnswers();
	}

	private function prepareImage()
	{
		$img = $this->arguments[0];
		$this->tpl->set('image', $this->properties['mediaurl'] . $img);
	}

	private function prepareVideo()
	{
		// prerender video players
		$videos = array();

		foreach ($this->arguments as $video)
		{
			$tpl = new Template('player.videojs', $this->batchId);
			$tpl->set('file', $this->properties['mediaurl'] . $video);
			$tpl->set('filename', $video);
			$tpl->set('width',  $this->properties['videowidth']);
			$tpl->set('height', $this->properties['videoheight']);
			$tpl->set('rebufferingsimulation', $this->properties['rebufferingsimulation']);
			$videos[$video] = $tpl->render();
		}

		$this->tpl->set('videos', $videos);
	}

	private function prepareAnswers()
	{
		// parse answers
		$answerStr = $this->properties['answers'];
		$answerStr = explode(';', $answerStr);

		$answers = array();
		foreach ($answerStr as $str)
		{
			$str = explode(':', $str);

			$answers[] = array(
				'value' => trim($str[0]),
				'text' => trim($str[1]),
			);
		}

		// set answer template
		$answermode = $this->properties['answermode'];
		if (!Template::exists('answer.' . $answermode)) {
			$answermode = 'continous';
		}

		$tpl = new Template('answer.' . $answermode, $this->batchId);
		$tpl->set('answers', $answers);
		$answerform = $tpl->render();
		$this->tpl->set('answerform', $answerform);
	}
}
