<?php
class Logger
{
	static $context;
	static $config;
	static $autoFlush;
	static $buffer;

	public static function init()
	{
		self::$autoFlush = true;
		self::$buffer = [];
	}

	public static function bufferChanges()
	{
		self::$autoFlush = false;
	}

	public static function flush()
	{
		$fh = fopen(self::getLogPath(), 'ab');
		if (!$fh)
			throw new SimpleException('Cannot write to log files');
		if (flock($fh, LOCK_EX))
		{
			foreach (self::$buffer as $logEvent)
				fwrite($fh, $logEvent->getFullText() . PHP_EOL);
			fflush($fh);
			flock($fh, LOCK_UN);
			fclose($fh);
		}
		self::$buffer = [];
		self::$autoFlush = true;
	}

	public static function getLogPath()
	{
		return TextHelper::absolutePath(getConfig()->main->logsPath . DS . date('Y-m') . '.log');
	}

	public static function log($text, array $tokens = [])
	{
		self::$buffer []= new LogEvent($text, $tokens);
		if (self::$autoFlush)
			self::flush();
	}

	//methods for manipulating buffered logs
	public static function getBuffer()
	{
		return self::$buffer;
	}

	public static function setBuffer(array $buffer)
	{
		self::$buffer = $buffer;
	}
}
