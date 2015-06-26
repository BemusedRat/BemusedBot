<?php
	
	class BemusedBotPage extends Page {

		var $config = array(
			'server' => 'irc.twitch.tv',
			'port' => 6667,
			'nick' => 'bemusedbot',
			'name' => '',
			'pass' => 'oauth:14sr9y6wczvsun2yoy9f2pr0oo46hh',
			'channel' => '#pilipili96'
		);

		// This is going to hold our TCP/IP connection
		var $socket;

		// This is going to hold all of the messages both server and client
		var $ex = array();

		// This is going to hold all of the mods
		var $mods = array();

	}

	class BemusedBotPage_Controller extends Page_Controller {

		private static $allowed_actions = array (
			'CreateConnection',
			'LoginBot',
			'LogoutBot'
		);

		public function init() {
			parent::init();
			$this->CreateConnection($this->config);
			$this->Login($this->config);
			$this->Main();
		}

		public function CreateConnection($config) {
			$this->socket = fsockopen($config['server'], $config['port']);
			$this->login($config);
			$this->main();
		}

		public function Login($config) {
			$this->send_data('USER', $config['nick'] . ' ' . $config['server'] . $config['nick'] . ' :' . $config['nick']);
			$this->send_data('PASS', $config['pass']);
			$this->send_data('NICK', $config['nick']);
			$this->send_data('JOIN', $config['channel']);
		}

		public function Main() {
			// Get mods on startup
			$this->getMods();
			while(1) {
				// Get chata data and split into lines and words
				$data = fgets($this->socket, 256);
				// Paste data to screen
				echo nl2br($data);
				// Remove SOH characters that get in the way
				$data = preg_replace('/\x01/', '', $data);
				// Remove quotes (I think..?)
				$data = str_replace(array(chr(10), chr(13)), '', $data);
				flush();
				$this->ex = explode(' ', $data);

				// Return PONG to the chat server's PING
				if($this->ex[0] == 'PING') {
					$this->send_data('PONG', $this->ex[1]); //Plays ping-pong with the server to stay connected.
				}

				// Check for commands
				$command = $this->ex[3];
				//Get the user's name
				$Name = $this->getName($this->ex);

				// Save mods to array after getMods() is called
				if($Name == 'jtv') {
					$this->saveMods();
				}


				// Command List
				switch($command) {
					case ':!hello' :
						$this->send_data('PRIVMSG', $this->ex[2] . " : Hi!");
					break;
					case ':!whois' :
						$data = explode('!', $this->ex[0]);
						$Name = str_replace(':', ' ', $data[0]);
						$this->send_data('PRIVMSG', $this->ex[2] . " : 111 - " . $Name);
						$this->send_data('WHOIS bemusedrat' . "\r\n");  // Doesn't work. Probably needs a space next to WHOIS, derp
					break;
					case ':!protossed' :
						$this->send_data('PRIVMSG', $this->ex[2] . " : piliProtossed piliProtossed piliProtossed piliProtossed piliProtossed piliProtossed piliProtossed piliProtossed piliProtossed piliProtossed piliProtossed piliProtossed piliProtossed");
					break;
					case ':!buildprobe' :
					case ':!hacker' :
					case ':!hax' :
					case ':!hacks' :
						$this->send_data('PRIVMSG', $this->ex[2] . " : piliBuildProbe piliBuildProbe piliBuildProbe piliBuildProbe piliBuildProbe piliBuildProbe piliBuildProbe piliBuildProbe piliBuildProbe piliBuildProbe piliBuildProbe piliBuildProbe piliBuildProbe");
					break;
					case ':!knees' :
						$this->send_data('PRIVMSG', $this->ex[2] . " : piliKnees piliKnees piliKnees piliKnees piliKnees piliKnees piliKnees piliKnees piliKnees piliKnees piliKnees piliKnees piliKnees");
					break;
					case ':!tcgm' :
						$this->send_data('PRIVMSG', $this->ex[2] . " : piliTCGM piliTCGM piliTCGM piliTCGM piliTCGM piliTCGM piliTCGM piliTCGM piliTCGM piliTCGM piliTCGM piliTCGM piliTCGM");
					break;
					case ':!emotes' :
						$this->send_data('PRIVMSG', $this->ex[2] . " : piliBuildProbe piliProtossed piliTCGM piliKnees");
					break;
					case ':!victor' :
						$this->send_data('PRIVMSG', $this->ex[2] . " : piliBuildProbe HITMAN piliBuildProbe");
					break;
					case ':!sudoku' :
						$this->send_data('PRIVMSG', $this->ex[2] . " :/timeout " . $Name . " 1");
						$this->send_data('PRIVMSG', $this->ex[2] . " : " . $Name . " commits honourable sudoku SMSkull");
					break;
					case ':!varsovie' :
						$this->send_data('PRIVMSG', $this->ex[2] . " :/timeout Varsovie_Pat 1");
					break;
					case ':!mods' :
						$this->getMods();
					break;

					case ':!russianroulette' :
					case ':!rr' :
						if(!isset($lastshot)) {
							$lastshot = 0;
						}
						if(($timesince = time() - $lastshot) < 120) {
							$timeleft = 120 - $timesince;
							$this->send_data('PRIVMSG', $this->ex[2] . " :/me is still spinning the thing. Wait " . $timeleft . " seconds!");
						} else {
							$chamber = rand(1,6);
							$lastshot = time();
							if($chamber == 6) {
								$this->send_data('PRIVMSG', $this->ex[2] . " :/timeout " . $Name . " 30");
								$this->send_data('PRIVMSG', $this->ex[2] . " : " . $Name . " shot himself in the head");
								$this->send_data('PRIVMSG', $this->ex[2] . " : RIP " . $Name . " BibleThump");
							} else {
								$this->send_data('PRIVMSG', $this->ex[2] . " : The gun clicks & " . $Name . " lives");
								$this->send_data('PRIVMSG', $this->ex[2] . " : Survival Hype \ 4Head /");
							}
						}
					break;
					case ':!flipcoin' :
					case ':!coinflip' :
						$this->send_data('PRIVMSG', $this->ex[2] . " :/me flips a coin ShibeZ");
						$coin = rand(1,2);
						if($coin == 1){
							$coin = 'HEADS RalpherZ';
						} else {
							$coin = 'TAILS BionicBunion';
						}
						$this->send_data('PRIVMSG', $this->ex[2] . " : It's " . $coin);
					break;
					case ':!slap' :
					case ':!slaps' :
						if(isset($this->ex[4])) {
							$slapee = str_replace(array(chr(10), chr(13)), '', $this->ex[4]);
						} else {
							$slapee = $Name;
						}
						$this->send_data('PRIVMSG', $this->ex[2] . " :/me slaps " . $slapee . " with a shoe KAPOW");
					break;
					case ':!hug' :
					case ':!hugs' :
						if(isset($this->ex[4])) {
							$hugee = str_replace(array(chr(10), chr(13)), '', $this->ex[4]);
						} else {
							$hugee = $Name;
						}
						$this->send_data('PRIVMSG', $this->ex[2] . " :/me hugs " . $hugee . " gently");
						$this->send_data('PRIVMSG', $this->ex[2] . " : It's OK, " . $hugee . ". I love y-SYSTEM--ERROR-- MrDestructoid");
					break;
					case ':ACTION' :
						if($this->ex[4] == 'kicks' && strcasecmp($this->ex[5], 'BemusedBot') == 0) {
							$this->send_data('PRIVMSG', $this->ex[2] . " : KAPOW OUCH!!");
							$this->send_data('PRIVMSG', $this->ex[2] . " : Fight me IRL, " . $Name . " DansGame");
						}
					break;
					case ':!quit' :
						$this->send_data('PRIVMSG', $this->ex[2] . " : Bye!");
						$this->send_data('QUIT', 'Seeya');
						die('quit');
				}



			}

		}

		public function getName($ex) {
			$data = explode('!', $ex[0]);
			$Name = str_replace(':', '', $data[0]);
			return $Name;
		}

		public function send_data($cmd, $msg = null) {
			if($msg == null) {
				fwrite($this->socket, $cmd . "\r\n");
				echo('<script>console.log(' . $cmd . ');</script>');
			} else {
				fwrite($this->socket, $cmd . ' ' . $msg . "\r\n");
				echo('<script>console.log(' . $cmd . ' ' . $msg . ');</script>');
			}
		}

		public function getMods() {
			$this->send_data('PRIVMSG', $this->ex[2] . " :/mods");
		}

		public function saveMods() {
			// Remove the first few data points to get to the message
			$data = array_slice($this->ex, 3);
			// Put all the words together
			$data = implode($data);
			// Remove the first letter which is a colon
			$data = substr($data, 1);
			// Remove everything up to and including the next colon
			// The rest will be the mods
			$data = preg_replace('/^[^:]*:/', '', $data);
			// Put the mods into an array
			$this->mods = explode(',', $data);
		}

	}