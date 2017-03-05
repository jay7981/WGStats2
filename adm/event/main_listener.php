<?php
/**
 *
 * Wargaming.net Stats. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, GhostRider, http://transformersfleet.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace GhostRider\WGStats\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use phpbb\config\config;
use phpbb\template\template;
use phpbb\user;

/**
 * Wargaming.net Stats Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	protected $user;
	protected $config;
	protected $template;
	
	static public function getSubscribedEvents() {
		return array(
			'core.memberlist_view_profile'		=> 'memberlist_view_profile',);
	}

	public function __construct(phpbb\user $user, phpbb\config\config $config, phpbb\template\template $template, $memberlist)	{
		$this->user = $user;
		$this->config = $config;
		$this->template = $template;
		$this->memberlist = $memberlist;
		$user->add_lang_ext('GhostRider/WGStats', 'info_WGStats_mod');
	}
	
	public function memberlist_view_profile($event) {
		$apikey = $this->config['gr_wgstats_wgapikey'];
		$member = $event['member'];
		$user_id = (int) $member['user_id'];
		$profile_fields = $event['profile_fields'];
			if (isset($profile_fields['row']['PROFILE_WWS_ID_VALUE'])) {
				if ($profile_fields['row']['PROFILE_MAIN_GAME_PLAYED_VALUE'] == "World of Warships") {
					$this->process_wowsstats($profile_fields['row']['PROFILE_WWS_ID_VALUE']);
					$this->process_wowsachievs($profile_fields['row']['PROFILE_WWS_ID_VALUE']);
					$this->process_wowsranked($profile_fields['row']['PROFILE_WWS_ID_VALUE']);
				}elseif ($profile_fields['row']['PROFILE_MAIN_GAME_PLAYED_VALUE'] == "World of Tanks"){
					$this->process_wotstats($profile_fields['row']['PROFILE_WWS_ID_VALUE']);
					$this->process_wotachievs($profile_fields['row']['PROFILE_WWS_ID_VALUE']);
					$this->process_wotranked($profile_fields['row']['PROFILE_WWS_ID_VALUE']);
					}
			}
	}
	
	// WoWs Player Stats
	public function process_wowsstats($wws_id) {
		$this->template->assign_vars(array(
			'S_WWS_HAS_PROFILE'	=> true,
			'WWS_ID'			=> $wws_id,));
		$ch = curl_init();
		$apiuri = 'https://api.worldofwarships.com/wows/account/info/?application_id=a3a64dfac090e84c33c25d816e4d2ccd&account_id='.$wws_id.'&language=en';
		curl_setopt($ch, CURLOPT_URL, $apiuri);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		if(curl_exec($ch) === false){
			$this->template->assign_block_vars(wowsstats, array(
			'WGSERR_CURL'	=> 'Curl error: ' . curl_error($ch) . '<br />',));
		}
		$wowsstats = json_decode(curl_exec($ch), true);
		curl_close($ch);
		foreach ($wowsstats['data'] AS $key => $value) {
			date_default_timezone_set('America/New_York');
			$lastonux = $value['last_battle_time'];
			$laston = date("F j, Y, g:i a", $lastonux);
			if($lastonux >= time()-1200) {
				$onoffline = '<img height="50" width="50" src="http://localhost/tfdemo2/images/icons/in_battle.gif" alt="online">';
			}else{
				$onoffline = '<img height="50" width="50" src="http://localhost/tfdemo2/images/icons/offline.png" alt="offline">';
			}
			$this->template->assign_block_vars(wowsstats, array(
			'WWS_NICKNAME'		=> $value['nickname'],
			'WWS_TBATTLES'		=> $value['statistics']['pvp']['battles'],
			'WWS_WINS'			=> $value['statistics']['pvp']['wins'],
			'WWS_LOSS'			=> $value['statistics']['pvp']['losses'],
			'WWS_DRAWS'			=> $value['statistics']['pvp']['draws'],
			'WWS_KILLS'			=> $value['statistics']['pvp']['frags'],
			'WWS_DEATHS'		=> $value['statistics']['pvp']['battles']-$value['statistics']['pvp']['survived_battles'],
			'WWS_RATIO'			=> number_format($value['statistics']['pvp']['wins']/$value['statistics']['pvp']['battles']*100,2, '.', ''),
			'WWS_KDRATIO'		=> number_format($value['statistics']['pvp']['frags']/($value['statistics']['pvp']['battles']-$value['statistics']['pvp']['survived_battles']),2, '.', ''),
			'WWS_LASTON'		=> $laston,
			'WWS_LASTONICON'	=> $onoffline,));
		}
	}

	// WoWs Achievements
	public function process_wowsachievs($wws_id) {
		$this->template->assign_vars(array(
			'S_WWS_HAS_PROFILE'	=> true,
			'WWS_ID'			=> $wws_id,));
		$ch = curl_init();
		$apiuri = 'https://api.worldofwarships.com/wows/account/achievements/?application_id='.$apikey.'&account_id='.$wws_id.'&language=en';
		curl_setopt($ch, CURLOPT_URL, $apiuri);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		if(curl_exec($ch) === false) {
			$this->template->assign_block_vars(wowsstats, array(
			'WGSERR_CURL'	=> 'Curl error: ' . curl_error($ch) . '<br />',));
		}
		$wowsachievs = json_decode(curl_exec($ch), true);
		curl_close($ch);
		foreach ($wowsachievs['data'][$wws_id]['battle'] AS $key => $value) {
			switch($key){
				case "BD2016_SNATCH":
				$nicename = "Big Roll";
				break;
				case "FIGHTER":
				$nicename = "Warrior";
				break;
				case "CAMPAIGN_SB_COMPLETED":
				$nicename = "Honorable Service";
				break;
				case "NY17_SAFECRACKER":
				$nicename = "Tin Can";
				break;
				case "ST_PARTICIPANT":
				$nicename = "ST Participant";
				break;
				case "SUPPORT":
				$nicename = "Confederate";
				break;
				case "BD2016_RISE_OF_THE_MACHINES":
				$nicename = "Rise of the Machines";
				break;
				case "MERCENARY_L":
				$nicename = "Supply Officer";
				break;
				case "ONE_SOLDIER_IN_THE_FIELD":
				$nicename = "Solo Warrior";
				break;
				case "AT_PARTICIPANT":
				$nicename = "AT Veteran";
				break;
				case "SEA_LEGEND":
				$nicename = "Bane of the Oceans";
				break;
				case "BD2016_KING_OF_PARTY":
				$nicename = "King of the Party";
				break;
				case "MESSENGER":
				$nicename = "In the Thick of It";
				break;
				case "NY17_WIN_AT_LEAST_ONE":
				$nicename = "Good Start";
				break;
				case "UNSINKABLE":
				$nicename = "Unsinkable";
				break;
				case "SCIENCE_OF_WINNING_ARSONIST":
				$nicename = "Naval Warfare. Arson";
				break;
				case "CAMPAIGN_SB_COMPLETED_EXCELLENT":
				$nicename = "Honorable Service with Honors";
				break;
				case "FIREPROOF":
				$nicename = "Fireproof";
				break;
				case "SCIENCE_OF_WINNING_TACTICIAN":
				$nicename = "Naval Warfare. Tactics";
				break;
				case "MESSENGER_L":
				$nicename = "Junior Supply Officer";
				break;
				case "WORKAHOLIC":
				$nicename = "Ready for Anything";
				break;
				case "BATTLE_HERO":
				$nicename = "Battle Hero";
				break;
				case "BD2016_WRONG_SOW":
				$nicename = "A Shot in the Dark";
				break;
				case "WG_STAFF":
				$nicename = "Wargaming";
				break;
				case "CAMPAIGN1_COMPLETED":
				$nicename = "Science of Victory";
				break;
				case "BD2016_MANNERS":
				$nicename = "Manners Maketh Man";
				break;
				case "NY17_DRESS_THE_TREE":
				$nicename = "Feeling Good";
				break;
				case "MAIN_CALIBER":
				$nicename = "High Caliber";
				break;
				case "WORKAHOLIC_L":
				$nicename = "Senior Supply Officer";
				break;
				case "HEADBUTT":
				$nicename = "Die-Hard";
				break;
				case "INSTANT_KILL":
				$nicename = "Devastating Strike";
				break;
				case "NY17_AIMING":
				$nicename = "Aiming? Too Much Effort";
				break;
				case "NY17_500_LEAGUES":
				$nicename = "An Epic Journey";
				break;
				case "JUNIOR_PLANNER":
				$nicename = "Junior Naval Designer";
				break;
				case "WGFEST2016":
				$nicename = "Wargaming Fest";
				break;
				case "NY17_WIN_ALL":
				$nicename = "Hoarder 2016";
				break;
				case "ENGINEER":
				$nicename = "Naval Constructor";
				break;
				case "SCIENCE_OF_WINNING_HARD_EDGED":
				$nicename = "Naval Warfare. Ramming";
				break;
				case "ATBA_CALIBER":
				$nicename = "Close Quarters Expert";
				break;
				case "NY17_BREAK_THE_BANK":
				$nicename = "Break the Bank";
				break;
				case "CBT_PARTICIPANT":
				$nicename = "CBT Veteran";
				break;
				case "CAMPAIGN_NY17B_COMPLETED":
				$nicename = "The Hunt for Graf Spee";
				break;
				case "BD2016_PARTY_ANIMAL":
				$nicename = "Life and Soul of the Party";
				break;
				case "WARRIOR":
				$nicename = "Kraken Unleashed!";
				break;
				case "CAMPAIGN1_COMPLETED_EXCELLENT":
				$nicename = "Science of Victory with Honors";
				break;
				case "SCIENCE_OF_WINNING_TO_THE_BOTTOM":
				$nicename = "Naval Warfare. Flooding";
				break;
				case "VETERAN":
				$nicename = "Veteran";
				break;
				case "CAMPAIGN_NY17E_COMPLETED_EXCELLENT":
				$nicename = "Santa's Christmas Convoys with Honors";
				break;
				case "NO_DAY_WITHOUT_ADVENTURE":
				$nicename = "A Day Without Adventure Is a Wasted One";
				break;
				case "BD2016_RUN_FOREST":
				$nicename = "Run! Admiral! Run!";
				break;
				case "NEVER_ENOUGH_MONEY":
				$nicename = "Business Magnate";
				break;
				case "MILLIONAIR":
				$nicename = "Moneybags";
				break;
				case "NO_PRICE_FOR_HEROISM":
				$nicename = "Legend of the Seas";
				break;
				case "BW_PARTICIPANT":
				$nicename = "BW Participant";
				break;
				case "DREADNOUGHT":
				$nicename = "Dreadnought";
				break;
				case "CAPITAL":
				$nicename = "Initial Capital";
				break;
				case "BD2016_FESTIV_SOUP":
				$nicename = "Festive Soup";
				break;
				case "SCIENCE_OF_WINNING_BOMBARDIER":
				$nicename = "Naval Warfare. Weaponry Basics";
				break;
				case "CLEAR_SKY":
				$nicename = "Clear Sky";
				break;
				case "CAMPAIGN_NY17E_COMPLETED":
				$nicename = "Santa's Christmas Convoys";
				break;
				case "DOUBLE_KILL":
				$nicename = "Double Strike";
				break;
				case "HALLOWEEN_2016":
				$nicename = "Ghostbuster";
				break;
				case "RETRIBUTION":
				$nicename = "It's Just a Flesh Wound!";
				break;
				case "NY17_TO_THE_BOTTOM":
				$nicename = "To the Bottom";
				break;
				case "FIRST_BLOOD":
				$nicename = "First Blood";
				break;
				case "DETONATED":
				$nicename = "Detonation";
				break;
				case "LIQUIDATOR":
				$nicename = "Liquidator";
				break;
				case "MERCENARY":
				$nicename = "Workhorse";
				break;
				case "WITHERING":
				$nicename = "Witherer";
				break;
				case "CHIEF_ENGINEER":
				$nicename = "Chief Naval Architect";
				break;
				case "BD2016_FIRESHOW":
				$nicename = "Fire Show";
				break;
				case "NO_DAY_WITHOUT_ADVENTURE_L":
				$nicename = "Smooth Supply";
				break;
				case "ARSONIST":
				$nicename = "Arsonist";
				break;
				case "WGSPB_STAFF":
				$nicename = "Developer";
				break;
				case "BD2016_PARTY_CHECK_IN":
				$nicename = "Queue Jumper";
				break;
				case "CAMPAIGN_NY17B_COMPLETED_EXCELLENT":
				$nicename = "The Hunt for Graf Spee with Honors";
				break;
				case "AMAUTEUR":
				$nicename = "Amateur";
				break;
				case "SCIENCE_OF_WINNING_LUCKY":
				$nicename = "Naval Warfare. Lucky Shot";
				break;
			}		
			if ($value > '1') {
			$this->template->assign_block_vars(wowsachievs, array(
			'WWSA_ACHIEVS'	=> '<figure><img class=scaled src="http://api.worldofwarships.com/static/1.11.0/wows/encyclopedia/achievements/normal/'.$key.'.png" alt="'.$key.'">x'.$value.'<figcaption>'.$nicename.'</figcaption></figure>',));
			}else{
				$this->template->assign_block_vars(wowsachievs, array(
			'WWSA_ACHIEVS'	=> '<figure><img class=scaled src="http://api.worldofwarships.com/static/1.11.0/wows/encyclopedia/achievements/normal/'.$key.'.png" alt="'.$key.'"><figcaption>'.$nicename.'</figcaption></figure>',));
			}
		}
	}
	
	// WoWs Ranked Stats
	public function process_wowsranked($wws_id)	{
		$this->template->assign_vars(array(
			'S_WWS_HAS_PROFILE'	=> true,
			'WWS_ID'			=> $wws_id,
		));
		$ch = curl_init();
		$apiuri = 'https://api.worldofwarships.com/wows/seasons/accountinfo/?application_id='.$apikey.'&account_id='.$wws_id.'&language=en';
		curl_setopt($ch, CURLOPT_URL, $apiuri);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		if(curl_exec($ch) === false) {
			$this->template->assign_block_vars(wowsstats, array(
			'WGSERR_CURL'	=> 'Curl error: ' . curl_error($ch) . '<br />',));
		}
		$wowsranked = json_decode(curl_exec($ch), true);
		curl_close($ch);
		foreach ($wowsranked['data'] AS $key => $value) {
			$this->template->assign_block_vars(wowsranked, array(
			'WWSR_MAXRANK1'			=> $value['seasons']['1']['rank_info']['max_rank'],
			'WWSR_STARTRANK1'		=> $value['seasons']['1']['rank_info']['start_rank'],
			'WWSR_RANK1'			=> $value['seasons']['1']['rank_info']['rank'],
			'WWSR_MAXRANK2'			=> $value['seasons']['2']['rank_info']['max_rank'],
			'WWSR_STARTRANK2'		=> $value['seasons']['2']['rank_info']['start_rank'],
			'WWSR_RANK2'			=> $value['seasons']['2']['rank_info']['rank'],
			'WWSR_MAXRANK3'			=> $value['seasons']['3']['rank_info']['max_rank'],
			'WWSR_STARTRANK3'		=> $value['seasons']['3']['rank_info']['start_rank'],
			'WWSR_RANK3'			=> $value['seasons']['3']['rank_info']['rank'],
			'WWSR_MAXRANK4'			=> $value['seasons']['4']['rank_info']['max_rank'],
			'WWSR_STARTRANK4'		=> $value['seasons']['4']['rank_info']['start_rank'],
			'WWSR_RANK4'			=> $value['seasons']['4']['rank_info']['rank'],
			'WWSR_MAXRANK5'			=> $value['seasons']['5']['rank_info']['max_rank'],
			'WWSR_STARTRANK5'		=> $value['seasons']['5']['rank_info']['start_rank'],
			'WWSR_RANK5'			=> $value['seasons']['5']['rank_info']['rank'],
			'WWSR_MAXRANK6'			=> $value['seasons']['6']['rank_info']['max_rank'],
			'WWSR_STARTRANK6'		=> $value['seasons']['6']['rank_info']['start_rank'],
			'WWSR_RANK6'			=> $value['seasons']['6']['rank_info']['rank'],));
		}
	}
	
	// WoT Player Stats
	public function process_wotstats($wws_id) {
		$this->template->assign_vars(array(
			'S_WWS_HAS_PROFILE'	=> true,
			'WWS_ID'			=> $wws_id,));
		$ch = curl_init();
		$apiuri = 'https://api.worldoftanks.com/wot/account/info/?application_id='.$apikey.'&account_id='.$wws_id.'&language=en';
		curl_setopt($ch, CURLOPT_URL, $apiuri);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		if(curl_exec($ch) === false) {
			$this->template->assign_block_vars(wowsstats, array(
			'WGSERR_CURL'	=> 'Curl error: ' . curl_error($ch) . '<br />',));
		}
		$wowsstats = json_decode(curl_exec($ch), true);
		curl_close($ch);
		foreach ($wowsstats['data'] AS $key => $value) {
			$this->template->assign_block_vars(wotstats, array(
			'WWS_NICKNAME'		=> $value['nickname'],
			'WWS_TBATTLES'		=> $value['statistics']['all']['battles'],
			'WWS_WINS'			=> $value['statistics']['all']['wins'],
			'WWS_LOSS'			=> $value['statistics']['all']['losses'],
			'WWS_DRAWS'			=> $value['statistics']['all']['draws'],
			'WWS_KILLS'			=> $value['statistics']['all']['frags'],
			'WWS_RATIO'			=> number_format($value['statistics']['all']['wins']/$value['statistics']['all']['battles']*100),));
		}
	}

	// WoT Achievements
	public function process_wotachievs($wws_id)	{
		$this->template->assign_vars(array(
			'S_WWS_HAS_PROFILE'	=> true,
			'WWS_ID'			=> $wws_id,));
		$ch = curl_init();
		$apiuri = 'https://api.worldoftanks.com/wot/account/achievements/?application_id='.$apikey.'&language=en&account_id='.$wws_id;
		curl_setopt($ch, CURLOPT_URL, $apiuri);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		if(curl_exec($ch) === false) {
			$this->template->assign_block_vars(wowsstats, array(
			'WGSERR_CURL'	=> 'Curl error: ' . curl_error($ch) . '<br />',));
		}
		$wotachievs = json_decode(curl_exec($ch), true);
		curl_close($ch);
		foreach ($wotachievs['data'][$wws_id]['achievements'] AS $key => $value) {
			switch($key){
				case "crucialShotMedal":
				$niceName = "Crucial Shot";
				break;
				case "armorPiercer":
				$niceName = "Master Gunner";
				break;
				case "medalFadin":
				$niceName = "Fadin's Medal";
				break;
				case "medalCarius":
				$niceName = "Carius's Medal";
				break;
				case "medalMonolith":
				$niceName = "Rock Solid";
				break;
				case "medalEkins":
				$niceName = "Ekins's Medal";
				break;
				case "noMansLand":
				$niceName = "Scorched Earth";
				break;
				case "heroesOfRassenay":
				$niceName = "Raseiniai Heroes' Medal";
				break;
				case "aimer":
				$niceName = "Spotter";
				break;
				case "markIRepairer":
				$niceName = "Field Repair";
				break;
				case "readyForBattleLT":
				$niceName = "Exemplary Performance: Light Tanks";
				break;
				case "defender":
				$niceName = "Defender";
				break;
				case "supporter":
				$niceName = "Confederate";
				break;
				case "effectiveSupport":
				$niceName = "Superior Support";
				break;
				case "medalLehvaslaiho":
				$niceName = "Lehväslaiho's Medal";
				break;
				case "tankExpert":
				$niceName = "Master Tanker";
				break;
				case "victoryMarch":
				$niceName = "Victory March";
				break;
				case "histBattle5_historyLessons":
				$niceName = "Lessons of History: Battle of Berlin";
				break;
				case "tankExpert4":
				$niceName = "Expert: France";
				break;
				case "sniper":
				$niceName = "Sniper";
				break;
				case "scout":
				$niceName = "Scout";
				break;
				case "titleSniper":
				$niceName = "Sharpshooter";
				break;
				case "medalCrucialContribution":
				$niceName = "Crucial Contribution";
				break;
				case "tacticalAdvantage":
				$niceName = "Tactical Superiority";
				break;
				case "histBattle2_battlefield":
				$niceName = "Battlefield: Battle of Kursk";
				break;
				case "predator":
				$niceName = "Predator";
				break;
				case "EFC2016Goleador":
				$niceName = "Hat Trick";
				break;
				case "markOfMastery":
				$niceName = "Mastery Badge";
				break;
				case "markI100Years":
				$niceName = "100 Years of Tanks";
				break;
				case "tankExpert2":
				$niceName = "Expert: U.S.A.";
				break;
				case "geniusForWarMedal":
				$niceName = "War Genius";
				break;
				case "tankExpert0":
				$niceName = "Expert: U.S.S.R.";
				break;
				case "tankExpert7":
				$niceName = "Expert: Czechoslovakia";
				break;
				case "tankExpert6":
				$niceName = "Expert: Japan";
				break;
				case "tankExpert5":
				$niceName = "Expert: U.K.";
				break;
				case "medalLavrinenko":
				$niceName = "Lavrinenko's Medal";
				break;
				case "arsonist":
				$niceName = "Arsonist";
				break;
				case "medalKolobanov":
				$niceName = "Kolobanov's Medal";
				break;
				case "tankExpert3":
				$niceName = "Expert: China";
				break;
				case "medalLafayettePool":
				$niceName = "Pool's Medal";
				break;
				case "rangerMedal":
				$niceName = "Ranger";
				break;
				case "pyromaniacMedal":
				$niceName = "Pyromaniac";
				break;
				case "histBattle4_battlefield":
				$niceName = "Battlefield: Bryansk Front";
				break;
				case "strategicOperations":
				$niceName = "For Strategic Operations";
				break;
				case "histBattle6_battlefield":
				$niceName = "Battlefield: Siege of Tobruk";
				break;
				case "medalKnispel":
				$niceName = "Knispel's Medal";
				break;
				case "bannerman":
				$niceName = "Streak of Color";
				break;
				case "shootToKill":
				$niceName = "Fire for Effect";
				break;
				case "invader":
				$niceName = "Invader";
				break;
				case "bonecrusher":
				$niceName = "Bruiser";
				break;
				case "fireAndSteelMedal":
				$niceName = "Fire and Steel";
				break;
				case "mechanicEngineer":
				$niceName = "Senior Technical Engineer";
				break;
				case "histBattle2_historyLessons":
				$niceName = "Lessons of History: Battle of Kursk";
				break;
				case "kampfer":
				$niceName = "Skirmisher";
				break;
				case "medalKay":
				$niceName = "Kay's Medal";
				break;
				case "duelist":
				$niceName = "Duelist";
				break;
				case "aloneInTheField":
				$niceName = "I Stand Alone!";
				break;
				case "kingOfTheHill":
				$niceName = "King of the Hill";
				break;
				case "medalOrlik":
				$niceName = "Orlik's Medal";
				break;
				case "prematureDetonationMedal":
				$niceName = "Achille’s Heel";
				break;
				case "crusher":
				$niceName = "Fortress Crusher";
				break;
				case "medalBrothersInArms":
				$niceName = "Brothers in Arms";
				break;
				case "medalAbrams":
				$niceName = "Abrams's Medal";
				break;
				case "medalRotmistrov":
				$niceName = "Rotmistrov's Medal";
				break;
				case "testartilleryman":
				$niceName = "Rocket Scientist";
				break;
				case "markIBaseProtector":
				$niceName = "Base Protector";
				break;
				case "tankwomen":
				$niceName = "Women at War";
				break;
				case "luckyDevil":
				$niceName = "Lucky";
				break;
				case "mainGun":
				$niceName = "High Caliber";
				break;
				case "ironMan":
				$niceName = "Cool-Headed";
				break;
				case "warrior":
				$niceName = "Top Gun";
				break;
				case "falloutPackOfWolfs":
				$niceName = "Pack Mentality";
				break;
				case "medalWittmann":
				$niceName = "Bölter's Medal";
				break;
				case "even":
				$niceName = "Eye for an Eye!";
				break;
				case "deathTrack":
				$niceName = "Racer 2014";
				break;
				case "godOfWar":
				$niceName = "God of War";
				break;
				case "counterblow":
				$niceName = "Retaliation";
				break;
				case "conqueror":
				$niceName = "Capturer";
				break;
				case "medalRadleyWalters":
				$niceName = "Radley-Walters's Medal";
				break;
				case "readyForBattleMT":
				$niceName = "Exemplary Performance: Medium Tanks";
				break;
				case "fireAndSword":
				$niceName = "For Decisive Battles";
				break;
				case "medalBillotte":
				$niceName = "Billotte's Medal";
				break;
				case "impenetrable":
				$niceName = "Shellproof";
				break;
				case "fallenFlags":
				$niceName = "Fallen Flags";
				break;
				case "histBattle5_battlefield":
				$niceName = "Battlefield: Battle of Berlin";
				break;
				case "falloutSingleWolf":
				$niceName = "Lone Wolf";
				break;
				case "diehard":
				$niceName = "Survivor";
				break;
				case "tacticalSkill":
				$niceName = "Tactical Supremacy";
				break;
				case "histBattle4_historyLessons":
				$niceName = "Lessons of History: Bryansk Front";
				break;
				case "evileye":
				$niceName = "Patrol Duty";
				break;
				case "histBattle3_historyLessons":
				$niceName = "Lessons of History: Battle of the Bulge (1944)";
				break;
				case "medalHalonen":
				$niceName = "Halonen's Medal";
				break;
				case "medalPascucci":
				$niceName = "Pascucci's Medal";
				break;
				case "fightingReconnaissanceMedal":
				$niceName = "Fighting Reconnaissance";
				break;
				case "fallout2":
				$niceName = "Glorious Victory";
				break;
				case "forTacticalOperations":
				$niceName = "For Tactical Operations";
				break;
				case "firstMerit":
				$niceName = "First Merit";
				break;
				case "histBattle1_historyLessons":
				$niceName = "Lessons of History: Operation Spring Awakening";
				break;
				case "readyForBattleHT":
				$niceName = "Exemplary Performance: Heavy Tanks";
				break;
				case "steelwall":
				$niceName = "Steel Wall";
				break;
				case "EFC2016":
				$niceName = "Football Player 2016";
				break;
				case "raider":
				$niceName = "Raider";
				break;
				case "medalPoppel":
				$niceName = "Popel's Medal";
				break;
				case "mechanicEngineer6":
				$niceName = "Technical Engineer: Japan";
				break;
				case "mechanicEngineer7":
				$niceName = "Technical Engineer: Czechoslovakia";
				break;
				case "mechanicEngineer4":
				$niceName = "Technical Engineer: France";
				break;
				case "mechanicEngineer5":
				$niceName = "Technical Engineer: U.K.";
				break;
				case "mechanicEngineer2":
				$niceName = "Technical Engineer: U.S.A.";
				break;
				case "markIBomberman":
				$niceName = "Bomberman";
				break;
				case "mechanicEngineer0":
				$niceName = "Technical Engineer: U.S.S.R.";
				break;
				case "mechanicEngineer1":
				$niceName = "Technical Engineer: Germany";
				break;
				case "unreachable":
				$niceName = "Hail to the King, Baby!";
				break;
				case "whiteTiger":
				$niceName = "White Tiger";
				break;
				case "medalTarczay":
				$niceName = "Tarczay's Medal";
				break;
				case "sinai":
				$niceName = "The Lion of Sinai";
				break;
				case "mechanicEngineer8":
				$niceName = "Technical Engineer: Sweden";
				break;
				case "histBattle1_battlefield":
				$niceName = "Battlefield: Operation Spring Awakening";
				break;
				case "infiltratorMedal":
				$niceName = "Vanquisher";
				break;
				case "guardsman":
				$niceName = "Guardsman";
				break;
				case "champion":
				$niceName = "Rambo";
				break;
				case "xmasTreeGold":
				$niceName = "Bad Ace Tanker, Class I";
				break;
				case "medalDeLanglade":
				$niceName = "De Langlade's Medal";
				break;
				case "sniper2":
				$niceName = "Tank Sniper";
				break;
				case "battleCitizen":
				$niceName = "Operation Nostalgia";
				break;
				case "heavyFireMedal":
				$niceName = "Heavy Fire";
				break;
				case "moonSphere":
				$niceName = "Trickshot";
				break;
				case "kamikaze":
				$niceName = "Kamikaze";
				break;
				case "shoulderToShoulder":
				$niceName = "United We Stand";
				break;
				case "charmed":
				$niceName = "Hand of God";
				break;
				case "readyForBattleSPG":
				$niceName = "Exemplary Performance: SPGs";
				break;
				case "invincible":
				$niceName = "Invincible";
				break;
				case "histBattle6_historyLessons":
				$niceName = "Lessons of History: Siege of Tobruk";
				break;
				case "wolfAmongSheepMedal":
				$niceName = "Wolf Among Sheep";
				break;
				case "mechanicEngineer3":
				$niceName = "Technical Engineer: China";
				break;
				case "guerrillaMedal":
				$niceName = "Guerrilla";
				break;
				case "promisingFighterMedal":
				$niceName = "Promising Fighter";
				break;
				case "medalDumitru":
				$niceName = "Dumitru's Medal";
				break;
				case "pattonValley":
				$niceName = "Valley of Pattons";
				break;
				case "mousebane":
				$niceName = "Mouse Trap";
				break;
				case "histBattle3_battlefield":
				$niceName = "Battlefield: Battle of the Bulge (1944)";
				break;
				case "medalBrunoPietro":
				$niceName = "Bruno's Medal";
				break;
				case "xmasTreeBronze":
				$niceName = "Bad Ace Tanker, Class III";
				break;
				case "medalOskin":
				$niceName = "Oskin's Medal";
				break;
				case "readyForBattleATSPG":
				$niceName = "Exemplary Performance: Tank Destroyers";
				break;
				case "medalLeClerc":
				$niceName = "Leclerc's Medal";
				break;
				case "demolition":
				$niceName = "Demolition Expert";
				break;
				case "beasthunter":
				$niceName = "Hunter";
				break;
				case "medalTamadaYoshio":
				$niceName = "Yoshio Tamada's Medal";
				break;
				case "sentinelMedal":
				$niceName = "Sentinel";
				break;
				case "medalStark":
				$niceName = "Stark's Medal";
				break;
				case "armoredFist":
				$niceName = "Armored Fist";
				break;
				case "medalAntiSpgFire":
				$niceName = "For Counter-Battery Fire";
				break;
				case "operationWinter":
				$niceName = "Operation Winter";
				break;
				case "reliableComrade":
				$niceName = "Battle Buddy";
				break;
				case "handOfDeath":
				$niceName = "Reaper";
				break;
				case "falloutDieHard":
				$niceName = "Unbreakable";
				break;
				case "winnerLaurels":
				$niceName = "V for Victory";
				break;
				case "markIProtector":
				$niceName = "Honor Guard";
				break;
				case "huntsman":
				$niceName = "Naydin's Medal";
				break;
				case "secretOperations":
				$niceName = "Sudden Strike";
				break;
				case "medalBurda":
				$niceName = "Burda's Medal";
				break;
				case "tankExpert8":
				$niceName = "Expert: Sweden";
				break;
				case "medalGore":
				$niceName = "Gore's Medal";
				break;
				case "falloutAlwaysInLine":
				$niceName = "Battle-Hardened";
				break;
				case "makerOfHistory":
				$niceName = "History Maker";
				break;
				case "stormLord":
				$niceName = "Valkyrie";
				break;
				case "falloutSteelHunter":
				$niceName = "Steel Hunter";
				break;
				case "bombardier":
				$niceName = "Bombardier";
				break;
				case "medalNikolas":
				$niceName = "Nicols's Medal";
				break;
				case "tankExpert1":
				$niceName = "Expert: Germany";
				break;
				case "battleTested":
				$niceName = "Battle Tested";
				break;
				case "fighter":
				$niceName = "Fighter";
				break;
				case "sturdy":
				$niceName = "Spartan";
				break;
				case "medalCoolBlood":
				$niceName = "Cold-Blooded";
				break;
				case "soldierOfFortune":
				$niceName = "Soldier of Fortune";
				break;
				case "willToWinSpirit":
				$niceName = "Will-to-Win Spirit";
				break;
				case "WFC2014":
				$niceName = "Football Player 2014";
				break;
				case "bruteForceMedal":
				$niceName = "Brute Force";
				break;
				case "tacticalBreakthrough":
				$niceName = "Tactical Genius";
				break;
				case "xmasTreeSilver":
				$niceName = "Bad Ace Tanker, Class II";
				break;
				case "fallout":
				$niceName = "Domination Hero";
				break;
			}
			switch($key){
				case "medalCarius":
					$ext = $value.'.png';
					break;
				case "medalEkins":
					$ext = $value.'.png';
					break;
				case "readyForBattleLT":
					$ext = $value.'.png';
					break;
				case "markOfMastery":
					$ext = $value.'.png';
					break;
				case "medalLavrinenko":
					$ext = $value.'.png';
					break;
				case "strategicOperations":
					$ext = $value.'.png';
					break;
				case "medalKnispel":
					$ext = $value.'.png';
					break;
				case "kampfer":
					$ext = $value.'.png';
					break;
				case "medalKay":
					$ext = $value.'.png';
					break;
				case "medalAbrams":
					$ext = $value.'.png';
					break;
				case "medalRotmistrov":
					$ext = $value.'.png';
					break;
				case "conqueror":
					$ext = $value.'.png';
					break;
				case "readyForBattleMT":
					$ext = $value.'.png';
					break;
				case "fireAndSword":
					$ext = $value.'.png';
					break;
				case "forTacticalOperations":
					$ext = $value.'.png';
					break;
				case "readyForBattleHT":
					$ext = $value.'.png';
					break;
				case "medalPoppel":
					$ext = $value.'.png';
					break;
				case "guardsman":
					$ext = $value.'.png';
					break;
				case "readyForBattleSPG":
					$ext = $value.'.png';
					break;
				case "readyForBattleATSPG":
					$ext = $value.'.png';
					break;
				case "medalLeClerc":
					$ext = $value.'.png';
					break;
				case "makerOfHistory":
					$ext = $value.'.png';
					break;
				case "battleTested":
					$ext = $value.'.png';
					break;
				case "soldierOfFortune":
					$ext = $value.'.png';
					break;
				default:
					$ext = '.png';
					break;
			}
			if ($value >= '2') {
			$this->template->assign_block_vars(wotachievs, array(
			'WOTA_ACHIEVS'	=> '<figure><img class=scaled src="http://api.worldoftanks.com/static/2.52.0/wot/encyclopedia/achievement/'.$key.$ext.'" alt="'.$key.'">x'.$value.'<figcaption>'.$niceName.'</figcaption></figure>',));
			}else{
				$this->template->assign_block_vars(wotachievs, array(
			'WOTA_ACHIEVS'	=> '<figure><img class=scaled src="http://api.worldoftanks.com/static/2.52.0/wot/encyclopedia/achievement/'.$key.$ext.'" alt="'.$key.'"><figcaption>'.$niceName.'</figcaption></figure>',));
			}
		}
	}
	
	// WoT Ranked Stats
	public function process_wotranked($wws_id) {
		$this->template->assign_vars(array(
			'S_WWS_HAS_PROFILE'	=> true,
			'WWS_ID'			=> $wws_id,));
		$ch = curl_init();
		$apiuri = 'https://api.worldofwarships.com/wows/seasons/accountinfo/?application_id='.$apikey.'&account_id='.$wws_id.'&language=en';
		curl_setopt($ch, CURLOPT_URL, $apiuri);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		if(curl_exec($ch) === false) {
			$this->template->assign_block_vars(wowsstats, array(
			'WGSERR_CURL'	=> 'Curl error: ' . curl_error($ch) . '<br />',));
		}
		$wowsranked = json_decode(curl_exec($ch), true);
		curl_close($ch);
		foreach ($wowsranked['data'] AS $key => $value) {
			$this->template->assign_block_vars(wotranked, array(
			'WWSR_MAXRANK1'			=> $value['seasons']['1']['rank_info']['max_rank'],
			'WWSR_STARTRANK1'		=> $value['seasons']['1']['rank_info']['start_rank'],
			'WWSR_RANK1'			=> $value['seasons']['1']['rank_info']['rank'],
			'WWSR_MAXRANK2'			=> $value['seasons']['2']['rank_info']['max_rank'],
			'WWSR_STARTRANK2'		=> $value['seasons']['2']['rank_info']['start_rank'],
			'WWSR_RANK2'			=> $value['seasons']['2']['rank_info']['rank'],
			'WWSR_MAXRANK3'			=> $value['seasons']['3']['rank_info']['max_rank'],
			'WWSR_STARTRANK3'		=> $value['seasons']['3']['rank_info']['start_rank'],
			'WWSR_RANK3'			=> $value['seasons']['3']['rank_info']['rank'],
			'WWSR_MAXRANK4'			=> $value['seasons']['4']['rank_info']['max_rank'],
			'WWSR_STARTRANK4'		=> $value['seasons']['4']['rank_info']['start_rank'],
			'WWSR_RANK4'			=> $value['seasons']['4']['rank_info']['rank'],
			'WWSR_MAXRANK5'			=> $value['seasons']['5']['rank_info']['max_rank'],
			'WWSR_STARTRANK5'		=> $value['seasons']['5']['rank_info']['start_rank'],
			'WWSR_RANK5'			=> $value['seasons']['5']['rank_info']['rank'],
			'WWSR_MAXRANK6'			=> $value['seasons']['6']['rank_info']['max_rank'],
			'WWSR_STARTRANK6'		=> $value['seasons']['6']['rank_info']['start_rank'],
			'WWSR_RANK6'			=> $value['seasons']['6']['rank_info']['rank'],));
		}
	}
}
?>