<?php
namespace AppBundle\Libs;
class Constant{
	// define error code for api
	const API_SECRET_KEY = "@briswell@sharp@2015";

	const ERR_LOGIN_REQUIRE = "401";
	const ERR_ACC_NOT_ACTIVE = "402";
	const ERR_NOT_LOGIN = "405";

	const ERR_INTERNAL = "500";

	const ERR_NULL_PARAMS = "601";
	const ERR_NULL_APPID = "602";
	const ERR_NULL_ACCESS_TIME = "603";
	const ERR_NULL_ACCESS_KEY = "604";
	const ERR_WRONG_ACCESS_KEY = "605";
	const ERR_NULL_CLIENT_QUERY = "606";
	const ERR_NULL_PARAMETER = "607";

	const ANDROID_API_KEY = "AIzaSyDwKkSd1D9hYesazoy2PntSQTjcWsKYnV0";

	public static $errorMsgArr = array(
		self::ERR_NULL_PARAMS => 'パラメーターがありません。',
		self::ERR_NULL_APPID => 'アップIDがありません。',
		self::ERR_NULL_ACCESS_TIME => 'アクセス時間がありません。',
		self::ERR_NULL_ACCESS_KEY => 'アクセスキーがありません。',
		self::ERR_WRONG_ACCESS_KEY => 'アクセスキーが間違っています。',
		self::ERR_NULL_CLIENT_QUERY => 'クライアントクエリーがありません。',
		self::ERR_NULL_PARAMETER => '必要パラメーターがありません。',
		self::ERR_LOGIN_REQUIRE => 'ログインしません。',
		self::ERR_NOT_LOGIN => 'ログインしません。',
		self::ERR_ACC_NOT_ACTIVE => 'メールアドレスが未承認なため、ログインすることは出来ません。',
	);

	public static function getErrorMsgArr() {
		return self::$errorMsgArr;
	}
}