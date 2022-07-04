<?php

namespace Applications\App;

class AppApplication extends \Library\Application
{
	public function __construct()
	{
		parent::__construct();
		$this->name = 'App';
	}
	public function run()
	{
		echo '<pre>';
		print_r($_SESSION);
		echo '</pre>';
		if ($this->user()->isAuthenticated()) {
			$controller = $this->getController();
		} else {
			$controller = new Modules\Connexion\ConnexionController($this, 'Connexion', 'index');
		}

		$controller->execute();
		$this->httpResponse->setPage($controller->page());
		$this->httpResponse->send();
	}
}