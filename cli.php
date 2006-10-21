<?


	$uname=getenv("BARTLBY_USER");
	$pw=getenv("BARTLBY_PASSWORD");
	if(!$uname && !$pw) {
		$fp=fopen("/dev/stdin", "r");
		$_SERVER[PHP_AUTH_USER]=trim(fgets($fp, 1024));
	 	$_SERVER[PHP_AUTH_PW]=trim(fgets($fp, 1024));
		fclose($fp);
	} else {
		$_SERVER[PHP_AUTH_USER]=$uname;
		$_SERVER[PHP_AUTH_PW]=$pw;
	}
	
	
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
$show_downtimes=0;
$selected_index=0;

$ncurses_session = ncurses_init();
$main = ncurses_newwin(0, 0, 0, 0); // main window
ncurses_getmaxyx(&$main, $lines, $columns);
ncurses_border(0,0, 0,0, 0,0, 0,0);

ncurses_start_color();
ncurses_init_pair(1,NCURSES_COLOR_BLACK,NCURSES_COLOR_RED);
ncurses_init_pair(2,NCURSES_COLOR_BLACK,NCURSES_COLOR_YELLOW);
ncurses_init_pair(3,NCURSES_COLOR_BLACK,NCURSES_COLOR_GREEN);


ncurses_init_pair(4,NCURSES_COLOR_WHITE,NCURSES_COLOR_BLACK);
ncurses_init_pair(5,NCURSES_COLOR_WHITE,NCURSES_COLOR_BLUE);

while(1){
	ncurses_timeout(2);
	$k = ncurses_getch();
	if($k == ESCAPE_KEY || $k == 113) {
		ncurses_resetty();
       		ncurses_end();
		exit(1);	
	}
	
	if($k == ENTER_KEY) {
		btl_disp_service();
	}
	
	if($k == 258) {
		if($start_from+1-5 < $btl->info[services]) {
			$start_from=$start_from+1;	
		
		} else {
			$start_from=0;	
		}
		//echo $start_from;
		if($selected_index+1 < $btl->info[services]) {
			$selected_index++;
		}
	}
	
	if($k == 97) {
		if($alerts_only==1) {
			$alerts_only=0;
		} else {
			$alerts_only=1;
		}
		$selected_index=0;
		$start_from=0;
	}
	if($k == 100) {
		if($show_downtimes == 1)
			$show_downtimes = 0;
		else
			$show_downtimes = 1;
			
	}
	if($k == 259) {
		if($start_from > 0) {
			//$start_from--;	
			$start_from=$start_from-1;	
		} else {
			$start_from=0;	
		}
		if($selected_index > 0) {
			$selected_index--;
		}
	}
	$map = @$btl->GetSVCMap();
	$oks=0;
	$warns=0;
	$crits=0;	
	ncurses_color_set(4);
	

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

				if($alerts_only == 1) {
					if($servs[$x][current_state] == 0) {
						continue;	
					}
					if($servs[$x][is_downtime] == 1 && $show_downtimes == 0) {
						continue;
					}
				}
				//$out_str=sprintf("%s - %s", $servs[$x][service_name], );
				if($a == $selected_index) {
					$selected_svc=$servs[$x];
					
					$this_row_selected=true;
				} else {
					$this_row_selected=false;

				}
				$a++;
				
				if($a > $start_from && $y <= $lines - 4) {
					
					ncurses_move($y+1, 6);
					//ncurses_addstr($servs[$x][server_name] .  str_repeat(" ", 20-strlen($servs[$x][server_name])));
					ncurses_color_set($cidx);
					

					mark_line($this_row_selected);
					 
					
					ncurses_addstr(sprintf("%-10s", $btl->GetState($servs[$x][current_state])));
					ncurses_move($y+1, 20);
					ncurses_color_set(4);

					mark_line($this_row_selected);
					
					$ostr=sprintf("%-30s  ", $servs[$x][server_name] . ":" . $servs[$x][service_name]);
					ncurses_addstr(substr($ostr,0,27));
					
					ncurses_addstr(substr(str_replace("dbr", "", str_replace("\n", "", $servs[$x][new_server_text])), 0, 60));
					ncurses_color_set(4);

					mark_line($this_row_selected);
					
					ncurses_addstr("\n");
					ncurses_color_set(4);
										
					$y++;
					
				
				}
			}
		
		
		
	}
	
  	for($tt=$y; $tt<$lines-2; $tt++) {
  		
  		ncurses_addstr(str_repeat(" ", $columns));
  		ncurses_move($tt+1, 3);	
  	}

	ncurses_border(0,0, 0,0, 0,0, 0,0);

	// border the main window
	ncurses_attron(NCURSES_A_REVERSE);

	ncurses_mvaddstr(0,1,"($selected_svc[service_id]) bartlby -> " . $btl->getRelease() . "\t" . date("d.m.Y H:i:s", time()) . " /" . $start_from . "-" . $btl->info[services]);
	ncurses_attroff(NCURSES_A_REVERSE);

	ncurses_color_set(4);
	ncurses_attron(NCURSES_A_REVERSE);
	ncurses_mvaddstr($lines-1,1,"Status:\t OK:$oks Criticals: $crits Warnings: $warns");
	ncurses_attroff(NCURSES_A_REVERSE);

  	ncurses_refresh();
  	usleep(2);


}//end main while
function window_td($w, $x, $y, $r1, $r2) {

	ncurses_mvwaddstr( $w, $x, $y, $r1 );
	ncurses_mvwaddstr( $w, $x, $y+30, $r2 );
}
function get_ncurses_color($s) {
	switch($s) {
          	case 0:
          		$cidx=3;
          	break;
          	case 1:
          		$cidx=2;
          	break;
          	case 2:
          		$cidx=1;
          	break;
          	default:
			$cidx=4;
          	break;
          }
	return $cidx;

}

function btl_disp_service() {
	global $selected_svc;
	global $lines, $columns, $btl;
	
	$defaults=$selected_svc;	
	
	if($defaults[service_type] == 1) {
        	$svc_type="Active";
	}

	if($defaults[service_type] == 2) {
        	$svc_type="Passive";
	}

	if($defaults[service_type] == 3) {
        	$svc_type="Group";
	}
	if($defaults[service_type] == 4) {
        	$svc_type="Local";
	}
	if($defaults[service_type] == 5) {
        	$svc_type="SNMP";
	}	
	if($defaults[service_type] == 6) {
        	$svc_type="NRPE";
	}
	if($defaults[service_type] == 7) {
        	$svc_type="NRPE(ssl)";
	}

	$color=get_ncurses_color($defaults[current_state]);

	
	$w = ncurses_newwin(40,80, 2,2);
	ncurses_wborder($w, 0,0, 0,0, 0,0, 0,0);
	

	window_td($w, 1,1, "Server:", $selected_svc[server_name] . "(" . $selected_svc[client_ip] . ")");
	window_td($w, 2,1, "Name:" , $selected_svc[service_name]);

	window_td($w, 3,1, "Type:" , $svc_type);


        ncurses_wcolor_set($w, $color);
        ncurses_wattron($w, NCURSES_A_REVERSE);
	window_td($w, 4,1, "Current State:" , $btl->getState($defaults[current_state]));
        ncurses_wattroff($w, NCURSES_A_REVERSE);
	
	ncurses_wcolor_set($w, 4);


	window_td($w, 5,1, "Last Check:" , date("d.m.Y H:i:s", $defaults[last_check]));

	window_td($w, 6,1, "approx. next Check:" , date("d.m.Y H:i:s", $defaults[last_check]+$defaults[check_interval]));

	window_td($w, 7,1, "Intervall:" , $defaults[check_interval]);

	window_td($w, 8,1, "Last Notify sent:" ,  date("d.m.Y H:i:s", $defaults[last_notify_send]));
	if($defaults["notify_enabled"]==1) {
	        $noti_en="true";
	} else {
		$noti_en="false";
	}
	if($defaults[server_notify] != 1) {
		$noti_en .= " (disabled via server)";
	}

	if($defaults["service_active"]==1) {
		$serv_en = "true";

	} else {
		$serv_en = "false";
	}
	if($defaults[server_enabled] != 1) {
		$serv_en .= "(disabled via server)";
	}
	if($defaults[check_starttime] != 0) {
		$currun=date("d.m.Y H:i:s", $defaults[check_starttime]) . " (PID: $defaults[check_is_running] )";
	} else {
		$currun="(Currently not running)";
	}
	switch($defaults[service_ack]) {
		case 0:
			$needs_ack="no";
		break;
		case 1:
			$needs_ack="yes";
		
		break;
		case 2:
			$needs_ack="outstanding";
		break;
	}
	if( $defaults[service_time_sum] > 0 && $defaults[service_time_count] > 0) {
		$svcMS=round($defaults[service_time_sum] / $defaults[service_time_count], 2);
	} else {
		$svcMS=0;
	}

	

	window_td($w, 9,1, "Notify Enabled:" ,  $noti_en);


	window_td($w, 10,1, "Check Enabled:" ,  $serv_en);


	window_td($w, 11,1, "Check from:" ,  dnl($defaults[hour_from]) . ":" . dnl($defaults[min_from]) . ':00');
	window_td($w, 12,1, "Check to:" ,  dnl($defaults[hour_to]) . ":" . dnl($defaults[min_to]) . ':00');
	window_td($w, 13,1, "flap count:" ,  $defaults[flap_count]);
	window_td($w, 14,1, "flap seconds:" ,  $defaults[flap_seconds]);
	window_td($w, 15,1, "needs ack:" ,  $needs_ack);


	window_td($w, 16,1, "Status:" ,  $defaults[service_retain_current] . "/"  . $defaults[service_retain]);

	window_td($w, 17,1, "Is running?:" ,  $currun);
	window_td($w, 18,1, "Average Check time:" ,  $svcMS . " ms");


	ncurses_wattron($w, NCURSES_A_REVERSE);
 	ncurses_mvwaddstr($w, 22,1,"Last Output:");
	ncurses_wattroff($w, NCURSES_A_REVERSE);
        ncurses_mvwaddstr($w, 23,1,$defaults[new_server_text]);



	ncurses_wattron($w, NCURSES_A_REVERSE);
 	ncurses_mvwaddstr($w, 0,1,"Service Detail:");
	ncurses_wattroff($w, NCURSES_A_REVERSE);
	

	ncurses_wrefresh($w);
	$k = ncurses_wgetch($w);

	ncurses_delwin($w);
	
	
}
function mark_line($tf) {
	if($tf) 
		ncurses_color_set(5);
			
}
function dnl($i) {
        return sprintf("%02d", $i);
}



// the following are two helper functions for
// this ncurses example.



?>
