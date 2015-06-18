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

		//This is going to hold our TCP/IP connection
		var $socket;

		//This is going to hold all of the messages both server and client
		var $ex = array();

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
			while(1) {
				// Get chata data and split into lines and words
				$data = fgets($this->socket, 128);
				echo nl2br($data);
				flush();
				$this->ex = explode(' ', $data);

				// Return PONG to the chat server's PING
				if($this->ex[0] == 'PING') {
					$this->send_data('PONG', $this->ex[1]); //Plays ping-pong with the server to stay connected.
				}

				// Check for commands
				$command = str_replace(array(chr(10), chr(13)), '', $this->ex[3]);
				$Name = $this->getName($this->ex);

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
					case ':!print' :
						$this->send_data('PRIVMSG', $this->ex[2] . " : 0 " . $this->ex[0] . " 1 " . $this->ex[1] . " 2 " . $this->ex[2] . " 3 " . $this->ex[3]);
					break;
					case ':!victor' :
						$this->send_data('PRIVMSG', $this->ex[2] . " : piliBuildProbe HITMAN piliBuildProbe");
					break;
					case ':!sudoku' :
						$this->send_data('PRIVMSG', $this->ex[2] . " :/timeout " . $Name . " 1");
						$this->send_data('PRIVMSG', $this->ex[2] . " : " . $Name . " commits honourable sudoku KZskull");
					break;
					case ':!varsovie' :
						$this->send_data('PRIVMSG', $this->ex[2] . " :/timeout Varsovie_Pat 1");
					break;
					case ':!zed' :
						$this->send_data('PRIVMSG', $this->ex[2] . " : Fuk u 420 DansGame");
					break;
					case ':!russianroulette' :
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
					case ':!quit' :
						$this->send_data('PRIVMSG', $this->ex[2] . " : Bye!");
						$this->send_data('QUIT', 'Seeya');
						die('quit');
				}



			}

		}

		public function getName($ex) {
			$data = explode('!', $ex[0]);
			$Name = str_replace(':', ' ', $data[0]);
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

	}