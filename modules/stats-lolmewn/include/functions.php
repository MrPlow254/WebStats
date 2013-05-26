<?php
function get_amount($user, $stat, $location) {
	global $link;
	$query = mysqli_query($link, "SELECT `$stat` FROM `".WS_CONFIG_STATS."$location` WHERE `player`='$user'");
	$data = @mysqli_fetch_array($query, MYSQLI_NUM);
	return $data[0];
}

function get_amount_sum($user, $stat, $location) {
	global $link;
	$query = mysqli_query($link, "SELECT SUM(`$stat`) FROM `".WS_CONFIG_STATS."$location` WHERE `player`='$user'");
	$data = @mysqli_fetch_array($query, MYSQLI_NUM);
	return $data[0];
}

function get_movement($user, $type) {
	if($type > 3 || $type < 0){
		return "Error! No movement of this type exists.";
	} else {
		global $link;
		$query = mysqli_query($link, "SELECT `distance` FROM `".WS_CONFIG_STATS."move` WHERE `player` = '$user' AND `type` = '$type'");
		$data = @mysqli_fetch_array($query, MYSQLI_NUM);
		return $data[0];
	}
}
	
function get_amount_break_place_sum($user, $type) {
	global $link;
	$query = mysqli_query($link, "SELECT SUM(`amount`) FROM `".WS_CONFIG_STATS."block` WHERE `player`='$user' AND `break`='$type'");
	$data = @mysqli_fetch_array($query, MYSQLI_NUM);
	return $data[0];
}

function get_user($sort) {
	global $link;
	if(!isset($sort)) {$sort = 'player';}
	$sortkey = "ORDER BY $sort";

	$query = mysqli_query($link, "SELECT * FROM `".WS_CONFIG_STATS."player` ".$sortkey."");
	$time = 0;
	while($row = mysqli_fetch_array($query, MYSQLI_NUM)) {
		$players[$time] = $row[0];
		$time++;
	}
	return $players;
}

function get_user_stats($sort, $start, $end) {
	global $link;
	if(!isset($sort)) {
		$sort = 'player';
	}
	$deadline = time() - WS_CONFIG_DEADLINE;
	$sortkey = "ORDER BY $sort";
	$query = mysqli_query($link, "SELECT $sort FROM `".WS_CONFIG_STATS."player` ".$sortkey." LIMIT ".$start.",".$end."");
	$time = 0;
	while($row = mysqli_fetch_array($query, MYSQLI_NUM)) {
		$players[$time] = $row[0];
		$time++;
	}
	return $players;
}

function set_index_table($player, $pos) {
	$pos++;
	global $image_control;
	if($image_control == true) {
		$image = small_image($player);
	}
	$output .= '<tr><td>'.$pos.'</td>';
	$output .= '<td>&nbsp;&nbsp;'.$image.'<a href="index.php?mode=show-player&user='.$player.'"  >'.$player.'</a></td>';
	$output .= '<td>'.get_played(get_amount($player, "playtime", "player")).'</td>';
	$output .= '<td>'.get_amount($player, "lastjoin", "player").'</td>';
	$output .= '<td>'.get_status($player).'</td>';
	$output .= "</tr>";
	return $output;
}

function set_player_details_table($player) {
	global $image_control, $image_control_3d;
	$foot = get_movement($player, "0");
	$boat = get_movement($player, "1");
	$pig = get_movement($player, "2");
	$cart = get_movement($player, "3");
	$output = '<div class="row">';
	$output .= '<div class="three columns">
				<h6>Movement</h6>
				<table style="margin: 0 auto;">
					<thead>
						<tr>
							<th>Total:</th>
							<th>'. number_format(($foot+$boat+$pig+$cart), 2, '.', '').'</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>'.translate("var85").':</td>
							<td>'. number_format($foot, 2, '.', '').'</td>
						</tr>
						<tr>
							<td>'.translate("var86").':</td>
							<td>'.number_format($boat, 2, '.', '').'</td>
						</tr>
						<tr>
							<td>'.translate("var87").':</td>
							<td>'.number_format($cart, 2, '.', '').'</td>
						</tr>
						<tr>
							<td>'.translate("var88").':</td>
							<td>'.number_format($pig, 2, '.', '').'</td>
						</tr>
					</tbody>
				</table>
			</div>';
	$output .= '<div class="three columns">';
	$output .= '<table>
					<tbody>
						
				<tr>
					<td>'.translate("var5").':</td>
					<td>'.get_amount($player, "lastjoin", "player").'</td>
				</tr>
				<tr>
					<td>'.translate("var14").':</td>
					<td>'.get_amount($player, "lastleave", "player").'</td>
				</tr>
				<tr>
					<td>'.translate("var4").':</td>
					<td>'.get_played(get_amount($player, "playtime", "player")).'</td>
				</tr>
				<tr>
					<td>'.translate("var82").':</td>
					<td>'.get_amount($player, "joins", "player").'</td>
				</tr>
				<tr>
					<td>'.translate("var15").':</td>
					<td>'.get_status($player).'</td>
				</tr>
				<tr>
					<td>'.translate("var16").':</td>
					<td>'.get_amount_sum($player, "amount", "kill").' '.translate("var16").'</td>
				</tr>
				<tr>
					<td>'.translate("var81").':</td>
					<td>'.get_amount($player, "commandsdone", "player").' '.translate("var81").'</td>
				</tr>
				<tr>
					<td>'.translate("var19").':</td>
					<td>'.get_amount_break_place_sum($player, "1").' '.translate("var18").'</td>
				</tr>
				<tr>
					<td>'.translate("var20").':</td>
					<td>'.get_amount_break_place_sum($player, "0").' '.translate("var18").'</td>
				</tr>
					</tbody>
				</table>';
	$output .= '</div>';
	return $output;
}

function set_player_destroy_build_table($player) {
	global $link;
	$query = mysqli_query($link, "SELECT sbo.blockID, q1.amn, q2.brk FROM (SELECT `blockID` FROM `".WS_CONFIG_STATS."block` WHERE `player` = '".$player."' GROUP BY `blockID` ORDER BY `blockID` asc) as sbo LEFT JOIN (SELECT `blockID`, SUM(`amount`) as amn FROM `".WS_CONFIG_STATS."block` WHERE `player` = '".$player."' AND break = 0 GROUP BY blockID ORDER BY blockID asc) as q1 ON sbo.blockID = q1.blockID LEFT JOIN (SELECT blockID, SUM(`amount`) as brk FROM `".WS_CONFIG_STATS."block` WHERE `player` = '".$player."' AND `break` = 1 GROUP BY `blockID` ORDER BY `blockID` asc) as q2 ON sbo.blockID = q2.blockID");
	while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)){
		$output .= '<tr><td><img src="images/icons/'.$row['blockID'].'.png" width="16px" height="16px" />&nbsp;&nbsp;<a href="index.php?mode=material-stats&material='.$row['blockID'].'"  >'.translate(''.$row['blockID'].'').':</a></td>';	
		$output .= '<td>'.$row['brk'].'</td>';
		$output .= '<td>'.$row['amn'].'</td></tr>';
	}
	return $output;
}

function set_player_didkill_table($player, $search) {
	global $link;
	$query = mysqli_query($link, "SELECT `type`, `amount` FROM `".WS_CONFIG_STATS."kill` WHERE `player`='".$player."'");
	$output = '';
	while($row = mysqli_fetch_array($query, MYSQLI_NUM)) {
		$output .= '<div style="clear: both;">';
		$output .= '<div class="content_line_small" align="left" style="width:250px;"><img src="images/icons/'.strtolower(decrypt($row[0])).'.png" width="16px" height="16px" />&nbsp;&nbsp;<a href="index.php?mode=creature-stats&creature='.decrypt($row[0]).'"  >'.translate(''.$row[0].'').':</a></div>';	
		$output .= '<div class="content_line_small" align="left" style="width:100px;">'.$row[1].'</div>';
		$output .= '</div>';
	}
	return $output;
}

function set_player_getkill_table($player, $search) {
	global $link;
	$query = mysqli_query($link, "SELECT `entity`, `amount`, `cause` FROM `".WS_CONFIG_STATS."death` WHERE player='".$player."'");
	$output = '';
	while($row = mysqli_fetch_array($query, MYSQLI_NUM)) {
		$output .= '<div style="clear: both;">';
		$output .= '<div class="content_line_small" align="left" style="width:250px;"><img src="images/icons/'.strtolower(decrypt($row[0])).'.png" width="16px" height="16px" />&nbsp;&nbsp;<a href="index.php?mode=creature-stats&creature='.decrypt($row[0]).'"  >'.translate(''.$row[0].'').':</a></div>';	
		$output .= '<div class="content_line_small" align="left" style="width:100px;">'.$row[1].'</div>';
		$output .= '</div>';
	}
	return $output;
}

function get_server_player() {
	global $link;
	$query = mysqli_query($link, "SELECT COUNT(`player`) FROM `".WS_CONFIG_STATS."player`");
	$row = mysqli_fetch_array($query, MYSQLI_NUM);
	return $row[0];
}

function get_server_count_block($column) {
	global $link;
	$query = mysqli_query($link, "SELECT SUM(`$column`) FROM `".WS_CONFIG_STATS."block` ");
	$row = mysqli_fetch_array($query, MYSQLI_NUM);
	return $row[0];
}

function get_server_count_player($column) {
	global $link;
	$query = mysqli_query($link, "SELECT SUM(`$column`) FROM `".WS_CONFIG_STATS."player`");
	$row = mysqli_fetch_array($query, MYSQLI_NUM);
	return $row[0];
}

function get_server_count_player_move($type) {
	global $link;
	$query = mysqli_query($link, "SELECT SUM(`distance`) FROM `".WS_CONFIG_STATS."move` WHERE `type`=$type");
	$row = mysqli_fetch_array($query, MYSQLI_NUM);
	return $row[0];
}

function get_server_played() {
	global $link;
	$query = mysqli_query($link, "SELECT SUM(`playtime`) FROM `".WS_CONFIG_STATS."player`");
	$row = mysqli_fetch_array($query, MYSQLI_NUM);
	$time = $row[0];
	
	$hour = $time / 3600;
	$hour_2 = floor($hour);
	$minute_hour = $hour_2 * 60;
	$minute = $time / 60; 
	$minute_2 = $minute - $minute_hour;
	$minute_3 = floor($minute_2);
	$day = $hour_2 / 24;
	$day_2 = floor($day);
	$hour_3 = $hour_2 - ($day_2 * 24) ;
	$dayholder = 0;
	
	if ($minute_3 <= 9){$minute_3 = '0'.$minute_3;};
	if ($hour_2 <= 10 && $minute_3 >= 0) {
		$played = "0$hour_2 h $minute_3 m";
	}

	if ($hour_2 > 10) {
		$played = "$hour_2 h $minute_3 m";
	 }
	 return $played;
}

function set_server_details_table() {
	$output = '<div class="row head_logo" style="margin: 0 0;background-image:url('.WS_CONFIG_LOGO.'); background-repeat: no-repeat; background-position: center"></div>';
	$output .= '<div class="row" style="margin: 0 0;"><table class="six columns head_contentbox">';
	$output .= '<tr>
					<td>'.translate("var23").':</td>
					<td> '.get_server_player().'</td>
				</tr>
				
				<tr>
					<td>'.translate("var8").':</td>
					<td> '.get_server_count_block('break').'</td>
				</tr>
				
				<tr>
					<td>'.translate("var9").':</td>
					<td> '.get_server_count_block('amount').'</td>
				</tr>
				
				<!--<div>
					<div class="head_stat">'.translate("var24").':</div>
					<div class="head_content"> '.get_server_count_player('armswing').'</div>
				</div>-->
				
				<tr>
					<td>'.translate("var4").':</td>
					<td> '.get_server_played().'</td>
				</tr>
				
				<tr>
					<td>'.translate("var25").':</td>
					<td>'.get_server_count_player("joins").'</td>
				</tr>
				
				<tr>
					<td>'.translate("var17").' '.translate("var85").':</div>
					<td> '.round(get_server_count_player_move(0), 2).' '.translate("var18").'</td>
				</tr>
				<tr>
					<td>'.translate("var17").' '.translate("var86").':</div>
					<td> '.round(get_server_count_player_move(1), 2).' '.translate("var18").'</td>
				</tr>
				<tr>
					<td>'.translate("var17").' '.translate("var87").':</div>
					<td> '.round(get_server_count_player_move(2), 2).' '.translate("var18").'</td>
				</tr>
				<tr>
					<td>'.translate("var17").' '.translate("var88").':</div>
					<td> '.round(get_server_count_player_move(3), 2).' '.translate("var18").'</td>
				</tr>
				<!--<div>
					<div class="head_stat">'.translate("var26").':</div>
					<div class="head_content"> '.get_server_count_player("openchest").'</div>
				</div>-->
				
				<tr>
					<td>'.translate("var27").':</td>
					<td> '.get_server_count_player("commandsdone").'</td>
				</tr>
				
				<tr>
					<td>'.translate("var93").':</td>
					<td> '.get_server_count_player("arrows").'</td>
				</tr>
				
				<tr>
					<td>'.translate("var89").':</td>
					<td> '.get_server_count_player("xpgained").'</td>
				</tr>
				
				<tr>
					<td>'.translate("var90").':</td>
					<td> '.get_server_count_player("fishcatch").'</td>
				</tr>
				
				<tr>
					<td>'.translate("var91").':</td>
					<td> '.get_server_count_player("damagetaken").'</td>
				</tr>
				
				<tr>
					<td>'.translate("var92").':</td>
					<td> '.get_server_count_player("timeskicked").'</td>
				</tr>

				<!--<div>
					<div class="head_stat">'.translate("var28").':</div>
					<div class="head_content"> '.get_server_count_player("chat").'</div>
				</div>-->
				
				<!--<tr>
					<td>'.translate("var29").':</td>
					<td>'.get_server_count_player("chatletters").'</td>
				</tr>-->';
	$output .= '</table>';
	$output .= '<table class="six columns head_contentbox">';
	$output .= '<tr>
					<td>'.translate("var94").':</td>
					<td> '.get_server_count_player("toolsbroken").'</td>
				</tr>
				
				<tr>
					<td>'.translate("var95").':</td>
					<td> '.get_server_count_player("eggsthrown").'</td>
				</tr>
				<tr>
					<td>'.translate("var96").':</td>
					<td> '.get_server_count_player("itemscrafted").'</td>
				</tr>
				
				<tr>
					<td>'.translate("var97").':</td>
					<td> '.get_server_count_player("omnomnom").'</td>
				</tr>
				<tr>
					<td>'.translate("var98").':</td>
					<td> '.get_server_count_player("onfire").'</td>
				</tr>
				<tr>
					<td>'.translate("var28").':</td>
					<td> '.get_server_count_player("wordssaid").'</td>
				</tr>
				<tr>
					<td>'.translate("var99").':</td>
					<td> '.get_server_count_player("votes").'</td>
				</tr>
				<tr>
					<td>'.translate("var100").':</td>
					<td> '.get_server_count_player("teleports").'</td>
				</tr>
				<tr>
					<td>'.translate("var101").':</td>
					<td> '.get_server_count_player("itempickups").'</td>
				</tr>
				<tr>
					<td>'.translate("var104").':</td>
					<td> '.get_server_count_player("itemdrops").'</td>
				</tr>
				<tr>
					<td>'.translate("var102").':</td>
					<td> '.get_server_count_player("bedenter").'</td>
				</tr>
				<tr>
					<td>'.translate("var106").':</td>
					<td> '.get_server_count_player("bucketempty").'</td>
				</tr>
				<tr>
					<td>'.translate("var103").':</td>
					<td> '.get_server_count_player("worldchange").'</td>
				</tr>
				<tr>
					<td>'.translate("var105").':</td>
					<td> '.get_server_count_player("shear").'</td>
				</tr>
				';
	$output .= '</table></div>';
	return $output;
}

function set_server_didkill_table($search) {
	global $link;
	$query = mysqli_query($link, "SELECT `type`, SUM(`amount`) FROM `".WS_CONFIG_STATS."kill` GROUP BY `type` ".$search."");
	$output = '';
	while($row = mysqli_fetch_array($query, MYSQLI_NUM)) {
		$output .= '<div style="clear: both;">';
		$output .= '<div class="content_line_small" align="left" style="width:250px;"><img src="images/icons/'.strtolower(decrypt($row[0])).'.png" width="16px" height="16px" />&nbsp;&nbsp;<a href="index.php?mode=creature-stats&creature='.decrypt($row[0]).'"  >'.translate(''.$row[0].'').':</a></div>';	
		$output .= '<div class="content_line_small" align="left" style="width:100px;">'.$row[1].'</div>';
		$output .= '</div>';
	}
	return $output;
}

function set_server_getkill_table($search) {
	global $link;
	$query = mysqli_query($link, "SELECT `entity`, `cause`, SUM(`amount`) FROM `".WS_CONFIG_STATS."death` GROUP BY `entity` ".$search."");
	$output = '';
	while($row = mysqli_fetch_array($query, MYSQLI_NUM)) {
		$output .= '<div style="clear: both;">';
		$output .= '<div class="content_line_small" align="left" style="width:250px;"><img src="images/icons/'.strtolower(decrypt($row[0])).'.png" width="16px" height="16px" />&nbsp;&nbsp;<a href="index.php?mode=creature-stats&creature='.decrypt($row[0]).'"  >'.translate(''.$row[0].'').':</a></div>';	
		$output .= '<div class="content_line_small" align="left" style="width:100px;">'.$row[2].'</div>';
		$output .= '</div>';
	}
	return $output;
}

function set_server_destroy_table($search) {
	global $link;
	$query = mysqli_query($link, "SELECT `sbo`.`blockID`, `q2`.`brk` FROM (SELECT `blockID` FROM `".WS_CONFIG_STATS."block` GROUP BY `blockID` ORDER BY `blockID` asc) as `sbo` LEFT JOIN (SELECT `blockID`, SUM(`amount`) as `brk` FROM `".WS_CONFIG_STATS."block` WHERE `break` = 1 GROUP BY `blockID` ORDER BY `blockID` asc) as `q2` ON `sbo`.`blockID` = `q2`.`blockID`");
	$output = '';
	while($row = mysqli_fetch_array($query, MYSQLI_NUM)) {
		$image = str_replace(":", "-", $row[0]); 
		$output .= '<div style="clear: both;">';
		$output .= '<div class="content_line_small" align="left" style="width:250px;"><img src="images/icons/'.strtolower(decrypt($image)).'.png" width="16px" height="16px" />&nbsp;&nbsp;<a href="index.php?mode=material-stats&material='.$row[0].'"  >'.translate(''.$row[0].'').':</a></div>';	
		$output .= '<div class="content_line_small" align="left" style="width:100px;">'.$row[1].'</div>';
		$output .= "\n";
		$output .= '</div>';
	}
	return $output;
}

function set_server_build_table($search) {
	global $link;
	$query = mysqli_query($link, "SELECT `sbo`.`blockID`, `q1`.`amn` FROM (SELECT `blockID` FROM `".WS_CONFIG_STATS."block` GROUP BY `blockID` ORDER BY `blockID` asc) as `sbo` LEFT JOIN (SELECT `blockID`, SUM(`amount`) as `amn` FROM `".WS_CONFIG_STATS."block` WHERE `break` = 0 GROUP BY `blockID` ORDER BY `blockID` asc) as `q1` ON `sbo`.`blockID` = `q1`.`blockID`");
	$output = '';
	while($row = mysqli_fetch_array($query, MYSQLI_NUM)) {
		$image = str_replace(":", "-", $row[0]); 
		$output .= '<div style="clear: both;">';
		$output .= '<div class="content_line_small" align="left" style="width:250px;"><img src="images/icons/'.strtolower(decrypt($image)).'.png" width="16px" height="16px" />&nbsp;&nbsp;<a href="index.php?mode=material-stats&material='.$row[0].'"  >'.translate(''.$row[0].'').':</a></div>';	
		$output .= '<div class="content_line_small" align="left" style="width:100px;">'.$row[1].'</div>';
		$output .= "\n";
		$output .= '</div>';
	}	
	return $output;
}

function set_material_destroy_table($material, $search) {
	global $image_control, $link;
	$query = mysqli_query($link, "SELECT `player`, `amount` FROM `".WS_CONFIG_STATS."block` WHERE `blockID` = '".mysql_real_escape_string($material)."' AND `break` = 1 GROUP BY `blockID` ORDER BY `blockID` ".$search);
	$output = '<table>';
	while($row = mysqli_fetch_array($query, MYSQLI_NUM)) {		
		if($image_control == true) {
			$image = small_image($row[0]);
		}
		$output .= '<tr>';
		$output .= '<td style="width:250px;">'.$image.'&nbsp;&nbsp;<a href="index.php?mode=show-player&user='.$row[0].'">'.$row[0].':</a></td>';	
		$output .= '<td style="width:100px;">'.$row[1].'</td>';
		$output .= '</tr>';
	}
	$output .= '</table>';
	return $output;
}

function set_material_build_table($material, $search) {
	global $image_control, $link;
	$query = mysqli_query($link, "SELECT `player`, `amount` FROM `".WS_CONFIG_STATS."block` WHERE `blockID` = '".mysql_real_escape_string($material)."' AND `break` = 0 GROUP BY `blockID` ORDER BY `blockID` ".$search);
	$output = '<table>';
	while($row = mysqli_fetch_array($query, MYSQLI_NUM)) {
		if($image_control == true) {
			$image = small_image($row[0]);
		}
		$output .= '<tr>';
		$output .= '<td style="width:250px;">'.$image.'&nbsp;&nbsp;<a href="index.php?mode=show-player&user='.$row[0].'">'.$row[0].':</a></td>';	
		$output .= '<td style="width:100px;">'.$row[1].'</td>';
		$output .= '</tr>';
	}
	$output .= '</table>';
	return $output;
}

function set_creature_damagereceived_table($creature, $search) {
	global $image_control, $link;
	$query = mysqli_query($link, "SELECT player, category, stat, value FROM `".WS_CONFIG_STATS."player` WHERE `stat` = '".encrypt($creature)."' GROUP BY `player` ".$search." ");
	$output = '';
	while($row = mysqli_fetch_array($query)) {
		if($image_control == true) {
			$image = small_image($row['player']);
		}
		$output .= '<div style="clear: both;">';
		$output .= '<div class="content_line_small" align="left" style="width:250px;">'.$image.'&nbsp;&nbsp;<a href="index.php?mode=show-player&user='.$row[0].'">'.$row[0].':</a></div>';	
		$output .= '<div class="content_line_small" align="left" style="width:100px;">'.$row['value'].'</div>';
		$output .= "\n";
		$output .= '</div>';
	} 
	return $output;
}

function set_creature_damagedealt_table($creature, $search) {
	global $image_control, $link;
	$query = mysqli_query($link, "SELECT player, category, stat, value FROM `".WS_CONFIG_STATS."player` WHERE stat = '".encrypt($creature)."' GROUP BY player ".$search."");
	$output = '';
	while($row = mysqli_fetch_array($query, MYSQLI_NUM)) {
		if($image_control == true) {
			$image = small_image($row['player']);
		}
		$output .= '<div style="clear: both;">';
		$output .= '<div class="content_line_small" align="left" style="width:250px;">'.$image.'&nbsp;&nbsp;<a href="index.php?mode=show-player&user='.$row[0].'">'.$row[0].':</a></div>';	
		$output .= '<div class="content_line_small" align="left" style="width:100px;">'.$row['value'].'</div>';
		$output .= '</div>';
	} 
	return $output;
}

// Blacklist for inactive users (using WS_CONFIG_DEADLINE)
function blacklist() {
	global $link;
	$marker = '~*~';
	$player_all = get_user('player');
	for($i=0; $i < sizeof($player_all); $i++) {
		$query = mysqli_query($link, "UPDATE `".WS_CONFIG_STATS."player` SET player='".$marker."".$player_all[$i]."' WHERE player='".$player_all[$i]."'");
	}	
}
?>