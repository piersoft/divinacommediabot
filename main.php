<?php
/**
* Telegram Bot Divina Commedia
* @author Francesco Piero Paolicelli @piersoft
*/

include("Telegram.php");
include("settings_t.php");

class mainloop{
const MAX_LENGTH = 4096;
function start($telegram,$update)
{

	date_default_timezone_set('Europe/Rome');
	$today = date("Y-m-d H:i:s");
	$text = $update["message"] ["text"];
	$chat_id = $update["message"] ["chat"]["id"];
	$user_id=$update["message"]["from"]["id"];
	$location=$update["message"]["location"];
	$reply_to_msg=$update["message"]["reply_to_message"];

	$this->shell($telegram,$text,$chat_id,$user_id,$location,$reply_to_msg);
	$db = NULL;

}

//gestisce l'interfaccia utente
 function shell($telegram,$text,$chat_id,$user_id,$location,$reply_to_msg)
{
	date_default_timezone_set('Europe/Rome');
	$today = date("Y-m-d H:i:s");
	if (strpos($text,'@divinacommediabot') !== false) $text=str_replace("@divinacommediabot ","",$text);

	if ($text == "/start" || $text == "Informazioni") {
		$reply = "Benvenuto. Sono un servizio automatico (bot da Robot) per la ".NAME.". Puoi ricercare i versi per parola anteponendo il carattere ? oppure cliccare su Canto per avere un intero Canto a scelta. In qualsiasi momento scrivendo /start ti ripeterò questo messaggio di benvenuto.\nQuesto bot è stato realizzato da @piersoft. E' stato testato dalla bravissima Prof. Paola Lisimberti e sarà alimentato dai links per terzine da parte della 3B del Liceo Pepe di Ostuni (che ringraziamo sentitamente), da Fabio Antonio Grasso (critico d'arte), Pino Suriano (Presidente Ass. Dante Alighieri Matera), Fedele Condedo (architetto visionario ed esperto processi partecipati), Mimmo Aprile (professore e Ingegnere). Il progetto e il codice sorgente sono liberamente riutilizzabili con licenza MIT.";
		$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
		$telegram->sendMessage($content);
		$this->create_keyboard_temp($telegram,$chat_id);
	$log=$today. ",new chat started," .$chat_id. "\n";
	file_put_contents(LOG_FILE, $log, FILE_APPEND | LOCK_EX);

		exit;
		}
		elseif ($text == "Canto" || $text == "/canto") {
			$reply = "Digita direttamente il numero del Canto postponendo PU=Purgatorio IN=Inferno PA=Paradiso.\nEsempio 2PA. Attenzione!! La risposta sarà ovviamente lunga";
			$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);
			$this->create_keyboard_temp($telegram,$chat_id);

exit;
			}
			elseif ($text == "Ricerca" || $text == "/ricerca") {
				$reply = "Scrivi la parola da cercare anteponendo il carattere ?, ad esempio: ?Caron";
				$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);
				$this->create_keyboard_temp($telegram,$chat_id);

exit;

}elseif($location!=null)
		{

		//	$this->location_manager($telegram,$user_id,$chat_id,$location);
			exit;

		}
//elseif($text !=null)

		elseif(strpos($text,'/') === false){
			$img = curl_file_create('logo.png','image/png');
			$contentp = array('chat_id' => $chat_id, 'photo' => $img);
			$telegram->sendPhoto($contentp);
		//	if (strpos($text,'@divinacommediabot') !== false) $text=str_replace("@divinacommediabot ","-",$text);

			if(strpos($text,'?') !== false || strpos($text,'-') !== false){
				function decode_entities($texts) {

									$texts=htmlentities($texts, ENT_COMPAT,'ISO-8859-1', true);
									$texts= preg_replace('/&#(\d+);/me',"chr(\\1)",$texts); #decimal notation
									$texts= preg_replace('/&#x([a-f0-9]+);/mei',"chr(0x\\1)",$texts);  #hex notation
									$texts= html_entity_decode($texts,ENT_COMPAT,"ISO-8859-1"); // UTF-8 does not work!
			return $texts;
				}
				$text=str_replace("?","",$text);
				$text=str_replace("-","",$text);
				$location="Sto cercando argomenti con parola chiave: ".$text;
				$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);
				$text=str_replace(" ","%20",$text);
/*
if (strpos($text,'è') === false || strpos($text,'é') === false || strpos($text,'ò') === false || strpos($text,'à') === false || strpos($text,'ù') === false || strpos($text,'ì') === false){
			$content = array('chat_id' => $chat_id, 'text' => "Purtroppo la ricerca per caratteri accentati non è ancora implementata",'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);
			$this->create_keyboard_temp($telegram,$chat_id);
	exit;
}
*/
$text=strtolower($text);
//			$text=mb_strtoupper($text,'UTF-8');
			$text=str_replace("ò","%C3%B2",$text);
			$text=str_replace("à","%C3%A0",$text);
			$text=str_replace("è","%C3%A8",$text);
			$text=str_replace("é","%C3%A9",$text);
			$text=str_replace("ì","%C3%AC",$text);
			$text=str_replace("ù","%C3%B9",$text);


				$urlgd  ="https://spreadsheets.google.com/tq?tqx=out:csv&tq=SELECT%20%2A%20WHERE%20lower(C)%20like%20%27%25";
				$urlgd .=$text;
				$urlgd .="%25%27&key=".GDRIVEKEY."&gid=".GDRIVEGID1;
				$urlgd=trim($urlgd);
				$urlgd=str_replace(array("\r", "\n"), '', $urlgd);
		//		$content = array('chat_id' => $chat_id, 'text' => "debug: ".$text."\n".$urlgd,'disable_web_page_preview'=>true);
		//		$telegram->sendMessage($content);
			//	sleep (1);
				$inizio=0;
				$homepage ="";
				//$comune="Lecce";

				//echo $urlgd;
				$csv = array_map('str_getcsv',file($urlgd));

				//var_dump($csv[1][0]);
				$count = 0;
				foreach($csv as $data=>$csv1){
					$count = $count+1;
				}
				if ($count ==0){
						$location="Nessun risultato trovato";
						$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
						$telegram->sendMessage($content);
					}
					if ($count >40){
							$location="Troppe risposte per il criterio scelto. Ti preghiamo di fare una ricerca più circoscritta";
							$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
							$telegram->sendMessage($content);
							exit;
						}

				for ($i=$inizio;$i<$count;$i++){

					$homepage .="\n";
					if (strpos($csv[$i][0],'O') !== false)$homepage .="\n";
					$homepage .=$csv[$i][0];
					if ($csv[$i][1] !=NULL) $homepage .=" Canto : ".$csv[$i][1];
					$homepage .="\n".$csv[$i][2];
					if ($csv[$i][7] !=NULL) 	$homepage .="\n[".$csv[$i][7]."]";
					if ($csv[$i][3] !=NULL) 	$homepage .="\n".$csv[$i][3];
					if ($csv[$i][8] !=NULL) 	$homepage .="\n[".$csv[$i][8]."]";
					if ($csv[$i][4] !=NULL) 	$homepage .="\n".$csv[$i][4];
					if ($csv[$i][9] !=NULL) 	$homepage .="\n[".$csv[$i][9]."]";
					if ($csv[$i][5] !=NULL) 	$homepage .="\n".$csv[$i][5];
					if ($csv[$i][10] !=NULL) 	$homepage .="\n[".$csv[$i][10]."]";
					if ($csv[$i][6] !=NULL) 	$homepage .="\n".$csv[$i][6];
			//		$homepage .="\n____________\n";

				}
				$chunks = str_split($homepage, self::MAX_LENGTH);
				foreach($chunks as $chunk) {
					$content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);
					$telegram->sendMessage($content);
	}
		$log=$today. ",parola," .$chat_id. "\n";
		file_put_contents(LOG_FILE, $log, FILE_APPEND | LOCK_EX);

		}elseif (strpos($text,'1') !== false || strpos($text,'2') !== false || strpos($text,'3') !== false || strpos($text,'4') !== false || strpos($text,'5') !== false || strpos($text,'6') !== false || strpos($text,'7') !== false || strpos($text,'8') !== false || strpos($text,'9') !== false || strpos($text,'0') !== false ){
$canto="Purgatorio";
if (strpos($text,'PA') !== false || strpos($text,'pa') !== false){
	$canto="Paradiso";
	$text=str_replace("pa","",$text);
	$text=str_replace("PA","",$text);
}elseif (strpos($text,'PU') !== false || strpos($text,'pu') !== false) {
	$text=str_replace("pu","",$text);
	$text=str_replace("PU","",$text);
}elseif (strpos($text,'IN') !== false || strpos($text,'in') !== false) {
	$text=str_replace("in","",$text);
	$text=str_replace("IN","",$text);
		$canto="Inferno";
}
			$canto=strtoupper($canto);
			$location="Sto cercando il Canto ".$text." del ".$canto;
			$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);
			//$text=str_replace(" ","%20",$text);
			//$text=strtoupper($text);
			$urlgd  ="https://spreadsheets.google.com/tq?tqx=out:csv&tq=SELECT%20%2A%20WHERE%20B%20%3D%20";
			$urlgd .=$text;
			$urlgd .="%20AND%20upper(A)%20contains%20%27";
			$urlgd .=$canto;
			$urlgd .="%27&key=".GDRIVEKEY."&gid=".GDRIVEGID1;
			$inizio=0;
			$homepage ="";
			//$comune="Lecce";

		//echo $urlgd;
			$csv = array_map('str_getcsv',file($urlgd));
		//var_dump($csv[1][0]);

			$count = 0;
			foreach($csv as $data=>$csv1){
				$count = $count+1;
			}
		if ($count ==0){
					$location="Nessun risultato trovato";
					$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
					$telegram->sendMessage($content);
				}
				function decode_entities($text) {

											$text=htmlentities($text, ENT_COMPAT,'ISO-8859-1', true);
											$text= preg_replace('/&#(\d+);/me',"chr(\\1)",$text); #decimal notation
											$text= preg_replace('/&#x([a-f0-9]+);/mei',"chr(0x\\1)",$text);  #hex notation
											$text= html_entity_decode($text,ENT_COMPAT,"UTF-8"); #NOTE: UTF-8 does not work!
	return $text;
				}
			for ($i=$inizio;$i<$count;$i++){

//	$csv[$i][2]=str_replace(array("\r\n", "\r", "\n"), "", $csv[$i][2] );
				$homepage .="\n\n";
		//		$homepage .=$csv[$i][0]." Terzina : ".$csv[$i][1]."\n";
				$homepage .=$csv[$i][2];
		//		$homepage .="\n____________\n";
		}
		$chunks = str_split($homepage, self::MAX_LENGTH);
		foreach($chunks as $chunk) {
			$content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);
		}

		}
	$log=$today. ",canto," .$chat_id. "\n";
	file_put_contents(LOG_FILE, $log, FILE_APPEND | LOCK_EX);

		$this->create_keyboard_temp($telegram,$chat_id);
exit;
}


	}

	function create_keyboard_temp($telegram, $chat_id)
	 {
			 $option = array(["Canto","Ricerca"],["Informazioni"]);
			 $keyb = $telegram->buildKeyBoard($option, $onetime=false);
			 $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "[Digita la parola da cercare anteponendo ? o Seleziona]");
			 $telegram->sendMessage($content);
	 }




}

?>
