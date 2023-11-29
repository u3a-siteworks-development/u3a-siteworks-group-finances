<?php

class U3aGroupFinance {
public $sourcefile;
public $csv_array;
public $headers;
public $grouprow;
public $rownum;
public $gpid;
public $error_msg;
public $year;
 

public function __construct($gpid,$year){
    // Read csv file, extract headings, create keyed array $groupfinances_csv
    $this->sourcefile = U3A_GROUP_FINANCES . "/groupfinances.csv";
    $this->u3a_read_finance_file($this->sourcefile);
    if (!empty($this->error_msg)) {
        print(esc_html($this->error_msg));
    }
    $this->gpid=$gpid;
    //$this->u3a_read_finance_file();
    $this->u3a_get_row_for_group();
    $this->year=$year;
}

public function u3a_get_row_for_group(){
    $gpname=get_the_title($this->gpid);
    $i=0;
    $done=false;
    $count=count($this->csv_array);
    while($i<$count&&!$done){
        if($this->csv_array[$i]['Name']==$gpname)
            {
            $this->grouprow=$this->csv_array[$i];
            $done=true;
            $this->rownum=$i;
        }
        $i=$i+1;
    }
    if(!$done){
        $this->rownum=$count;
        $gpname=get_the_title($this->gpid);
        $c1id=get_post_meta($this->gpid,'coordinator_ID',true);
        $c2id=get_post_meta($this->gpid,'coordinator2_ID',true);
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
        $this->csv_array[$count]['Name']=$gpname;
        $this->csv_array[$count]['Coordinator']=$gpcoord1.", ".$c1phone;
        $this->csv_array[$count]['Coordinator2']=$gpcoord2.", ".$c2phone;
        $this->csv_array[$count]['InBalance']=0.00;
        $this->csv_array[$count]['OutBalance']=0.00;
        $this->csv_array[$count]['IncomeLessExpend']=0.00;
        $this->csv_array[$count]['InFee']=0.00;
        $this->csv_array[$count]['InDonations']=0.00;
        $this->csv_array[$count]['InInterest']=0.00;
        $this->csv_array[$count]['InOther']=0.00;
        $this->csv_array[$count]['OutVenue']=0.00;
        $this->csv_array[$count]['OutActs']=0.00;
        $this->csv_array[$count]['OutEquip']=0.00;
        $this->csv_array[$count]['OutOther']=0.00;
        $this->csv_array[$count]['Bank']="";
        $this->csv_array[$count]['Submitter']="";
        $this->csv_array[$count]['SubPhone']="";
        $this->csv_array[$count]['TotalIncome']=0.00;
        $this->csv_array[$count]['TotalExpenditure']=0.00;
        $this->csv_array[$count]['Year']=$this->year;
        $this->grouprow=$this->csv_array[$count];
    }

}
public function u3a_read_finance_file(){
    $this->csv_array = [];
    // phpcs:disable 
    // Justification for ignoring nonce requirement,not sticcking to wp functions.
    $file=fopen($this->sourcefile,"c+");
    $content=file_get_contents($this->sourcefile);
    fclose($file);
    if(!empty($content)){
        $rows = array_map('str_getcsv', file($this->sourcefile));
        $this->error_msg ="";// u3a_check_rows_for_length($rows,$this->sourcefile);
        // risk code errors if rows not same size
        if (empty($this->error_msg)) {
            $this->headers = array_shift($rows);
            $BOM = "\u{FEFF}";
            //remove BOM if present at start of file, as some software may add it.
            $this->headers[0] = str_replace($BOM, "", $this->headers[0]);
            foreach ($rows as $row) {
                if (!empty(implode($row))) {    // skip empty rows
                    if (count($this->headers) == count($row)) {
                            $this->csv_array[] = array_combine($this->headers, $row);
                            }
                        }
                    }
                }
        }
        else{
            $this->headers = [array("Name", "Coordinator", "Coordinator2", "InBalance", "OutBalance", "IncomeLessExpend", "InFee", "InDonations", "InInterest", "InOther", "OutVenue", "OutActs", "OutEquip", "OutOther", "TotalIncome", "TotalExpenditure", "Bank", "Submitter", "SubPhone", "Year")];
        }
    }
    //first update csv_array with new grouprow 
public function updatecsv(){
    $this->csv_array[$this->rownum]=$this->grouprow;
        //add headers
    $f=fopen($this->sourcefile,"w");
    fputcsv($f, array_keys($this->csv_array[0]));                         
    fclose($f);
        // now add the rows
    $f = fopen($this->sourcefile, "a") or die("Unable to open file!");
    foreach ($this->csv_array as $line) {
            fputcsv($f, $line);
        }
    fclose($f);
    }     
}
