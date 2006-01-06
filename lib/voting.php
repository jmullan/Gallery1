<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2006 Bharat Mediratta
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
 */
?>
<?php

/*
 * expects as input an array where the keys
 * are string labels and the values are
 * numbers.  Values must be non-negative
 * returns an HTML bar graph as a string
 * assumes bar.gif, located in images/
 * modified from example in PHP Bible
 */
function arrayToBarGraph ($array, $max_width, $table_values="CELLPADDING=5",  $col_1_head=null, $col_2_head=null) {
	global $gallery;
	foreach ($array as $value) {
		if ((IsSet($max_value) && ($value > $max_value)) || (!IsSet($max_value)))  {
			$max_value = $value;
		}
	}

	if (!isSet($max_value)) {
		// no results!
		return null;
	}

	$string_to_return = "\n  <table $table_values>";
	if ($col_1_head || $col_2_head) {
		$string_to_return .=	'<tr>' .
					"\n\t<td></td>".
					"\n\t<td class=\"admin\">$col_1_head</td>".
					"\n\t<td class=\"admin\">$col_2_head</td>".
					"</tr>";
	}

	if ($max_value > 0) {
		$pixels_per_value = ((double) $max_width)
			/ $max_value;
	}
	else {
		$pixels_per_value = 0;
	}

	$counter = 0;
	foreach ($array as $name => $value) {
		$bar_width = $value * $pixels_per_value;
		$img_url= getImagePath('bar.gif');
		$string_to_return .= "\n\t<tr>"
			. "\n\t<td>(". ++$counter .")</td>"
			. "\n\t<td>$name ($value)</td>"
			. "\n\t<td><img src=\"". $img_url ."\" border=\"1\""
			. " width=\"$bar_width\" height=\"10\" alt=\"BAR\"></td>"
			. "\n\t</tr>";
	}
	$string_to_return .= "\n  </table>";
	return($string_to_return);
}

function saveResults($votes) {
	global $gallery;

	if (!$votes) {
		return;
	}

	if ($gallery->album->getPollType() == "critique") {
		foreach ($votes as $vote_key => $vote_value) {
			if ($vote_value === null || $vote_value == "NULL")  {
			       	if (isset($gallery->album->fields["votes"][$vote_key][getVotingID()])) {
				       	unset($gallery->album->fields["votes"][$vote_key][getVotingID()]);
			       	}
		       	} else {
				$gallery->album->fields["votes"][$vote_key][getVotingID()]=intval($vote_value);
			}
		}
       	} else {
	       	krsort($votes, SORT_NUMERIC);
		foreach ($votes as $vote_value => $vote_key) {
		       	if (isset($gallery->album->fields["votes"] [$vote_key] [getVotingID()]) &&
				  $gallery->album->fields["votes"] [$vote_key] [getVotingID()] ===intval($vote_value)) {
				//vote hasn't changed, so skip to next one
				continue;
			}
			foreach ($gallery->album->fields["votes"] as $previous_key => $previous_vote) {
				if (isset($previous_vote[getVotingID()]) &&
						$previous_vote[getVotingID()] 
							=== intval($vote_value)) {
					unset($gallery->album->fields["votes"][$previous_key][getVotingID()]);
				}
			}
			$gallery->album->fields["votes"][$vote_key][getVotingID()] = intval($vote_value);
		}
	}
	$gallery->album->save(array(i18n("New vote recorded")));
}

function getVotingID() {
	global $gallery;

	if ($gallery->album->getVoterClass() ==  "Logged in") {
		return $gallery->user->getUid();
	}
	else if ($gallery->album->getVoterClass() ==  "Everybody") {
		return session_id();
	}
	else {
		return NULL;
	}

}

function canVote()
{
	global $gallery;

	if ($gallery->album->numPhotos($gallery->user->canWriteToAlbum($gallery->album)) == 0) {
	       return false; 
	}	       

	if ($gallery->album->getVoterClass() == "Everybody") {
		return true;
	}

	if ($gallery->album->getVoterClass() == "Logged in" && $gallery->user->isLoggedIn()) {
		return true;
	}

	return false;
}

function addPolling ($id, $form_pos=-1, $immediate=true) {
	global $gallery;

	if ( !canVote()) {
		return;
	}

	if (isset($gallery->album->fields['votes'][$id][getVotingID()])) {
	       	$current_vote = $gallery->album->fields['votes'][$id][getVotingID()];
	} else {
		$current_vote = -1;
	}

	$nv_pairs=$gallery->album->getVoteNVPairs();
	print $gallery->album->getPollHint();
	if ($gallery->album->getPollScale() == 1 && $gallery->album->getPollType() == 'critique') {
		print "\n<input type=checkbox name=\"votes[$id]\" value=\"1\"";
		if ($current_vote > 0) {
			print 'checked';
		}
		print '>'.$nv_pairs[0]['name'];
	}
	else if ($gallery->album->getPollType() == 'rank') {
		if ($gallery->album->getPollHorizontal()) {
			print '<table><tr>';
			for ($i = 0; $i < $gallery->album->getPollScale() ; $i++) {
				print "\n<td align=\"center\"><input type=\"radio\" name=\"votes[$i]\" value=$id onclick=\"chooseOnlyOne($i, $form_pos,".
				$gallery->album->getPollScale().")\" ";
				if ($current_vote === $i) {
					print 'checked';
				}
				print '></td>';
			}
			print '</tr><tr>';
			for ($i = 0; $i < $gallery->album->getPollScale() ; $i++) {
				print '<td align="center" class="attention">'. $nv_pairs[$i]['name'] .'</td>';
			}
			print '</tr></table>';
		    }
		else {
			print '<table>';
			for ($i = 0; $i < $gallery->album->getPollScale() ; $i++) {
				print '<tr>';
				print "\n<td align=\"center\"><input type=\"radio\" name=\"votes[$i]\" value=$id onclick=\"chooseOnlyOne($i, $form_pos,".
				$gallery->album->getPollScale().")\" ";
				if ($current_vote === $i) {
					print 'checked';
				}
				print '></td>';
				print '<td class="attention">'. $nv_pairs[$i]['name']. '</td>';
				print '</tr><tr>';
			}
			print '</table>';
	    	}
	}
	else { // "critique"
		if ($immediate) {
			print "\n<br><select style='FONT-SIZE: 10px;' name=\"votes[$id]\" ";
			print "onChange='this.form.submit();'>";
		}
		else {
			print "\n<br><select name=\"votes[$id]\">";
		}

		if ($current_vote == -1) {
			print '<option value="NULL"><< '. _("Vote") . " >></option>\n";
		}

		for ($i = 0; $i < $gallery->album->getPollScale() ; $i++) {
			$sel='';
			if ($current_vote === $i) {
				$sel = 'selected';
			}
			print "<option value=\"$i\" $sel>". $nv_pairs[$i]['name']. "</option>\n";
		}
		print '</select>';
	}
}

function showResultsGraph($num_rows) {
	global $gallery;

	$results=array();
	$results_count=array();
	$nv_pairs=$gallery->album->getVoteNVPairs();
	$buf='';

	$voters=array();
	foreach ($gallery->album->fields["votes"] as $element => $image_votes) {
		$accum_votes=0;
		$count=0;
		foreach ($image_votes as $voter => $vote_value ) {
			$voters[$voter]=true;
			if ($vote_value> $gallery->album->getPollScale()) { // scale has changed
				$vote_value=$gallery->album->getPollScale();
			}
			$accum_votes+=$nv_pairs[$vote_value]["value"];
			$count++;
		    }
		    if ($accum_votes > 0)  {
	        	$results_count[$element]=$count;
			if ($gallery->album->getPollType() == "rank" || $gallery->album->getPollScale() == 1) {
		    		$results[$element]=$accum_votes;
				$summary="("._("Total points in brackets") . ")";
			}
		    	else {
				$results[$element]=number_format(((double)$accum_votes)/$count, 2);
				$summary="("._("Average points in brackets") . ")";
			}
		}
	}
	array_multisort($results, SORT_NUMERIC, SORT_DESC, $results_count, SORT_NUMERIC, SORT_DESC);
	$rank=0;
	$graph=array();
	$needs_saving=false;
	foreach ($results as $element => $count) {
		$index=$gallery->album->getIndexByVotingId($element);
		if ($index < 0)  {
			// image has been deleted!
			continue;
		} 

		if ($gallery->album->isAlbum($index)) {
			$url = makeAlbumUrl($gallery->album->getAlbumName($index));
			$album=$gallery->album->getSubAlbum($index);
			$desc=sprintf(_("Album: %s"), 
					$album->fields['title']);

		} else {
			$id = $gallery->album->getPhotoId($index);
			$url=makeAlbumUrl($gallery->session->albumName, $id);
			$desc=$gallery->album->getCaption($index);
			if (trim($desc)== "") {
				$desc=$id;
			}	
		}
		$current_rank = $gallery->album->getRank($index);
		$rank++;
		if ($rank != $current_rank) {
			$needs_saving = true;
			$gallery->album->setRank($index, $rank);
		}

		if ($rank > $num_rows) {
			continue;
		}
		
	    	$name_string='<a href="';
		$name_string.= $url;
		$name_string.= '">';
		$name_string.= $desc;
		$name_string.= "</a>";
		$name_string.= " - ".
		      	gTranslate('common', "1 voter", "%d voters", $results_count[$element]);
	       	$graph[$name_string]=$count;
	}

	if ($needs_saving) {
		$gallery->album->save();
	}

	$graph=arrayToBarGraph($graph, 300, "border=0");
	$buf .="\n<br>";
	if ($graph) {
            $buf .="<span class=\"title\">".
		gTranslate('common', "Result from one voter", "Result of %d voters", sizeof($voters)).
                "</span>";
                if ($gallery->album->getPollType() == "critique") {
                        $key_string="";
                        foreach ($nv_pairs as $nv_pair) {
				if (empty($nv_pair["name"])) {
					continue;
				}
				$key_string .= sprintf(_("%s: %s points; "), 
						$nv_pair["name"],
						$nv_pair["value"]);
			}
                        if (strlen($key_string) > 0) {
                                $buf .= "<br>". sprintf(_("Key - %s"), 
						$key_string)." $summary<br>";
			}
		}
                $buf .= $graph;
        } else if ($num_rows > 0 && $gallery->user->canWriteToAlbum($gallery->album)) {
		$buf .= "<span class=\"title\">"._("No votes so far.")."<br></span>";
	}
	return array($buf, $results);
}

function showResults($id) {
	global $gallery;

	$vote_tally=array();
	$nv_pairs=$gallery->album->getVoteNVPairs();
	$buf='';
	if (isSet ($gallery->album->fields["votes"][$id])) {
		foreach ($gallery->album->fields["votes"][$id] as $vote) {
			if (!isSet($vote_tally[$vote])) {
				$vote_tally[$vote]=1;
			}
			else {
				$vote_tally[$vote]++;
			}
		}
	}
	$buf .= "<span class=\"admin\">"._("Poll results:")."</span><br>";

	if (sizeof($vote_tally) === 0) {
		$buf .= _("No votes")."<br>";
		return;
	}
	$index=$gallery->album->getIndexByVotingId($id);
	$buf .= sprintf(_("Number %d overall."), $gallery->album->getRank($index)) ."<br>";
	ksort($vote_tally);

	foreach ($vote_tally as $key => $value) {
		$buf .= sprintf(_("%s: %s"), $nv_pairs[$key]["name"],
		      	gTranslate('common', "one vote", "%d votes", $value)) . "<br>";

	}
	return $buf;
}

/*
** This is a hack around the voting code.
** Note $gallery->album must be set
*/

function buildVotingInputFields() {
    global $gallery;

    $nv_pairs = $gallery->album->getVoteNVPairs();
    $votingInputFieldArray = array();
    for ($i=0; $i<$gallery->album->getPollScale() ; $i++) {
	 $votingInputFieldArray[] = "<input type=\"text\" name=\"nv_pairs[$i][name]\" value=\"". $nv_pairs[$i]["name"] ."\">";
	 $votingInputFieldArray[] = "<input type=\"text\" name=\"nv_pairs[$i][value]\" value=\"". $nv_pairs[$i]["value"] ."\">";
    }

    return $votingInputFieldArray;
}

?>
