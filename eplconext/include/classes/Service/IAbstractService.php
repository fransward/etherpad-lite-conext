<?php

abstract class EPLc_Service_IAbstractService {
	
	/* worker function of a service class */
	abstract function perform($userinfo, $groupinfo, $args);
}