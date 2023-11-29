<?php
// Admin menu pages

add_action('admin_menu', 'u3a_finance_admin_menu');
function u3a_finance_admin_menu()
{
    add_menu_page(
        'u3a Finances',
        'u3a Finances',
        'manage_options',
        'u3a-finance-menu',
        'u3a_show_manage_finance_menu',
        'dashicons-money',
        50
    );
}

add_action('admin_post_u3a_manage_finance', 'u3a_manage_finance');
add_action('admin_post_u3a_set_treasurer', 'u3a_set_treasurer');



function u3a_show_manage_finance_menu(){
    $treasurer=get_option('treasurer');
    $downloadfile=WP_CONTENT_DIR . '/uploads/groupfinances';
    $submit_button = get_submit_button('Reset the finance sheet');
    $tsubmit_button = get_submit_button('Set the treasurer details');
    // phpcs:disable 
    // Justification for ignoring nonce requirement none as yet.
    print <<< END
    <p>To use this plug-in you need to place the shortcode ['u3a_finance_form' 'year'] where year is the year for which the finances are relevant, eg. 2023,  on a suitable page.</p><p> This will not show Groups in draft form  so these need to pass any finances to the treasurer separately.</p>
    
    <form method="POST" action="admin-post.php">
    <input type="hidden" name="action" value="u3a_set_treasurer">
    <h3>Set the name and phone of the treasurer</h3>
    <input type="text" name='treasurer' id='tr' value=$treasurer>
    $tsubmit_button
    </form>
    
    <a href=../../wp-content/uploads/groupfinances/groupfinances.csv download>Download the finance file</a>

    <form method="POST" action="admin-post.php">
    <input type="hidden" name="action" value="u3a_manage_finance">
    <h3>Reset Finance Spreadsheet</h3>
    <p>Make sure you have a copy of the current spreadsheet before doing this. This is to be used only when all the financial records for the current year have been collected.</p>
    $submit_button
    </form>
    
END;
}

function u3a_set_treasurer(){
   if(!null==$_POST['treasurer']){
    $treasurer="'".sanitize_text_field($_POST['treasurer'])."'";
    update_option('treasurer', $treasurer);
    $message="Your treasurer has been set to ".get_option('treasurer').". Click <a href='".site_url()."'>here</a> to return to website.";
    echo $message;
   }

}

function u3a_manage_finance()
{
    //first read the file into csv_array
    //note headers are not contained in csv_array
        $result="";
        $csv_array = [];
        $sourcefile=U3A_GROUP_FINANCES . "/groupfinances.csv";
        if(file_exists($sourcefile)){
            $file=fopen($sourcefile,"c+");
            $content=file_get_contents($sourcefile);
            fclose($file);
            if(!empty($content)){
                $rows = array_map('str_getcsv', file($sourcefile));
                $error_msg ="";
                // u3a_check_rows_for_length($rows,$this->sourcefile);
                    // risk code errors if rows not same size
                if (empty($error_msg)) {
                    $headers = array_shift($rows);
                    $BOM = "\u{FEFF}";
                    //remove BOM if present at start of file, as some software may add it.
                    $headers[0] = str_replace($BOM, "", $headers[0]);
                    foreach ($rows as $row) {
                        if (!empty(implode($row))) {    // skip empty rows
                            if (count($headers) == count($row)) {
                                $csv_array[] = array_combine($headers, $row);
                                }
                            }
                        }
                        
                    }
            }
            else{
                $result="No file to update";
            }
        //now update array, update year and set inBalance to outBalance and all other numbers to zero.
        $reset_array=[];
        if(!empty($csv_array[0]['Year'])){
            for($i=0;$i<count($csv_array);$i++){
                $balance=$csv_array[$i]['OutBalance'];
                $reset_array[$i]['Name']=$csv_array[$i]['Name'];
                $reset_array[$i]['Coordinator']=$csv_array[$i]['Coordinator'];
                $reset_array[$i]['Coordinator2']=$csv_array[$i]['Coordinator2'];
                $reset_array[$i]['InBalance']=$balance;
                $reset_array[$i]['OutBalance']=0.0;
                $reset_array[$i]['IncomeLessExpend']=0.0;
                $reset_array[$i]['InFee']=0.0;
                $reset_array[$i]['InDonations']=0.0;
                $reset_array[$i]['InInterest']=0.0;
                $reset_array[$i]['InOther']=0.0;
                $reset_array[$i]['OutVenue']=0.0;
                $reset_array[$i]['OutActs']=0.0;
                $reset_array[$i]['OutEquip']=0.0;
                $reset_array[$i]['OutOther']=0.0;           
                $reset_array[$i]['TotalIncome']=0.0;
                $reset_array[$i]['TotalExpenditure']=0.0;
                $reset_array[$i]['Bank']="";
                $reset_array[$i]['Submitter']="";
                $reset_array[$i]['SubPhone']="";
                $reset_array[$i]['TotalIncome']=0.00;
                $reset_array[$i]['TotalExpenditure']=0.00; 
                $reset_array[$i]['Year']=""; 
                }
            //now save to file
            $f=fopen($sourcefile,"w");
            fputcsv($f, array_keys($reset_array[0]));                         
            fclose($f);
            // now add the reset_array[$i]s
            $f = fopen($sourcefile, "a") or die("Unable to open file!");
            foreach ($reset_array as $line) {
                fputcsv($f, $line);
            }
            fclose($f);
        }
        else{
            $result="The file has already been reset. Click <a href='".site_url()."'>here</a> to return to website.";
        }
        if(empty($result)){
            $result="The csv finance file has been successfully cleared of all data except for the final balances. These are now set to be the incoming balances for the following year. Click <a href='".site_url()."'>here</a> to return to website.";
        }
    }
    else{
        $result="Finance file not found. Click <a href='".site_url()."'>here</a> to return to website.";
    }
 echo($result);
    }
