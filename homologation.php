<?php
	/**
	 * Homologation process for Registro.br
	 *
	 * Each step is required for homologation, and you have to execute in the following order.
	 * After executing all steps, contact Registro.br and let them know what time(day and hour)
	 * you proceeded.
	 *
	 */

	/**
	 * Step One
	 *
	 * Just login, change password and logout
	 */
	$epp = new Epp();

	$epp->connect();

	$epp->login('0001', 'YOURPASSWORD', 'YOURNEWPASSWORD');

	print_r($epp->hello());

	$epp->logout();

	/**
	 * Step Two
	 *
	 * Connect and execute some simple insertion and updates.
	 *
	 * Important: Once logged, execute steps Two to Nine without logoff!
	 */
	$epp = new Epp();

	$epp->connect();

	$epp->login('0001', 'YOURPASSWORD');

	print '<pre>'; print_r($epp->contact_create('José da Silva', 'Rua das Laranjeiras', '100', 'São Paulo', 'SP', '02127-000', 'BR', '+55.1122222222', 'teste@teste.com'));
	print '<pre>'; print_r($epp->contact_info('JOSIL44'));
	print '<pre>'; print_r($epp->contact_update('JOSIL44', 'Rua das Figueiras', '200', 'São Paulo', 'SP', '01311-100', 'BR', '+55.1133333333', 'teste@teste.com.br'));

	/* Step Three */
	print '<pre>'; print_r($epp->org_check('246.838.523-30'));
	print '<pre>'; print_r($epp->org_create('246.838.523-30', 'José da Silva', 'Rua das Figueiras', '200', 'São Paulo', 'SP', '01311-100', 'BR', '+55.1133333333', 'teste@teste.com.br', 'JOSIL44', 'José da Silva'));
	print '<pre>'; print_r($epp->org_info('246.838.523-30'));


	/* Step Four */
	print '<pre>'; print_r($epp->domain_check('yoursite6.com.br'));
	print '<pre>'; print_r($epp->domain_create('yoursite6.com.br', 1, 'ns1.yoursite-idc.net', 'ns2.yoursite-idc.net', '246.838.523-30', 0)); //Wait 15 minutes and change the temp_array in the function
	print '<pre>'; print_r($epp->domain_info('6661', 'yoursite6.com.br'));
	print '<pre>'; print_r($epp->domain_update('6661', 'yoursite6.com.br', 'ns1.yoursite-idc.net', 'ns2.yoursite-idc.net', null, 0)); //Wait 15 minutes and change the temp_array in the function

	/* Step Five */
	print '<pre>'; print_r($epp->domain_check('yoursite7.com.br'));
	print '<pre>'; print_r($epp->domain_create('yoursite7.com.br', 1, 'ns1.yoursite-idc.net', 'ns2.yoursite-idc.net', '246.838.523-30', 0)); //Wait 15 minutes


	/* Step Six */
	print '<pre>'; print_r($epp->poll_request());
	print '<pre>'; print_r($epp->poll_delete(8314));

	/* Step Seven */
	print '<pre>'; print_r($epp->org_update('246.838.523-30', 'Rua das Laranjeiras', '300', 'São Paulo', 'SP', '04209-004', 'BR', '+55.1144444444'));
	print '<pre>'; print_r($epp->org_info('246.838.523-30'));
	print '<pre>'; print_r($epp->domain_info('', 'yoursite7.com.br'));
	print '<pre>'; print_r($epp->domain_renew('yoursite7.com.br', '2012-02-07T21:33:17.0Z', 1));

	/* Step Eight */
	print '<pre>'; print_r($epp->contact_create('Amanda da Silva', 'Rua das Laranjeiras', '100', 'São Paulo', 'SP', '02127-000', 'BR', '+55.1122222222', 'teste@teste.com'));
	print '<pre>'; print_r($epp->domain_update('', 'yoursite7.com.br', 'ns1.yoursite-idc.net', 'ns2.yoursite-idc.net', 'AMSIL', '246.838.523-30', 0));
	print '<pre>'; print_r($epp->domain_update('', 'yoursite7.com.br', 'ns1.yoursite-idc.net', 'ns2.yoursite-idc.net', 'AMSIL', '246.838.523-30', 1)); //Automatic renew
	print '<pre>'; print_r($epp->domain_update('', 'yoursite7.com.br', 'ns1.yoursite-idc.net', 'ns2.yoursite-idc.net', 'AMSIL', '246.838.523-30', 0)); //No automatic renew

	/* Step Nine */
	print '<pre>'; print_r($epp->domain_delete('yoursite7.com.br'));
?>