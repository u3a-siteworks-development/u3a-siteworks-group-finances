<?php
/*This function displays the form to be filled in. It collects some post meta values from the Group custom post type and
any existing values for the group in the csv form.*/

function u3a_finance_form_code($atts){
	
	$year=$atts['year'];
	$result="";
	//$treasurer=get_option('treasurer');
	//$treasurer=str_replace("'","",$treasurer);

	$result="Choose your Group from the list below. It is in alphabetical
	order and you may need to scroll down.";
	$list=getgrouplist($year);
	$result=$list;
	
	return $result;
}

add_shortcode('u3a_finance_form','u3a_finance_form_code');

function savedata($nfd,$gpfinance){
	$result=true;
	updateTotals();
	$outcalc=$nfd['InBalance']+floatval($nfd['IncomeLessExpend']);
	$outcalc=round($outcalc,2,PHP_ROUND_HALF_UP);
    if(!($nfd['OutBalance']==$outcalc)){
        $nfd['OutBalance']=$outcalc;
        $result=false;
	    }
	//Save data by updating array for the group (even if false, not saved back to file yet)

	$gpfinance->grouprow=$nfd;
	if($result){
    	$gpfinance->updatecsv();
	}
	return $result;
}

function show_finance($year,$gpfinance){
	$finvalues=$gpfinance->grouprow;
	$result="<p>The following table shows the values that have been submitted. If you wish to change anything, you can select your Group again on the webpage <a href=".$_SERVER['REQUEST_URI']."> Group Finances</a> and update values as necessary.</p>";
	$result=$result."<table style='white-space:nowrap; margin:10px'>";
	$result=$result."<tr><td></td><td><strong>".$year."</strong></td></tr>";
	$result=$result. "<tr style='display:none;' class='hidden'><td>GroupID</td><td><input type='text' id='gpid' name='gpid' value=".$gpfinance->gpid."</td></tr>";
	$result=$result."<tr><td>Does your Group have a bank account?</td><td>".$finvalues['Bank']."</td></tr>";
	$result=$result."<tr><td>Bank balance & Cash at start of year - 1 January ".$year."</td><td>£".number_format($finvalues['InBalance'],2)."</td></tr>";
	$result=$result."<tr><td><strong>Receipts</strong></td></tr>";
	$result=$result."<tr><td>Standard Group Fees</td><td>£".number_format($finvalues['InFee'],2)."</td></tr>";
	$result=$result."<tr><td>Donations</td><td>£".number_format($finvalues['InDonations'],2)."</td></tr>";
	$result=$result."<tr><td>Bank interest</td><td>£".number_format($finvalues['InInterest'],2)."</td></tr>";
	$result=$result."<tr><td>Other Receipts</td><td>£".number_format($finvalues['InOther'],2)."</td></tr>";
	$result=$result."<tr><td><strong>Total Receipts in year</strong></td><td>£".number_format($finvalues['TotalIncome'],2)."</td></tr>";
	$result=$result."<tr><td><strong>Payments</strong></td></tr>";
	$result=$result."<tr><td>Rent / Room Hire</td><td>£".number_format($finvalues['OutVenue'],2)."</td></tr>";
	$result=$result."<tr><td>Activities</td><td>£".number_format($finvalues['OutActs'],2)."</td></tr>";
	$result=$result."<tr><td>Equipment purchased</td><td>£".number_format($finvalues['OutEquip'],2)."</td></tr>";
	$result=$result."<tr><td>Other Expenses</td><td>£".number_format($finvalues['OutOther'],2)."</td></tr>";
    $result=$result."<tr><td><strong>Total Payments in year</strong></td><td>£".number_format($finvalues['TotalExpenditure'],2)."</td></tr>";
    $diff=floatval($finvalues['TotalIncome']) - floatval($finvalues['TotalExpenditure']);
	$result=$result."<tr><td>Receipts less Payments for year</td><td>£".number_format($diff,2)."</td></tr>";
	$result=$result."<tr><td>		
	Bank balance & Cash at end of year - 31 December ".$year."</td><td>£".number_format($finvalues['OutBalance'],2)."</td></tr>";
	$result=$result."<tr><td>Person submitting form,  if not the Coordinator</td><td>".$finvalues['Submitter']."</td></tr>";
	$result=$result."<tr><td>Their phone number</td><td>".$finvalues['SubPhone']."</td></tr>";
	$result=$result."</table>";
	//need to add check that page exists
	$page = get_posts(['name' => 'group-finances-saved', 'post_type' => 'page']);
	$page_id=$page[0]->ID;
    $my_post = array(
		'ID'           => $page_id,
		'post_title'   => 'Group Finances Saved',
		'post_content' => $result,
	); 
    // Update the post into the database
	wp_update_post( $my_post );
    if ( wp_safe_redirect( get_site_url()."/group-finances-saved" ) ) {
		exit;
	}
}




function collect_entries($year,$gpid,$gpfinance){
	$result="";
	$gpname=get_the_title($gpid);
	$c1id=get_post_meta($gpid,'coordinator_ID',true);
	$c2id=get_post_meta($gpid,'coordinator2_ID',true);
	$gpcoord1=get_the_title($c1id);
	$c1phone=get_post_meta($c1id,'phone',true);
	// second coord details if they exist
	if(!empty($c2id)){
		$gpcoord2=get_the_title($c2id);
		$c2phone=get_post_meta($c2id,'phone',true);
	}
	else{
		$gpcoord2="";
		$c2phone="";
	}
	// create group finance class for this group
	//$gpfinance=new U3aGroupFinance($gpid,$year);
	//$nfd is an array (new finance data) for the entered data
	$nfd=array();
	$nfd['Name']=$gpname;
	$nfd['Coordinator']=$gpcoord1.", ".$c1phone;
	if(!empty($c2id)){
		$nfd['Coordinator2']=$gpcoord2.", ".$c2phone;
	}
	else{
		$nfd['Coordinator2']="";
	}
	// phpcs:disable 
    // Justification for ignoring nonce requirement, input checks are used.
	$nfd['InBalance']=floatval(inputcheck($_POST['inbal']));
	$nfd['OutBalance']=floatval(inputcheck($_POST['outbal']));
	$nfd['IncomeLessExpend']=floatval(inputcheck($_POST['rlp']));
	$nfd['InFee']=floatval(inputcheck($_POST['inf']));
	$nfd['InDonations']=floatval(inputcheck($_POST['ind']));
	$nfd['InInterest']=floatval(inputcheck($_POST['inbi']));
	$nfd['InOther']=floatval(inputcheck($_POST['ino']));
	$nfd['OutVenue']=floatval(inputcheck($_POST['outv']));
	$nfd['OutActs']=floatval(inputcheck($_POST['oute']));
	$nfd['OutEquip']=floatval(inputcheck($_POST['equip']));
	$nfd['OutOther']=floatval(inputcheck($_POST['outo']));
	$nfd['Bank']=inputcheck($_POST['bacc']);
	$nfd['Submitter']=inputcheck($_POST['subm']);
	$nfd['SubPhone']=inputcheck($_POST['sp']);
	$nfd['TotalIncome']=$nfd['InFee']+$nfd['InDonations']+$nfd['InInterest']+$nfd['InOther'];
	$nfd['TotalExpenditure']=$nfd['OutVenue']+$nfd['OutActs']+$nfd['OutEquip']+$nfd['OutOther'];
	$nfd['Year']=$year;
		//savedata checks arithmetic and entries in $nfd and saves to $grouprow if ok;
	if(!(savedata($nfd,$gpfinance))){
		$result=$result."<script>wrongbalancealert()</script>";
		$treasurer=get_option('treasurer');
		$treasurer=str_replace("'","",$treasurer);
		$elvalue=[$gpname, $gpcoord1.' '.$c1phone,$gpcoord2.' '.$c2phone, $nfd['Bank'], $nfd['InBalance'], $nfd['InFee'], $nfd['InDonations'], $nfd['InInterest'], $nfd['InOther'], $nfd['OutVenue'], $nfd['OutActs'], $nfd['OutEquip'], $nfd['OutOther']];
		$elvalue1=wp_json_encode($elvalue);
		$treas1=wp_json_encode($treasurer);
		$yr=wp_json_encode($year);
		//$arr=array_slice($nfd,0,)
		$result=$result."<script>fillform(".$elvalue1.",".$treas1.",".$yr.");</script>";
				
	}
	else{
		$result=$result.show_finance($year,$gpfinance);
	}
	return $result;
}

function add_group_entries_code(){
	if(isset($_GET['year'])){
		$year=$_GET['year'];
	}
	else{
		$year='Year';
	}
	if(isset($_GET['gp'])){
		$gpid=$_GET['gp'];
		// create group finance class for this group
		$gpfinance=new U3aGroupFinance($gpid,$year);
		if(isset($_POST['bacc'])){
			$result=collect_entries($year,$gpid,$gpfinance);
		}
		else{
			$result="";
			$treasurer=get_option('treasurer');
			$treasurer=str_replace("'","",$treasurer);
			if(isset($_GET['gp'])){
				//fill form with known numbers
				$gpid=$_GET['gp'];
				$gpname=get_the_title($gpid);
				$c1id=get_post_meta($gpid,'coordinator_ID',true);
				$c2id=get_post_meta($gpid,'coordinator2_ID',true);
				$gpcoord1=get_the_title($c1id);
				$c1phone=get_post_meta($c1id,'phone',true);
				// second coord details if they exist
				if(!empty($c2id)){
					$gpcoord2=get_the_title($c2id);
					$c2phone=get_post_meta($c2id,'phone',true);
				}
				else{
					$gpcoord2="";
					$c2phone="";
				}			
				$finvalues=$gpfinance->grouprow;
				$elvalue=[$gpname, $gpcoord1.' '.$c1phone,$gpcoord2.' '.$c2phone, $finvalues['Bank'], $finvalues['InBalance'], $finvalues['InFee'], $finvalues['InDonations'], $finvalues['InInterest'], $finvalues['InOther'], $finvalues['OutVenue'], $finvalues['OutActs'], $finvalues['OutEquip'], $finvalues['OutOther']];
				$elvalue1=wp_json_encode($elvalue);
				$treas1=wp_json_encode($treasurer);
				$yr=wp_json_encode($year);
				$result="<script>fillform(".$elvalue1.",".$treas1.",".$yr.");</script>";
			}
		}
	}
	return $result;
}
add_shortcode('add_group_entries','add_group_entries_code');


function get_base_form_code(){
    $content="";
	//create default values for entries
	//and create form
		$finvalues=array();
		$finvalues['Name']="";
		$finvalues['Coordinator']="";
		$finvalues['Coordinator2']="";
		$finvalues['Bank']="No";
		$finvalues['InBalance']=0.00;
		$finvalues['OutBalance']=0.00;
		$finvalues['IncomeLessExpend']=0.00;
		$finvalues['InFee']=0.00;
		$finvalues['InDonations']=0.00;
		$finvalues['InInterest']=0.00;
		$finvalues['InOther']=0.00;
		$finvalues['OutVenue']=0.00;
		$finvalues['OutActs']=0.00;
		$finvalues['OutEquip']=0.00;
		$finvalues['OutOther']=0.00;
		$finvalues['Submitter']="";
		$finvalues['SubPhone']="";
		$finvalues['TotalIncome']=0.00;
		$finvalues['TotalExpenditure']=0.00;
		$finvalues['Year']="";
		$intro="<p>Please complete the form below. If you have any queries, give
			your u3a treasurer a ring - their name and number are shown below. 
			If your Group has no financial activity, please just press Submit to confirm this.</p>";
		$intro=$intro."<p>If you are not the Group Coordinator, please enter your
			name and phone number at the end of the form.</p>";
		$intro=$intro."<p>The totals are automatically updated as you move from one entry to the next.</p>";
		$intro=$intro."<p id='gpname' style='font-weight:bold;'>".$finvalues['Name']."</p>";
		$intro=$intro."<table><tr><td>Coordinator</td><td id='coord1'>".$finvalues['Coordinator']."</td></tr>";
		///if(!empty($finvalues['Coordinator2'])){
			$intro=$intro."<tr><td>Coordinator 2</td><td id='coord2'>".$finvalues['Coordinator2']."</td></tr>";	
		//}
		$intro=$intro."<tr><td>Your u3a Treasurer</td><td id='treas'></td></tr>";
		$intro=$intro."</table>";
		$content=$content.$intro;
			/*form*/
		$content=$content."<style>input.error{color:red;}</style>";
		$content=$content."<form  id='myform'; method='POST' action='';?>";
			/*-- Prevent implicit submission of the form */
		$content=$content." <button type='submit' disabled style='display: none' aria
	-hidden='true'></button>";
		$content=$content."<input type='submit' style='display:none;' class='hidden' value='Submit' id='sub' name='sub'>";
		$content=$content."<button onclick='checkBalance();'>Submit</button>";
		$content=$content."<table style='white-space:nowrap;margin:10px;'>";
		$content=$content."<tr><td>Accounts for year</td><td id='year' name='year'></td></tr>";
		//$content=$content. "<tr style='display:none;' class='hidden'><td>Year</td><td><input type='text' id='yr' name='yr' //value='".$finvalues['Year']."'></td></tr>";
		//$content=$content. "<tr style='display:none;' class='hidden'><td>GroupID</td><td><input type='text' id='gid' name='gid' value=".$finvalues['gpid']."></td></tr>";
		if ($finvalues['Bank']=='Yes'){
			$content=$content. "<tr><td><label for='bacc'>Does your Group have a Bank Account?</label></td><td class='textinput'><input type='radio' name='bacc' value='Yes' checked> Yes
			<input type='radio' name='bacc' value='No'> No
			</td></tr>";
		}
		else{
			$content=$content. "<tr><td><label for='bacc'>Does your Group have a Bank Account?</label></td><td class='textinput'>
			<input type='radio' name='bacc' value='Yes' > Yes
			<input type='radio'  name='bacc' value='No' checked> No
			</td></tr>";
		}
		$content=$content."<tr><td><label for='inbal'>Bank balance & Cash at start of year - 1 January ".$finvalues['Year']."</label></td><td>£<input type='number' onwheel='this.blur();'  step='0.01'  class='textinput' id='inbal' name='inbal' value=".number_format(floatVal($finvalues['InBalance']),2,'.','')."></td></tr>";
		$content=$content."<tr><td><strong>Receipts</strong></td></tr>";
		$content=$content."<tr><td><label for='inf'>Standard Group Fees</label></td><td>£<input type='number' onwheel='this.blur(); ;'  step='0.01'   class='textinput' id='inf' name='inf' value=".number_format(floatVal($finvalues['InFee']),2,'.','')."></td></tr>";
		$content=$content."<tr><td><label for='ind'>Donations and legacies</label></td><td>£<input type='number' onwheel='this.blur();'  step='0.01'   class='textinput' id='ind' name='ind' value=".number_format(floatVal($finvalues['InDonations']),2,'.','')."></td></tr>";
		$content=$content."<tr><td><label for='inbi'>Bank interest</label></td><td>£<input type='number' onwheel='this.blur();'  step='0.01'   class='textinput' id='inbi' name='inbi' value=".number_format(floatVal($finvalues['InInterest']),2,'.','')."></td></tr>";
		$content=$content."<tr><td><label for='ino'>Other Receipts</label></td><td>£<input type='number' onwheel='this.blur();'  step='0.01'   class='textinput' id='ino' name='ino' value=".number_format(floatVal($finvalues['InOther']),2,'.','')."></td></tr>";
		$content=$content."<tr><td><strong>Total Receipts in year</strong></td><td id='tr' name='tr'>£".number_format(floatVal($finvalues['TotalIncome']),2,'.','')."</td></tr>";
		$content=$content."<tr><td><strong>Payments</strong></td></tr>";
		$content=$content."<tr><td><label for='outv'>Rent / Room Hire</label></td><td>£<input type='number' onwheel='this.blur();'  step='0.01'   class='textinput' id='outv' name='outv' value=".number_format(floatVal($finvalues['OutVenue']),2,'.','')."></td></tr>";
		$content=$content."<tr><td><label for='oute'>Activities</label></td><td>£<input type='number' onwheel='this.blur();'  step='0.01'  class='textinput' id='oute' name='oute' value=".number_format(floatVal($finvalues['OutActs']),2,'.','')."></td></tr>";
		$content=$content."<tr><td><label for='equip'>Equipment purchased</label></td><td>£<input type='number' onwheel='this.blur();'  step='0.01'  class='textinput' id='equip' name='equip' value=".number_format(floatVal($finvalues['OutEquip']),2,'.','')."></td></tr>";
		$content=$content."<tr><td><label for='outo'>Other Expenses</label></td><td>£<input type='number' onwheel='this.blur();'  step='0.01'  class='textinput' id='outo' name='outo' value=".number_format(floatVal($finvalues['OutOther']),2,'.','')."></td></tr>";
		$content=$content."<tr><td><strong>Total Payments in year</strong></td><td id='tp' name='tp'>£".number_format(floatVal($finvalues['TotalExpenditure']),2,'.','')."</td></tr>";
		$diff=floatval($finvalues['TotalIncome']) - floatval($finvalues['TotalExpenditure']);
		$content=$content."<tr style='display:none;' class='hidden' ><td></td><td><input type='number' step='0.01' class='textinput' id='rlp' name='rlp' value=".number_format($diff,2,'.','')."></td></tr>";
		$content=$content."<tr><td>Receipts less Payments for year</td><td id='rlpv' name='rlpv'>£".number_format($diff,2,'.','')."</td></tr>";
		$content=$content."<tr><td><label for='outbal'> 						
		Bank balance & Cash at end of year - 31 December ".$finvalues['Year']."</label></td><td>£<input type='number' onwheel='this.blur();'  step='0.01' class='textinput' id='outbal' name='outbal' value=".number_format(0,2,'.','')."></td></tr>";
		$content=$content."<tr><td>Person submitting form,  if not the Coordinator</td><td><input type='text' class='textinput' id='subm' name='subm' value='".$finvalues['Submitter']."'></td></tr>";
		$content=$content."<tr><td>Their phone number</td><td><input type='text' class='textinput' id='sp' name='sp' value='".$finvalues['SubPhone']."'></td></tr>";
		$content=$content."</table></form>";
		return $content;
}
add_shortcode('get_base_form','get_base_form_code');

function getgrouplist($year){
	$args = array(
        'numberposts' => -1,
        'post_type' => 'u3a_group',
        'orderby' => 'post_title',
        'order' => 'ASC'
    );
	//$thispage=untrailingslashit(get_page_link());
    $groups = get_posts($args);
	if(count($groups)>0){
		$result="<!-- wp:list --><ul>";
		foreach($groups as $group){
			$result .="<!-- wp:list-item --><li><a href='".get_site_url()."/group-finances-form?gp=".$group->ID."&year=".$year."'>".$group->post_title."</a></li><!-- /wp:list-item -->";
			//$result .="<li>".$group->post_title."</li>";
		}
		$result=$result."</ul><!-- /wp:list -->";
	}
	return $result;
}

	
function inputcheck($data){
	$data=trim($data);
   $data=stripslashes($data);
    $data=htmlspecialchars($data);
    /*$data=substr($data);*/
      return $data;
}

function updateTotals(){
	$result="<script>maketotals()</script>";
	return $result;
}
