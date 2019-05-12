<?php
namespace ProfiCloS;

use function count;
use Exception;
use Nette\Forms\IControl;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use function strlen;

class Validators extends \Nette\Utils\Validators
{

	public const PASSWORD = 'ProfiCloS\Validators::validatePassword';
	public const DATE = 'ProfiCloS\Validators::validateDate';
	public const DATE_RANGE = 'ProfiCloS\Validators::validateDateRange';
	public const IDENTIFICATION_NUMBER = 'ProfiCloS\Validators::validateIdentificationNumber';

	public static function validatePassword(IControl $control): bool
	{
		return self::isPasswordStrength($control->getValue());
	}

	public static function validateDate(IControl $control): bool
	{
		return self::isDate($control->getValue());
	}

	public static function validateIdentificationNumber(IControl $control): bool
	{
		return self::isIdentificationNumber($control->getValue());
	}

	public static function isDateRange($value): bool
	{
		$range = self::parseDateRange($value);
		if (!$range) {
			return FALSE;
		}

		return TRUE;
	}

	public static function parseDateRange($value)
	{
		$value = trim($value);
		$dates = explode('-', $value);
		if (count($dates) !== 2 || empty(trim($dates[0])) || empty(trim($dates[1]))) {
			return FALSE;
		}

		try {
			$date = new DateTime(trim($dates[0]));
			$date->setTime(0, 0, 0);
			$date2 = new DateTime(trim($dates[1]));
			$date2->setTime(23, 59, 59);
			return ArrayHash::from([
				'from' => $date,
				'to' => $date2
			]);
		} catch (Exception $e) {
			return FALSE;
		}
	}

	public static function isPasswordStrength($value): bool
	{
//		^ # start of line
//		(?=(?:.*[A-Z]){2,}) # 2 upper case letters
//      (?=(?:.*[a-z]){2,}) # 2 lower case letters
//      (?=(?:.*\d){2,}) # 2 digits
//      (?=(?:.*[!@#$%^&*()\-_=+{};:,<.>]){2,}) # 2 special characters
//		(?!.*(.)\1{2}) # negative lookahead, dont allow more than 2 repeating characters
//      ([A-Za-z0-9!@#$%^&*()\-_=+{};:,<.>]{12,20}) # length 12-20, only above char classes (disallow spaces)
//		$ # end of line

		return (bool)preg_match('/^(?=(?:.*[A-Z]){1,})(?=(?:.*[a-z]){1,})(?=(?:.*\d){1,})(?!.*(.)\1{2})(.{8,})$/', $value);
	}

	public static function isDate($value): bool
	{
		if ($value instanceof \DateTime) {
			return TRUE;
		}
		$value = Strings::trim($value);
		if (empty($value)) {
			return FALSE;
		}

		try {
			new DateTime($value);
		} catch (Exception $e) {
			return FALSE;
		}

		return TRUE;
	}

	public static function isIdentificationNumber($in): bool
	{
		$in = preg_replace('#\s+#', '', $in);

		if (strlen($in) < 8) {
			$in = str_pad($in, 8, '0', STR_PAD_LEFT);
		}

		if (!preg_match('#^\d{8}$#', $in)) {
			return FALSE;
		}

		$a = 0;
		for ($i = 0; $i < 7; $i++) {
			$a += $in[$i] * (8 - $i);
		}

		$a %= 11;
		if ($a === 0) {
			$c = 1;
		} else if ($a === 1) {
			$c = 0;
		} else {
			$c = 11 - $a;
		}

		return (int)$in[7] === $c;
	}

}
