<?php
	//	Copyright ©2023 by fiwswe
	//	All rights reserved.
	//	License: MIT
	//	The author assumes no warranties. USE AT YOUR OWN RISK!
	//
	//	Description:
	//	Dummycode written in PHP to perform an action in intervals that can be
	//	shorter than 60s. This code is meant to illustrate the important aspects
	//	of the basic structure. Many aspects are kept simple and very primitive
	//	on purpose.
	//
	//	Look for FIXME to see where your own modification should go.
	//
	//	Background:
	//	Often PHP is used for scripting things that need to run at regular intervals.
	//	On *NIX systems cron(8) is often used to trigger the script. However cron(8)
	//	only allows for a 1 minute (60 second) granularity when defining the interval.
	//	Somtimes an action needs to be performed more often.
	//
	//	Limitations:
	//	This code is not meant to be a daemon or a service (in the *NIX sense). It
	//	stays connected to its starting terminal. This script might be a starting
	//	point for developing a daemon though.
	//	When this script is launched automatically at e.g. system start, it will run
	//	until it is interupted by a signal or until it encounters a fatal error. There
	//	are no provisions for finding the running process or restarting it after a
	//	failure.
	//	PHP is a fairly heavy process to launch. OTOH the language is very flexible
	//	and allows good control of error handling, less obscure (IMHO) than e.g. shell
	//	scripts or perl. But using PHP is definitively a personal preference not
	//	everyone may agree with. If you don't then this code is not for you.
	//
	//	Requirements:
	//	PHP (version ≥7.4, lower version may also work)
	//	PHP extension pcntl
	//
	//	This code was tested with PHP 8.2.5, 8.1.18, 8.0.28 and 7.4.33 on OpenBSD 7.3.

	//
	//	MARK: Constants
	//

	//	The interval in seconds that defines how often the action is run.
	//	This is a floating point number so the interval does not need to be exact seconds.
	//	Generally the run time of the action should be shorter than the interval defined
	//	here. However the script will deal with time overruns by simply skipping intervals
	//	as necessary. Thus there is no guarantee that the action will be performed at each
	//	interval, only a best effort.
	define('kRunInterval',
		   10.0);			//	FIXME 


	//
	//	MARK: Error handling and loging
	//

	//	Private internal function to get a formatted timestamp string.
	//	Preferably this string will contain microseconds. But it will fall
	//	back to the microsecods having the value ".000".
	function _getTimestamp(): string
	{
        try {
			$d = new DateTimeImmutable();
			return $d->format(DATE_RFC3339_EXTENDED);
		}
		catch (Exception $e) {
			return date(DATE_RFC3339_EXTENDED);
		}
	}

	//	Very primitive log function that outputs the message to stdout:
	function logMsg(string $inMsg,
					string $inPrefix=null): void
	{
		echo $inPrefix._getTimestamp().' '.$inMsg.PHP_EOL;
	}

	//	Log an error.
	//	Note: This simple function logs to stdout not stderr.
	function logError(string $inErrorMsg): void
	{
		logMsg($inErrorMsg,
			   '### ERROR: ');
	}

	//	Log a fatal error and exit.
	//	Note: This simple function logs to stdout not stderr.
	//	Note: This function never returns!
	function fatalError(string $inErrorMsg): void
	{
		logMsg($inErrorMsg,
			   '### FATAL ERROR: ');
		exit(1);
	}


	//
	//	MARK: Basic infrastructure
	//

	function setupSignalHandlers(): void
	{
		//	Make sure the PHP pcntl extension is available:
		if (!function_exists('pcntl_async_signals'))
			fatalError('PHP pcntl functions not available!');

		//	Make sure async signal handling is turned on:
		$hasAsyncSignals = pcntl_async_signals();
		if (!$hasAsyncSignals) {
			//	Unless async signals are on no signal will be received!
			pcntl_async_signals(true);
		}

		if (!pcntl_signal(SIGTERM,'_handleSignal'))
			fatalError('Could not set SIGTERM handler!');
		if (!pcntl_signal(SIGQUIT,'_handleSignal'))
			fatalError('Could not set SIGQUIT handler!');
		if (!pcntl_signal(SIGINT,'_handleSignal'))
			fatalError('Could not set SIGINT handler!');
	}

	//	This function will perform the action at set time intervals forever.
	//	There should be no significant drift of the interval though a small amount
	//	of jitter is possible.
	//	It can only be terminated by signaling the process.
	//	Note: This function never returns!
	function runAction(float $inRunInterval): void
	{
		logMsg('Start.');
		//	Note the current time:
		$targetTime = microtime(true);
		//	Loop forever:
		while (true) {
			//	Perform the action:
			doAction();
			//	Find the next time to run:
			do {
				$targetTime += $inRunInterval;
			} while ($targetTime < microtime(true));
			//	Wait until that time:
			//	But sleep in small steps so that the script can be interrupted by a
			//	signal. Otherwise we might have to wait a significant amount of time
			//	for a signal to be handled.
			do {
				$tt = min($targetTime,
						  microtime(true) + 0.5);	//	Sleep for ≤0.5 seconds.
				//	Do not generate any E_WARNING because the actual time might be later
				//	than the target time. This is a corner case that might happen very
				//	occasionaly but we don't care.
				@time_sleep_until($tt);
			} while ($tt < $targetTime);
		}
	}

	//	This function is called when a signal has been recieved.
	function _handleSignal(int $inSigno,
						   $inSiginfo=null): void
	{
		switch ($inSigno) {
			case SIGTERM:
				doCleanupAndExit();	//	Never returns.

			case SIGQUIT:
			case SIGINT:
				doCleanupAndExit(256+$inSigno);	//	Never returns.

			default:
				//	We received an unknown signal.
				logMsg('Unknown signal received: '.$inSigno);
				break;
		}
	}

	//	Note: This function never returns!
	function doCleanupAndExit(int $inExitStatus=0): void
	{
		//	Do any required cleanup.
		//	FIXME
		logMsg('Cleaning up!');

		//	And exit the script.
		logMsg('Done.');
		exit($inExitStatus);
	}

	//
	//	MARK: Action
	//

	function doAction(): void
	{
		//	Do whatever this script is supposed to do.
		//	FIXME
		logMsg('Action!');
	}


	//
	//	MARK: Main code
	//

	setupSignalHandlers();
	runAction(kRunInterval);	//	Never returns.


	//	Local Variables:
	//	tab-width: 4
	//	End:


	//
	//	EOF.
	//
?>
