<?
	$fp=fopen("/dev/stdin", "r");
	echo "User:";	
	$_SERVER[PHP_AUTH_USER]=trim(fgets($fp, 1024));
	echo "Password:";
	 $_SERVER[PHP_AUTH_PW]=trim(fgets($fp, 1024));
	
	
	include "config.php";
	include "layout.class.php";
	include "bartlby-ui.class.php";
	$btl=new BartlbyUi($Bartlby_CONF, true);
	$info=$btl->getInfo();
	$layout= new Layout();
	$layout->DisplayHelp(array(0=>"WARN|Welcome to BartlbyUI",1=>"INFO|This is the help screen"));
	$layout->MetaRefresh(30);
	$layout->Table("100%");
	$lib=bartlby_lib_info($btl->CFG);
	
// define some key constants.
define("ESCAPE_KEY", 27);
define("ENTER_KEY", 13);

$start_from=0;
$alerts_only=1;
$ncurses_session = ncurses_init();
$main = ncurses_newwin(0, 0, 0, 0); // main window
ncurses_getmaxyx(&$main, $lines, $columns);
ncurses_border(0,0, 0,0, 0,0, 0,0);

ncurses_start_color();
ncurses_init_pair(1,NCURSES_COLOR_BLACK,NCURSES_COLOR_RED);
ncurses_init_pair(2,NCURSES_COLOR_BLACK,NCURSES_COLOR_YELLOW);
ncurses_init_pair(3,NCURSES_COLOR_BLACK,NCURSES_COLOR_GREEN);
ncurses_init_pair(4,NCURSES_COLOR_WHITE,NCURSES_COLOR_BLACK);

while(1){
	ncurses_timeout(2);
	$k = ncurses_getch();
	if($k == ESCAPE_KEY) {
		ncurses_resetty();
       		ncurses_end();
		exit(1);	
	}
	
	
	if($k == 258) {
		if($start_from+$lines-5 < $btl->info[services]) {
			$start_from++;	
		}
		//echo $start_from;
	}
	
	if($k == 97) {
		if($alerts_only==1) {
			$alerts_only=0;
		} else {
			$alerts_only=1;
		}
	}
	if($k == 259) {
		if($start_from > 0) {
			$start_from--;	
		}
	}
	$map = $btl->GetSVCMap();
	$oks=0;
	$warns=0;
	$crits=0;	
	ncurses_color_set(4);
	// border the main window
	ncurses_attron(NCURSES_A_REVERSE);
	ncurses_mvaddstr(0,1,"($k) bartlby\t" . date("d.m.Y H:i:s", time()) . " /" . $start_from . "-" . $btl->info[services]);
	
	ncurses_attroff(NCURSES_A_REVERSE);

 	// create a lower window which is dynamically sized...
	

       
        
        $y=2;

	$a=0;
	while(list($k, $servs) = @each($map)) {
		$displayed_servers++;
		
		
			for($x=0; $x<count($servs); $x++) {
				
					
				switch($servs[$x][current_state]) {
					case 0:
						$oks++;
						$cidx=3;
						
					break;	
					case 1:
						$warns++;
						$cidx=2;
						
					break;
					case 2:
						$crits++;
						$cidx=1;
						
					break;
					default:
						ncurses_color_set(4);
					break;
				}
				//$out_str=sprintf("%s - %s", $servs[$x][service_name], );
				$a++;
				
				if($a > $start_from && $y <= $lines-4) {
					if($alerts_only == 1) {
						if($servs[$x][current_state] == 0) {
							continue;	
						}
					}
					ncurses_move($y+1, 6);
					//ncurses_addstr($servs[$x][server_name] .  str_repeat(" ", 20-strlen($servs[$x][server_name])));
					ncurses_color_set($cidx);
					ncurses_addstr(sprintf("%10s", $btl->GetState($servs[$x][current_state])));
					ncurses_move($y+1, 20);
					ncurses_color_set(4);
					ncurses_addstr($servs[$x][server_name] . ":" . $servs[$x][service_name] . str_repeat(" ", 40-(strlen($servs[$x][service_name])+strlen($servs[$x][server_name]))));
					
					ncurses_addstr(substr(str_replace("\n", "", $servs[$x][new_server_text]), 0, 60));
					ncurses_color_set(4);
					ncurses_addstr("\n");
					//ncurses_mvaddstr($y+1,6,  $out_str);			
					$y++;
					
				
				}
			}
		
		
		
	}
	ncurses_color_set(4);
	ncurses_attron(NCURSES_A_REVERSE);
	ncurses_mvaddstr($lines-2,1,"Status:\t OK:$oks Criticals: $crits Warnings: $warns");
	ncurses_attroff(NCURSES_A_REVERSE);
	/*
	for($x=0; $x<$btl->info[services]; $x++) {
		ncurses_color_set(1);
		$svc=bartlby_get_service($btl->CFG, $x);
		
		$out_str=sprintf("		
		
  		ncurses_mvaddstr($x+1,2,  $out_str);
  	}*/
  	
  	for($tt=$y; $tt<$lines-5; $tt++) {
  		
  		ncurses_addstr(str_repeat(" ", $columns));
  		ncurses_move($tt+1, 3);	
  	}
  	ncurses_refresh();
  	usleep(2);


}//end main while


// the following are two helper functions for
// this ncurses example.



?>