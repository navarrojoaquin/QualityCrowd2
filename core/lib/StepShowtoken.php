<?php

class StepShowtoken extends Step
{
	protected function init() {}
	
	protected function prepareRender()
	{
		$meta = $this->store->readWorker('meta', '', $this->batchId, $this->workerId);
		$this->tpl->set('token', $meta['token']);
	}

	public function validate(&$data) 
	{
		return true;
	}
}
