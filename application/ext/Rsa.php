<?php

class Rsa {

	private $pubkey = "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDQKFDQWLx8tLUQoqtNLht28fPh
BtCu0dfOF5qOJI/DVh8Jfv7axJV9Nx+UT/VTj8cIhd6SkFQnqxWlhEEWjSMPMjWQ
lj4RFqPKGkGGTP3e+/adnmK2LqMqNWf6l1zfvSORg6yUi+YU79r2fMT3Dt0OCl+9
exL/9kYtFUd/47RTOQIDAQAB
-----END PUBLIC KEY-----";


	private $privkey = "-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQDQKFDQWLx8tLUQoqtNLht28fPhBtCu0dfOF5qOJI/DVh8Jfv7a
xJV9Nx+UT/VTj8cIhd6SkFQnqxWlhEEWjSMPMjWQlj4RFqPKGkGGTP3e+/adnmK2
LqMqNWf6l1zfvSORg6yUi+YU79r2fMT3Dt0OCl+9exL/9kYtFUd/47RTOQIDAQAB
AoGAAvdwH2gEV6qjofcPhewQTCOqnBxiwPsQnklL1JbEzb3ed14t96QxlTVB5/Uz
w9satQ5jW6de66nOhytZWh7szvYNl56NK5m9Alq0hBQ5qX8rkgmn90LUKf15JNEY
UGm+B2X2lFq2UPjc8nBkbq0AruXPExfq7xCL5n1o/CDGxAECQQDqckeFyG7Wgfgc
E0G2ZXdHmTEWX3V7u8rFb0VP92kWi641N4uDG/gJD7rQuxaFV96YY3cJkd8g80OX
jsqUSmf5AkEA40tS5GOAj//OyDh36f1YrPIyot9eoYUGLvm5mVA4JI21QvOT6wEE
dVfCKDyQqGTeulpQljmYepwViNQ+a6yVQQJBANovFm7j1HrfI8cFCM+1aCeC4tL+
bbiEUTYi0q+UAgHQZoTyN20B13ifYe2lX1UjLG5Hit2mGrBwlEP0yITvJgkCQEh/
tijzNAa6aZKjsFwKW0aO2mfpJ54NeDNzpCeq1r2SFccNOpky8eEb5OpAp0OPKRv3
wsyoAmLZdmT2jhJ6MgECQQCTub1Ifa1VVkg7UQti7rwml7NtXT/VLH5dhi6HOMMo
+Ut6xiHQQp/yEFQpu1KkjODGwZAHXHmUunBZji3EP5Gw
-----END RSA PRIVATE KEY-----";

	function __construct($myPrivateKey = '', $myPublicKey = '') {

		if(isset($myPrivateKey) && !empty($myPrivateKey)){
			$this->privkey = $myPrivateKey;
		}

		if(isset($myPublicKey) && !empty($myPublicKey)){
			$this->pubkey = $myPublicKey;
		}
		 
	}
	
	/**
	 * Des: RSA加密
	 */
	public function encrypt($data) {

		if (openssl_public_encrypt($data, $encrypted, $this->pubkey,OPENSSL_NO_PADDING))
			$data = base64_encode($encrypted);
		else
			$data = '';

		return $data;
	}
	
	/**
	 * DES: RSA解密
	 */
	public function decrypt($data) {
		if (openssl_private_decrypt(base64_decode($data), $decrypted, $this->privkey,OPENSSL_NO_PADDING))
			$data = $decrypted;
		else
			$data = '';

		return trim($data);
	}
	
	/**
	 * Des: Ios RSA加密
	 */
	public function iosEncrypt($data) {

		if (openssl_public_encrypt($data, $encrypted, $this->pubkey,OPENSSL_PKCS1_PADDING))
			$data = base64_encode($encrypted);
		else
			$data = '';

		return $data;
	}
	
	/**
	 * DES: Ios RSA解密
	 */
	public function iosDecrypt($data) {
		if (openssl_private_decrypt(base64_decode($data), $decrypted, $this->privkey,OPENSSL_PKCS1_PADDING))
			$data = $decrypted;
		else
			$data = '';

		return trim($data);
	}
	
	public function test(){
		$str = 'BJd3uhfJb5mlNel%2FGgJYG0sQ81JCKz2H1y00ZXmpW5%2Fe51%2FH8zCax05BsaQbSyupzs%2FM6A84uwdfYeZW9yB0IZG3AT91FeQHRb9p60p78m8HbI0NwvcfvpCJSwjGuH4nhUlDuBSbopHDIe3rRcvuD4zkHi1BQrVznGLBpohMRZc%3D';
	}

	//补齐128位
	public function noPaddingStr($str) {
		return str_pad($str, 128, " ",STR_PAD_RIGHT);
	}
}

