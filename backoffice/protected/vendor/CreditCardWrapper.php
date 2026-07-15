<?php
class CreditCardWrapper{
		
	public static function encryptCard($card_number='')
	{
		$encryption_key="mykey";
		$card_number = str_replace(" "," ",$card_number);
		$encrypted = SaferCrypto::encrypt($card_number, $encryption_key);
		return $encrypted;
	}
	
	public static function decryptCard($encrypted_card='')
	{				
		$encryption_key="mykey";
		$decrypted = SaferCrypto::decrypt(trim($encrypted_card), $encryption_key);
		return $decrypted;		
	}
	
} /*end class*/